<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\Payment;
use App\Models\HotspotUser;
use App\Services\MikrotikHotspotProvisioner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CustomerPaymentPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

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
            ->assertSee('1 Hour of Wi-Fi access')
            ->assertSee(route('payments.initialize', $package), false)
            ->assertSee('name="email"', false)
            ->assertSee('name="phone"', false);
    }

    public function test_package_and_checkout_pages_preserve_mikrotik_captive_context(): void
    {
        $package = Package::create([
            'name' => '1 Hour',
            'price' => 2,
            'duration' => 1,
            'unit' => 'Hour',
            'active' => true,
        ]);
        $context = [
            'ip' => '172.31.2.37',
            'mac' => '14:3A:AB:3C:5A:DA',
            'link-login-only' => 'http://10.0.0.1/login',
            'link-orig' => 'http://example.test/',
        ];

        $this->get(route('portal.packages', $context))
            ->assertOk()
            ->assertSee('Internet, on your terms.')
            ->assertSee('ip=172.31.2.37')
            ->assertSee('link-login-only=http%3A%2F%2F10.0.0.1%2Flogin');

        $this->get(route('payments.show', array_merge(['package' => $package->id], $context)))
            ->assertOk()
            ->assertSee('name="device_ip" value="172.31.2.37"', false)
            ->assertSee('name="device_mac" value="14:3A:AB:3C:5A:DA"', false)
            ->assertSee('name="link-login-only" value="http://10.0.0.1/login"', false)
            ->assertSee('Continue to Paystack');
    }

    public function test_package_page_shows_a_native_trial_link_for_trusted_router_context(): void
    {
        config(['mikrotik.portal_host' => '10.0.0.1']);

        $this->get(route('portal.packages', [
            'ip' => '172.31.2.37',
            'mac' => '14:3A:AB:3C:5A:DA',
            'link-login-only' => 'http://10.0.0.1/login',
            'link-orig' => 'https://example.test/watch?video=1',
        ]))
            ->assertOk()
            ->assertSee('Free trial')
            ->assertSee('12 HOURS')
            ->assertSee('Start trial')
            ->assertSee('username=T-14%3A3A%3AAB%3A3C%3A5A%3ADA', false)
            ->assertSee('dst=https%3A%2F%2Fexample.test%2Fwatch%3Fvideo%3D1');
    }

    public function test_package_page_hides_native_trial_without_trusted_router_context(): void
    {
        $this->get(route('portal.packages', [
            'mac' => '14:3A:AB:3C:5A:DA',
            'link-login-only' => 'https://attacker.example/login',
        ]))
            ->assertOk()
            ->assertDontSee('Start trial');
    }

    public function test_terms_page_shows_access_payment_and_support_terms(): void
    {
        $this->get(route('terms'))
            ->assertOk()
            ->assertSee('Terms & conditions')
            ->assertSee('Access and duration')
            ->assertSee('Payments')
            ->assertSee('Fair use')
            ->assertSee('tel:+254743980340', false)
            ->assertSee('https://wa.me/254743980340', false);
    }

    public function test_package_list_identifies_multi_device_access(): void
    {
        $package = Package::query()->where('name', '1 Month (2 Devices)')->firstOrFail();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('1 Month (2 Devices)')
            ->assertSee('2 devices');

        $this->assertSame(2, $package->device_limit);
        $this->assertEquals(200, Package::query()->where('name', '2 Weeks')->value('price'));
        $this->assertEquals(300, Package::query()->where('name', '1 Month')->value('price'));
        $this->assertEquals(500, $package->price);
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

    public function test_checkout_persists_only_router_context_for_the_configured_hotspot_host(): void
    {
        config([
            'paystack.secret_key' => 'sk_test_example',
            'mikrotik.portal_host' => '10.0.0.1',
        ]);
        Http::fake([
            'api.paystack.co/transaction/initialize' => Http::response([
                'status' => true,
                'data' => ['authorization_url' => 'https://checkout.paystack.com/example'],
            ]),
        ]);
        $package = Package::create([
            'name' => '1 Hour',
            'price' => 2,
            'duration' => 1,
            'unit' => 'Hour',
            'active' => true,
        ]);

        $this->post(route('payments.initialize', $package), [
            'email' => 'buyer@example.com',
            'phone' => '0712345678',
            'device_ip' => '172.31.2.37',
            'device_mac' => '14:3A:AB:3C:5A:DA',
            'link-login-only' => 'http://10.0.0.1/login',
            'link-orig' => 'http://example.test/',
        ])->assertRedirect('https://checkout.paystack.com/example');

        $payment = Payment::query()->sole();
        $this->assertSame('172.31.2.37', $payment->device_ip);
        $this->assertSame('14:3A:AB:3C:5A:DA', $payment->device_mac);
        $this->assertSame('http://10.0.0.1/login', $payment->router_login_url);
        $this->assertSame('http://example.test/', $payment->router_redirect_url);
    }

    public function test_paystack_return_auto_submits_credentials_only_to_the_configured_router(): void
    {
        config(['mikrotik.portal_host' => '10.0.0.1']);
        $package = Package::create([
            'name' => '2 Hours',
            'price' => 10,
            'duration' => 2,
            'unit' => 'Hours',
            'active' => true,
        ]);
        $payment = Payment::create([
            'package_id' => $package->id,
            'amount' => 10,
            'phone' => '0712345678',
            'email' => 'buyer@example.com',
            'reference' => 'wifi_auto_login',
            'payment_channel' => 'paystack',
            'status' => 'Success',
            'device_ip' => '172.31.2.37',
            'device_mac' => '14:3A:AB:3C:5A:DA',
            'router_login_url' => 'http://10.0.0.1/login',
            'router_redirect_url' => 'http://example.test/',
        ]);
        HotspotUser::create([
            'payment_id' => $payment->id,
            'username' => '0712345678-'.$payment->id,
            'password' => 'generated-secret',
            'phone' => '0712345678',
            'status' => 'Provisioned',
        ]);

        $this->get(route('payments.callback', ['reference' => $payment->reference]))
            ->assertOk()
            ->assertSee('Connecting you now')
            ->assertSee('action="http://10.0.0.1/login"', false)
            ->assertSee('name="username"', false)
            ->assertSee('value="generated-secret"', false)
            ->assertSee('requestSubmit()', false);
    }

    public function test_paystack_return_does_not_post_credentials_to_an_untrusted_router_host(): void
    {
        config(['mikrotik.portal_host' => '10.0.0.1']);
        $package = Package::create([
            'name' => '2 Hours',
            'price' => 10,
            'duration' => 2,
            'unit' => 'Hours',
            'active' => true,
        ]);
        $payment = Payment::create([
            'package_id' => $package->id,
            'amount' => 10,
            'phone' => '0712345678',
            'email' => 'buyer@example.com',
            'reference' => 'wifi_bad_router_host',
            'payment_channel' => 'paystack',
            'status' => 'Success',
            'device_ip' => '172.31.2.37',
            'device_mac' => '14:3A:AB:3C:5A:DA',
            'router_login_url' => 'https://attacker.example/login',
        ]);
        HotspotUser::create([
            'payment_id' => $payment->id,
            'username' => '0712345678-'.$payment->id,
            'password' => 'generated-secret',
            'phone' => '0712345678',
            'status' => 'Provisioned',
        ]);

        $this->get(route('payments.callback', ['reference' => $payment->reference]))
            ->assertOk()
            ->assertSee('You’re almost online')
            ->assertDontSee('action="https://attacker.example/login"', false)
            ->assertDontSee('requestSubmit()', false);
    }

    public function test_successful_paystack_callback_verifies_and_records_the_package_purchase(): void
    {
        config(['paystack.secret_key' => 'sk_test_example']);
        $this->mock(MikrotikHotspotProvisioner::class)
            ->shouldReceive('provision')
            ->once()
            ->withArgs(fn (HotspotUser $hotspotUser, Package $provisionedPackage): bool =>
                str_starts_with($hotspotUser->username, '0712345678-')
                && strlen($hotspotUser->password) === 12
                && $provisionedPackage->name === '3 Days'
            );

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
            ->assertSee('PAYMENT VERIFIED')
            ->assertSee('Connect to 10bob WiFi and enter these hotspot credentials.')
            ->assertSee('Username')
            ->assertSee('Password');

        $payment->refresh();
        $this->assertSame('Success', $payment->status);
        $this->assertNotNull($payment->access_expires_at);
        $this->assertSame($package->id, $payment->package_id);
        $this->assertSame('Provisioned', $payment->hotspotUser->status);
        $this->assertSame('0712345678', $payment->hotspotUser->phone);
        $this->assertNotSame('wifi_test_password', $payment->hotspotUser->getRawOriginal('password'));
    }

    public function test_verified_payment_remains_successful_when_router_provisioning_fails(): void
    {
        config(['paystack.secret_key' => 'sk_test_example']);
        $this->mock(MikrotikHotspotProvisioner::class)
            ->shouldReceive('provision')
            ->once()
            ->andThrow(new \RuntimeException('Router unavailable'));

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
            'reference' => 'wifi_router_unavailable',
            'payment_channel' => 'paystack',
            'status' => 'Pending',
        ]);

        Http::fake([
            'api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => [
                    'status' => 'success',
                    'reference' => 'wifi_router_unavailable',
                    'amount' => 200,
                    'currency' => 'KES',
                ],
            ]),
        ]);

        $this->get(route('payments.callback', ['reference' => $payment->reference]))
            ->assertOk()
            ->assertSee('PAYMENT VERIFIED')
            ->assertSee('Your payment is confirmed, but Wi-Fi account setup is delayed.')
            ->assertDontSee('Connect to 10bob WiFi and enter these hotspot credentials.');

        $this->assertSame('Success', $payment->fresh()->status);
        $this->assertSame('Failed', $payment->fresh('hotspotUser')->hotspotUser->status);
    }

    public function test_successful_callback_retries_failed_hotspot_provisioning_without_reverifying_payment(): void
    {
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
            'reference' => 'wifi_retry_provisioning',
            'payment_channel' => 'paystack',
            'status' => 'Success',
        ]);
        $hotspotUser = HotspotUser::create([
            'payment_id' => $payment->id,
            'username' => '0712345678-'.$payment->id,
            'password' => 'generated-secret',
            'phone' => '0712345678',
            'status' => 'Failed',
            'provisioning_error' => 'MikroTik provisioning failed.',
        ]);

        $this->mock(MikrotikHotspotProvisioner::class)
            ->shouldReceive('provision')
            ->once()
            ->withArgs(fn (HotspotUser $provisionedUser, Package $provisionedPackage): bool =>
                $provisionedUser->is($hotspotUser)
                && $provisionedPackage->is($package)
            );

        $this->get(route('payments.callback', ['reference' => $payment->reference]))
            ->assertOk()
            ->assertSee('You’re almost online')
            ->assertSee('generated-secret');

        $this->assertSame('Provisioned', $hotspotUser->fresh()->status);
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