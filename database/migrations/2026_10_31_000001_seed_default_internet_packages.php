<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $packages = [
        ['name' => '1 Hour', 'price' => 2, 'duration' => 1, 'unit' => 'Hour'],
        ['name' => '3 Hours', 'price' => 5, 'duration' => 3, 'unit' => 'Hours'],
        ['name' => '24 Hours', 'price' => 20, 'duration' => 24, 'unit' => 'Hours'],
        ['name' => '3 Days', 'price' => 50, 'duration' => 3, 'unit' => 'Days'],
        ['name' => '7 Days', 'price' => 100, 'duration' => 7, 'unit' => 'Days'],
    ];

    public function up(): void
    {
        if (! Schema::hasColumn('packages', 'active')) {
            Schema::table('packages', function (Blueprint $table) {
                $table->boolean('active')->default(true);
            });
        }

        foreach ($this->packages as $package) {
            $exists = DB::table('packages')
                ->where('price', $package['price'])
                ->where('duration', $package['duration'])
                ->where('unit', $package['unit'])
                ->exists();

            if (! $exists) {
                DB::table('packages')->insert([
                    ...$package,
                    'active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        foreach ($this->packages as $package) {
            DB::table('packages')
                ->where('name', $package['name'])
                ->where('price', $package['price'])
                ->where('duration', $package['duration'])
                ->where('unit', $package['unit'])
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('payments')
                        ->whereColumn('payments.package_id', 'packages.id');
                })
                ->delete();
        }
    }
};