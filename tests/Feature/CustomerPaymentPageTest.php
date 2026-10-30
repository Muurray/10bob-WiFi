<?php

namespace Tests\Feature;

use Tests\TestCase;

class CustomerPaymentPageTest extends TestCase
{
    public function test_buy_page_shows_the_phone_only_package_without_starting_a_payment(): void
    {
        $response = $this->get('/buy');

        $response->assertOk()
            ->assertSee('KSh 10')
            ->assertSee('12 hours of 10bob WiFi')
            ->assertSee('name="phone"', false)
            ->assertSee('M-Pesa payments unavailable')
            ->assertSee('Payments are unavailable while phone-only checkout is confirmed with Paystack.');

        $this->post('/buy')->assertStatus(405);
    }
}