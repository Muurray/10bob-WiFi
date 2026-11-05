<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotspot_users', function (Blueprint $table) {
            $table->foreignId('payment_id')->unique()->after('id')->constrained()->cascadeOnDelete();
            $table->string('username')->unique()->after('payment_id');
            $table->text('password')->after('username');
            $table->string('phone')->after('password');
            $table->string('status')->default('Pending')->after('phone');
            $table->timestamp('provisioned_at')->nullable()->after('status');
            $table->text('provisioning_error')->nullable()->after('provisioned_at');
        });
    }

    public function down(): void
    {
        Schema::table('hotspot_users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_id');
            $table->dropUnique(['username']);
            $table->dropColumn(['username', 'password', 'phone', 'status', 'provisioned_at', 'provisioning_error']);
        });
    }
};