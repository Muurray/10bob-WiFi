<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->updatePrice('2 Weeks', 14, 1, 200);
        $this->updatePrice('1 Month', 30, 1, 300);
        $this->updatePrice('1 Month (2 Devices)', 30, 2, 500);
    }

    public function down(): void
    {
        $this->updatePrice('2 Weeks', 14, 1, 500);
        $this->updatePrice('1 Month', 30, 1, 750);
        $this->updatePrice('1 Month (2 Devices)', 30, 2, 950);
    }

    private function updatePrice(string $name, int $duration, int $deviceLimit, int $price): void
    {
        DB::table('packages')
            ->where('name', $name)
            ->where('duration', $duration)
            ->where('unit', 'Days')
            ->where('device_limit', $deviceLimit)
            ->update(['price' => $price, 'updated_at' => now()]);
    }
};