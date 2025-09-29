<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('current_package_id')->nullable()->constrained('user_packages')->after('email_verified_at');
            $table->timestamp('package_expires_at')->nullable()->after('current_package_id');
            $table->decimal('wallet_balance', 10, 2)->default(0)->after('package_expires_at');
            $table->boolean('is_admin')->default(false)->after('wallet_balance');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['current_package_id']);
            $table->dropColumn(['current_package_id', 'package_expires_at', 'wallet_balance', 'is_admin']);
        });
    }
};