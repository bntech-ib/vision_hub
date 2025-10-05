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
            $table->text('bank_account_holder_name')->nullable()->change();
            $table->text('bank_account_number')->nullable()->change();
            $table->text('bank_name')->nullable()->change();
            $table->text('bank_branch')->nullable()->change();
            $table->text('bank_routing_number')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('bank_account_holder_name')->nullable()->change();
            $table->string('bank_account_number')->nullable()->change();
            $table->string('bank_name')->nullable()->change();
            $table->string('bank_branch')->nullable()->change();
            $table->string('bank_routing_number')->nullable()->change();
        });
    }
};
