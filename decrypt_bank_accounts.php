<?php

// Script to decrypt existing bank account data and store it in plain text
// This should be run once after deploying the changes to decrypt any existing encrypted data

require_once 'vendor/autoload.php';

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Get all users with bank account data
$users = DB::table('users')
    ->whereNotNull('bank_account_holder_name')
    ->orWhereNotNull('bank_account_number')
    ->orWhereNotNull('bank_name')
    ->orWhereNotNull('bank_branch')
    ->orWhereNotNull('bank_routing_number')
    ->get();

echo "Found {$users->count()} users with bank account data.\n";

$decryptedCount = 0;
$errorCount = 0;

foreach ($users as $user) {
    $updatedData = [];
    
    // Try to decrypt each field
    $fields = ['bank_account_holder_name', 'bank_account_number', 'bank_name', 'bank_branch', 'bank_routing_number'];
    
    foreach ($fields as $field) {
        if ($user->$field !== null) {
            try {
                // Try to decrypt the value
                $decryptedValue = Crypt::decryptString($user->$field);
                $updatedData[$field] = $decryptedValue;
                $decryptedCount++;
            } catch (DecryptException $e) {
                // If it's already plain text or can't be decrypted, leave it as is
                echo "Could not decrypt {$field} for user ID {$user->id}: " . $e->getMessage() . "\n";
                $errorCount++;
            }
        }
    }
    
    // Update the user record with decrypted values
    if (!empty($updatedData)) {
        DB::table('users')
            ->where('id', $user->id)
            ->update($updatedData);
            
        echo "Updated user ID {$user->id} with decrypted bank account data.\n";
    }
}

echo "Decryption complete. {$decryptedCount} fields decrypted, {$errorCount} errors encountered.\n";
echo "Please verify the data and delete this script after use.\n";