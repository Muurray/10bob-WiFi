<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('reference')->nullable()->unique();
            $table->timestamp('access_expires_at')->nullable();
            $table->string('payment_channel')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique(['reference']);
            $table->dropColumn([
                'reference',
                'access_expires_at',
                'payment_channel',
            ]);
        });
    }
};