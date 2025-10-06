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
        // This migration is for documentation purposes only.
        // The actual change is in the User model where we removed encryption/decryption
        // of bank account fields to store them in plain text as requested.
        
        // No schema changes are needed as the columns already exist.
        // Existing encrypted data will need to be manually decrypted if needed.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration cannot be reversed as it represents a policy change
        // rather than a schema change.
    }
};