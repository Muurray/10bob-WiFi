<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Payment;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        return view('customer.pay', compact('package'));
    }

    public function initialize(Request $request, Package $package): RedirectResponse
    {
        abort_unless($package->active, 404);

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:32'],
        ]);

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

    public function callback(Request $request): View
    {
        $reference = $request->query('reference');
        abort_unless(is_string($reference) && $reference !== '', 404);

        $payment = Payment::with('package')
            ->where('reference', $reference)
            ->where('payment_channel', 'paystack')
            ->firstOrFail();

        if ($payment->status === 'Success') {
            return view('customer.payment-result', [
                'payment' => $payment,
                'success' => true,
            ]);
        }

        $secretKey = config('paystack.secret_key');
        if (! $secretKey) {
            return view('customer.payment-result', [
                'payment' => $payment,
                'success' => false,
                'message' => 'Payment verification is unavailable. Contact support with your reference.',
            ]);
        }

        try {
            $response = Http::withToken($secretKey)
                ->acceptJson()
                ->timeout(15)
                ->get(config('paystack.base_url').'/transaction/verify/'.rawurlencode($reference));
        } catch (ConnectionException) {
            return view('customer.payment-result', [
                'payment' => $payment,
                'success' => false,
                'message' => 'Payment verification could not be reached. Please refresh this page shortly.',
            ]);
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

            return view('customer.payment-result', [
                'payment' => $payment->fresh('package'),
                'success' => false,
                'message' => 'Payment was not confirmed. No package purchase has been completed.',
            ]);
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

        return view('customer.payment-result', [
            'payment' => $payment,
            'success' => true,
        ]);
    }
}