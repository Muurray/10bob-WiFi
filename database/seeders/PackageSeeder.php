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
        ];

        foreach ($packages as $package) {
            Package::firstOrCreate(
                [
                    'price' => $package['price'],
                    'duration' => $package['duration'],
                    'unit' => $package['unit'],
                ],
                ['name' => $package['name'], 'active' => true],
            );
        }
    }
}