<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $packages = [
        ['name' => '2 Weeks', 'price' => 200, 'duration' => 14, 'unit' => 'Days', 'device_limit' => 1],
        ['name' => '1 Month', 'price' => 300, 'duration' => 30, 'unit' => 'Days', 'device_limit' => 1],
        ['name' => '1 Month (2 Devices)', 'price' => 500, 'duration' => 30, 'unit' => 'Days', 'device_limit' => 2],
    ];

    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->unsignedTinyInteger('device_limit')->default(1);
        });

        foreach ($this->packages as $package) {
            $exists = DB::table('packages')
                ->where('name', $package['name'])
                ->where('price', $package['price'])
                ->where('duration', $package['duration'])
                ->where('unit', $package['unit'])
                ->where('device_limit', $package['device_limit'])
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
                ->where('device_limit', $package['device_limit'])
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('payments')
                        ->whereColumn('payments.package_id', 'packages.id');
                })
                ->delete();
        }

        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('device_limit');
        });
    }
};