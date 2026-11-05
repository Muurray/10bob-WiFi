<?php

namespace Tests\Feature;

use App\Models\Package;
use Database\Seeders\PackageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_homepage_shows_the_eight_packages_in_the_captive_portal_layout(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('Available packages')
            ->assertSee('Choose a package to get online.')
            ->assertSee('portal-panel')
            ->assertSee('KSh 2')
            ->assertSee('1 Hour')
            ->assertSee('KSh 5')
            ->assertSee('3 Hours')
            ->assertSee('KSh 20')
            ->assertSee('24 Hours')
            ->assertSee('KSh 50')
            ->assertSee('3 Days')
            ->assertSee('KSh 100')
            ->assertSee('7 Days')
            ->assertSee('KSh 200')
            ->assertSee('2 Weeks')
            ->assertSee('KSh 300')
            ->assertSee('1 Month')
            ->assertSee('KSh 500')
            ->assertSee('1 Month (2 Devices)')
            ->assertDontSee('10 KES')
            ->assertDontSee('200 KES');

        $this->assertStringNotContainsString('<nav', $response->getContent());
    }

    public function test_homepage_lists_database_packages_and_package_specific_checkout_links(): void
    {
        $this->seed(PackageSeeder::class);

        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('KSh 2')
            ->assertSee('1 Hour')
            ->assertSee('KSh 5')
            ->assertSee('3 Hours')
            ->assertSee('KSh 20')
            ->assertSee('24 Hours')
            ->assertSee('KSh 50')
            ->assertSee('3 Days')
            ->assertSee('KSh 100')
            ->assertSee('7 Days');

        foreach (Package::query()->where('active', true)->get() as $package) {
            $response->assertSee(route('payments.show', $package), false);
        }
    }
}
