<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            ['name' => '1 Hour', 'price' => 2, 'duration' => 1, 'unit' => 'Hour'],
            ['name' => '3 Hours', 'price' => 5, 'duration' => 3, 'unit' => 'Hours'],
            ['name' => '24 Hours', 'price' => 20, 'duration' => 24, 'unit' => 'Hours'],
            ['name' => '3 Days', 'price' => 50, 'duration' => 3, 'unit' => 'Days'],
            ['name' => '7 Days', 'price' => 100, 'duration' => 7, 'unit' => 'Days'],
            ['name' => '2 Weeks', 'price' => 200, 'duration' => 14, 'unit' => 'Days', 'device_limit' => 1],
            ['name' => '1 Month', 'price' => 300, 'duration' => 30, 'unit' => 'Days', 'device_limit' => 1],
            ['name' => '1 Month (2 Devices)', 'price' => 500, 'duration' => 30, 'unit' => 'Days', 'device_limit' => 2],
        ];

        foreach ($packages as $package) {
            $package['device_limit'] ??= 1;

            Package::firstOrCreate(
                [
                    'price' => $package['price'],
                    'duration' => $package['duration'],
                    'unit' => $package['unit'],
                    'device_limit' => $package['device_limit'],
                ],
                ['name' => $package['name'], 'active' => true],
            );
        }
    }
}