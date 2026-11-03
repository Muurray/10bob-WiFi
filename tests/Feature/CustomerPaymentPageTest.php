<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CustomerPaymentPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_shows_the_selected_database_package(): void
    {
        $package = Package::create([
            'name' => '1 Hour',
            'price' => 2,
            'duration' => 1,
            'unit' => 'Hour',
            'active' => true,
        ]);

        $this->get(route('payments.show', $package))
            ->assertOk()
            ->assertSee('KSh 2')
            ->assertSee('1 Hour package')
            ->assertSee(route('payments.initialize', $package), false)
            ->assertSee('name="email"', false)
            ->assertSee('name="phone"', false);
    }

    public function test_checkout_initializes_paystack_for_the_selected_package_amount(): void
    {
        config(['paystack.secret_key' => 'sk_test_example']);
        Http::fake([
            'api.paystack.co/transaction/initialize' => Http::response([
                'status' => true,
                'data' => ['authorization_url' => 'https://checkout.paystack.com/example'],
            ]),
        ]);

        $package = Package::create([
            'name' => '3 Hours',
            'price' => 5,
            'duration' => 3,
            'unit' => 'Hours',
            'active' => true,
        ]);

        $this->post(route('payments.initialize', $package), [
            'email' => 'buyer@example.com',
            'phone' => '0712345678',
        ])->assertRedirect('https://checkout.paystack.com/example');

        $payment = Payment::query()->sole();
        $this->assertSame((string) $package->id, (string) $payment->package_id);
        $this->assertEquals(5.0, (float) $payment->amount);
        $this->assertSame('Pending', $payment->status);
        $this->assertSame('paystack', $payment->payment_channel);

        Http::assertSent(fn (ClientRequest $request): bool =>
            str_ends_with($request->url(), '/transaction/initialize')
            && $request['amount'] === 500
            && $request['currency'] === 'KES'
            && $request['email'] === 'buyer@example.com'
        );
    }

    public function test_successful_paystack_callback_verifies_and_records_the_package_purchase(): void
    {
        config(['paystack.secret_key' => 'sk_test_example']);

        $package = Package::create([
            'name' => '3 Days',
            'price' => 50,
            'duration' => 3,
            'unit' => 'Days',
            'active' => true,
        ]);
        $payment = Payment::create([
            'package_id' => $package->id,
            'amount' => 50,
            'phone' => '0712345678',
            'email' => 'buyer@example.com',
            'reference' => 'wifi_test_reference',
            'payment_channel' => 'paystack',
            'status' => 'Pending',
        ]);

        Http::fake([
            'api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => [
                    'status' => 'success',
                    'reference' => 'wifi_test_reference',
                    'amount' => 5000,
                    'currency' => 'KES',
                ],
            ]),
        ]);

        $this->get(route('payments.callback', ['reference' => $payment->reference]))
            ->assertOk()
            ->assertSee('Payment received and verified')
            ->assertSee('Wi-Fi access still requires hotspot provisioning');

        $payment->refresh();
        $this->assertSame('Success', $payment->status);
        $this->assertNotNull($payment->access_expires_at);
        $this->assertSame($package->id, $payment->package_id);
    }

    public function test_paystack_callback_rejects_a_success_response_with_the_wrong_amount(): void
    {
        config(['paystack.secret_key' => 'sk_test_example']);

        $package = Package::create([
            'name' => '1 Hour',
            'price' => 2,
            'duration' => 1,
            'unit' => 'Hour',
            'active' => true,
        ]);
        $payment = Payment::create([
            'package_id' => $package->id,
            'amount' => 2,
            'phone' => '0712345678',
            'email' => 'buyer@example.com',
            'reference' => 'wifi_wrong_amount',
            'payment_channel' => 'paystack',
            'status' => 'Pending',
        ]);

        Http::fake([
            'api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => [
                    'status' => 'success',
                    'reference' => 'wifi_wrong_amount',
                    'amount' => 1,
                    'currency' => 'KES',
                ],
            ]),
        ]);

        $this->get(route('payments.callback', ['reference' => $payment->reference]))
            ->assertOk()
            ->assertSee('Payment was not confirmed. No package purchase has been completed.');

        $payment->refresh();
        $this->assertSame('Pending', $payment->status);
        $this->assertNull($payment->access_expires_at);
    }
}