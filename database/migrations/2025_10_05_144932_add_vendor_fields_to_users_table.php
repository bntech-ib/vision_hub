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
            $table->boolean('is_vendor')->default(false)->after('is_admin');
            $table->string('vendor_company_name')->nullable()->after('is_vendor');
            $table->text('vendor_description')->nullable()->after('vendor_company_name');
            $table->string('vendor_website')->nullable()->after('vendor_description');
            $table->string('vendor_commission_rate')->default(0)->after('vendor_website');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_vendor', 'vendor_company_name', 'vendor_description', 'vendor_website', 'vendor_commission_rate']);
        });
    }
};