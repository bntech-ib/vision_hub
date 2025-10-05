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
        Schema::create('vendor_access_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('access_key_id')->constrained('access_keys')->onDelete('cascade');
            $table->decimal('commission_rate', 5, 2)->default(0); // Commission percentage for vendor
            $table->boolean('is_sold')->default(false);
            $table->foreignId('sold_to_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('sold_at')->nullable();
            $table->decimal('earned_amount', 10, 2)->default(0); // Amount earned by vendor
            $table->timestamps();
            
            $table->index(['vendor_id', 'is_sold']);
            $table->index(['access_key_id']);
            $table->index(['sold_to_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_access_keys');
    }
};