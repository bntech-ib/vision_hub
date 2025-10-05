<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BankAccountBindingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a user can bind their bank account details
     */
    public function test_user_can_bind_bank_account_details()
    {
        // Create a user
        $user = User::factory()->create();
        
        // Authenticate the user
        Sanctum::actingAs($user);
        
        // Prepare bank account data
        $bankData = [
            'bank_account_holder_name' => 'John Doe',
            'bank_account_number' => '1234567890',
            'bank_name' => 'Test Bank',
            'bank_branch' => 'Main Branch',
            'bank_routing_number' => '987654321',
        ];
        
        // Make request to bind bank account
        $response = $this->postJson('/api/v1/user/bank-account/bind', $bankData);
        
        // Assert successful response
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Bank account details bound successfully.'
                 ]);
        
        // Refresh user data
        $user->refresh();
        
        // Assert that bank account details are saved
        $this->assertTrue($user->hasBoundBankAccount());
        $this->assertEquals('John Doe', $user->bank_account_holder_name);
        $this->assertEquals('1234567890', $user->bank_account_number);
        $this->assertEquals('Test Bank', $user->bank_name);
        $this->assertEquals('Main Branch', $user->bank_branch);
        $this->assertEquals('987654321', $user->bank_routing_number);
        $this->assertNotNull($user->bank_account_bound_at);
    }
    
    /**
     * Test that a user cannot update their bank account details after binding
     */
    public function test_user_cannot_update_bank_account_after_binding()
    {
        // Create a user with bank account already bound
        $user = User::factory()->create([
            'bank_account_holder_name' => 'John Doe',
            'bank_account_number' => '1234567890',
            'bank_name' => 'Test Bank',
            'bank_account_bound_at' => now(),
        ]);
        
        // Authenticate the user
        Sanctum::actingAs($user);
        
        // Try to bind different bank account details
        $newBankData = [
            'bank_account_holder_name' => 'Jane Smith',
            'bank_account_number' => '0987654321',
            'bank_name' => 'Another Bank',
        ];
        
        // Make request to bind bank account
        $response = $this->postJson('/api/v1/user/bank-account/bind', $newBankData);
        
        // Assert error response
        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Bank account details have already been bound and cannot be updated.'
                 ]);
        
        // Refresh user data
        $user->refresh();
        
        // Assert that bank account details are unchanged
        $this->assertEquals('John Doe', $user->bank_account_holder_name);
        $this->assertEquals('1234567890', $user->bank_account_number);
        $this->assertEquals('Test Bank', $user->bank_name);
    }
    
    /**
     * Test that bank account fields are protected from updates after binding
     */
    public function test_bank_account_fields_protected_from_profile_updates()
    {
        // Create a user with bank account already bound
        $user = User::factory()->create([
            'bank_account_holder_name' => 'John Doe',
            'bank_account_number' => '1234567890',
            'bank_name' => 'Test Bank',
            'bank_account_bound_at' => now(),
        ]);
        
        // Authenticate the user
        Sanctum::actingAs($user);
        
        // Try to update profile with bank account fields
        $profileData = [
            'name' => 'John Smith',
            'email' => 'johnsmith@example.com',
            'bank_account_holder_name' => 'Jane Smith', // This should be ignored
            'bank_account_number' => '0987654321', // This should be ignored
            'bank_name' => 'Another Bank', // This should be ignored
        ];
        
        // Make request to update profile
        $response = $this->putJson('/api/v1/user/profile', $profileData);
        
        // Assert successful response
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Profile updated successfully.'
                 ]);
        
        // Refresh user data
        $user->refresh();
        
        // Assert that profile details are updated but bank account details are unchanged
        $this->assertEquals('John Smith', $user->name);
        $this->assertEquals('johnsmith@example.com', $user->email);
        $this->assertEquals('John Doe', $user->bank_account_holder_name); // Should be unchanged
        $this->assertEquals('1234567890', $user->bank_account_number); // Should be unchanged
        $this->assertEquals('Test Bank', $user->bank_name); // Should be unchanged
    }
}