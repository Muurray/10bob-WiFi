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
    public function test_homepage_shows_the_five_packages_with_one_navigation_bar(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('Available Packages')
            ->assertSee('Choose an affordable internet package.')
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
            ->assertDontSee('10 KES')
            ->assertDontSee('200 KES');

        $this->assertSame(1, substr_count($response->getContent(), '<nav'));
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
