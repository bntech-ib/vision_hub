<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('bank_account_holder_name')->nullable()->after('referral_code');
            $table->string('bank_account_number')->nullable()->after('bank_account_holder_name');
            $table->string('bank_name')->nullable()->after('bank_account_number');
            $table->string('bank_branch')->nullable()->after('bank_name');
            $table->string('bank_routing_number')->nullable()->after('bank_branch');
            $table->boolean('bank_account_verified')->default(false)->after('bank_routing_number');
            $table->timestamp('bank_account_bound_at')->nullable()->after('bank_account_verified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'bank_account_holder_name',
                'bank_account_number',
                'bank_name',
                'bank_branch',
                'bank_routing_number',
                'bank_account_verified',
                'bank_account_bound_at'
            ]);
        });
    }
};