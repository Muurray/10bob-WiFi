<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Payment;
use App\Models\HotspotUser;
use App\Services\MikrotikHotspotProvisioner;
use App\Services\MikrotikPortalLink;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function show(Package $package): View
    {
        abort_unless($package->active, 404);

        $portalContext = request()->only(['ip', 'mac', 'link-login-only', 'link-orig']);

        return view('customer.pay', compact('package', 'portalContext'));
    }

    public function initialize(Request $request, Package $package, MikrotikPortalLink $portalLink): RedirectResponse
    {
        abort_unless($package->active, 404);

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:32'],
            'device_ip' => ['nullable', 'ip'],
            'device_mac' => ['nullable', 'regex:/^(?:[0-9A-Fa-f]{2}:){5}[0-9A-Fa-f]{2}$/'],
            'link-login-only' => ['nullable', 'url', 'max:2048'],
            'link-orig' => ['nullable', 'url', 'max:2048'],
        ]);

        $routerLoginUrl = $portalLink->trustedLoginUrl($validated['link-login-only'] ?? null);
        $hasDeviceContext = $routerLoginUrl
            && ! empty($validated['device_ip'])
            && ! empty($validated['device_mac']);

        $secretKey = config('paystack.secret_key');
        if (! $secretKey) {
            throw ValidationException::withMessages([
                'payment' => 'Online payments are not configured yet.',
            ]);
        }

        $reference = 'wifi_'.Str::uuid();
        $payment = Payment::create([
            'package_id' => $package->id,
            'amount' => $package->price,
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'reference' => $reference,
            'payment_channel' => 'paystack',
            'status' => 'Pending',
            'device_ip' => $hasDeviceContext ? $validated['device_ip'] : null,
            'device_mac' => $hasDeviceContext ? strtoupper($validated['device_mac']) : null,
            'router_login_url' => $hasDeviceContext ? $routerLoginUrl : null,
            'router_redirect_url' => $hasDeviceContext ? ($validated['link-orig'] ?? null) : null,
        ]);

        try {
            $response = Http::withToken($secretKey)
                ->acceptJson()
                ->timeout(15)
                ->post(config('paystack.base_url').'/transaction/initialize', [
                    'email' => $validated['email'],
                    'amount' => (int) round((float) $package->price * 100),
                    'currency' => config('paystack.currency', 'KES'),
                    'reference' => $reference,
                    'callback_url' => route('payments.callback'),
                    'metadata' => [
                        'payment_id' => $payment->id,
                        'package_id' => $package->id,
                        'phone' => $validated['phone'],
                    ],
                ]);
        } catch (ConnectionException) {
            $payment->update(['status' => 'Failed']);

            return back()->withInput()->withErrors([
                'payment' => 'Could not connect to the payment provider. Please try again.',
            ]);
        }

        $authorizationUrl = $response->json('data.authorization_url');
        if (! $response->successful() || ! $response->json('status') || ! $authorizationUrl) {
            $payment->update(['status' => 'Failed']);

            return back()->withInput()->withErrors([
                'payment' => 'The payment provider could not start checkout. Please try again.',
            ]);
        }

        return redirect()->away($authorizationUrl);
    }

    public function callback(Request $request, MikrotikHotspotProvisioner $provisioner, MikrotikPortalLink $portalLink): Response
    {
        $reference = $request->query('reference');
        abort_unless(is_string($reference) && $reference !== '', 404);

        $payment = Payment::with(['package', 'hotspotUser'])
            ->where('reference', $reference)
            ->where('payment_channel', 'paystack')
            ->firstOrFail();

        if ($payment->status === 'Success') {
            $payment = $this->provisionHotspot($payment, $provisioner);

            return $this->paymentResult($payment, true, $portalLink);
        }

        $secretKey = config('paystack.secret_key');
        if (! $secretKey) {
            return $this->paymentResult($payment, false, $portalLink, 'Payment verification is unavailable. Contact support with your reference.');
        }

        try {
            $response = Http::withToken($secretKey)
                ->acceptJson()
                ->timeout(15)
                ->get(config('paystack.base_url').'/transaction/verify/'.rawurlencode($reference));
        } catch (ConnectionException) {
            return $this->paymentResult($payment, false, $portalLink, 'Payment verification could not be reached. Please refresh this page shortly.');
        }

        $transaction = $response->json('data');
        $isVerified = $response->successful()
            && $response->json('status')
            && is_array($transaction)
            && ($transaction['status'] ?? null) === 'success'
            && ($transaction['reference'] ?? null) === $reference
            && (int) ($transaction['amount'] ?? 0) === (int) round((float) $payment->amount * 100)
            && strtoupper((string) ($transaction['currency'] ?? '')) === strtoupper((string) config('paystack.currency', 'KES'));

        if (! $isVerified) {
            if (($transaction['status'] ?? null) === 'failed') {
                $payment->update(['status' => 'Failed']);
            }

            return $this->paymentResult($payment->fresh(['package', 'hotspotUser']), false, $portalLink, 'Payment was not confirmed. No package purchase has been completed.');
        }

        $payment = DB::transaction(function () use ($payment): Payment {
            $lockedPayment = Payment::query()->lockForUpdate()->findOrFail($payment->id);
            if ($lockedPayment->status !== 'Success') {
                $package = Package::findOrFail($lockedPayment->package_id);
                $duration = (int) $package->duration;
                $expiresAt = match (strtolower(rtrim($package->unit, 's'))) {
                    'hour' => now()->addHours($duration),
                    'day' => now()->addDays($duration),
                    default => throw new \InvalidArgumentException('Unsupported package duration unit.'),
                };

                $lockedPayment->update([
                    'status' => 'Success',
                    'access_expires_at' => $expiresAt,
                ]);
            }

            return $lockedPayment->fresh('package');
        });

        $payment = $this->provisionHotspot($payment, $provisioner);

        return $this->paymentResult($payment, true, $portalLink);
    }

    private function paymentResult(Payment $payment, bool $success, MikrotikPortalLink $portalLink, ?string $message = null): Response
    {
        $autoLogin = $success
            && $payment->hotspotUser?->status === 'Provisioned'
            && $payment->device_ip
            && $payment->device_mac
            && $portalLink->trustedLoginUrl($payment->router_login_url);

        return response()->view('customer.payment-result', [
            'payment' => $payment,
            'success' => $success,
            'message' => $message,
            'autoLogin' => (bool) $autoLogin,
        ])->header('Cache-Control', 'no-store, private');
    }


    private function provisionHotspot(Payment $payment, MikrotikHotspotProvisioner $provisioner): Payment
    {
        $hotspotUser = $payment->hotspotUser()->firstOrCreate([], function () use ($payment): array {
            $phone = preg_replace('/\D+/', '', $payment->phone) ?: 'wifi';

            return [
                'username' => substr($phone, 0, 24).'-'.$payment->id,
                'password' => Str::random(12),
                'phone' => $payment->phone,
                'status' => 'Pending',
            ];
        });

        if ($hotspotUser->status !== 'Provisioned') {
            try {
                $provisioner->provision($hotspotUser, $payment->package);
                $hotspotUser->update([
                    'status' => 'Provisioned',
                    'provisioned_at' => now(),
                    'provisioning_error' => null,
                ]);
            } catch (\Throwable $exception) {
                report($exception);
                $hotspotUser->update([
                    'status' => 'Failed',
                    'provisioning_error' => 'MikroTik provisioning failed. Retry the payment callback.',
                ]);
            }
        }

        return $payment->fresh(['package', 'hotspotUser']);
    }
}