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
        Schema::table('support_options', function (Blueprint $table) {
            $table->dropColumn('icon');
            $table->string('avatar')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('support_options', function (Blueprint $table) {
            $table->dropColumn('avatar');
            $table->string('icon')->nullable();
        });
    }
};