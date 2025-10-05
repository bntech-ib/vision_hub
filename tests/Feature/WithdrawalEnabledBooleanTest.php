<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WithdrawalEnabledBooleanTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that withdrawal enabled field only accepts boolean values
     */
    public function test_withdrawal_enabled_field_only_accepts_boolean_values()
    {
        // Create an admin user
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);
        
        // Authenticate as admin
        $this->actingAs($admin->fresh());
        
        // Test with true value
        $response = $this->postJson(route('admin.settings.financial.update'), [
            'withdrawal_enabled' => true,
        ]);
        
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Financial settings updated successfully'
                 ]);
        
        $this->assertTrue(\App\Models\GlobalSetting::isWithdrawalEnabled());
        
        // Test with false value
        $response = $this->postJson(route('admin.settings.financial.update'), [
            'withdrawal_enabled' => false,
        ]);
        
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Financial settings updated successfully'
                 ]);
        
        $this->assertFalse(\App\Models\GlobalSetting::isWithdrawalEnabled());
    }
    
    /**
     * Test that non-boolean values are rejected
     */
    public function test_non_boolean_values_are_rejected()
    {
        // Create an admin user
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);
        
        // Authenticate as admin
        $this->actingAs($admin->fresh());
        
        // Test with string value
        $response = $this->postJson(route('admin.settings.financial.update'), [
            'withdrawal_enabled' => 'yes',
        ]);
        
        $response->assertStatus(422);
        
        // Test with numeric value
        $response = $this->postJson(route('admin.settings.financial.update'), [
            'withdrawal_enabled' => 1,
        ]);
        
        $response->assertStatus(200); // Numeric 1 is converted to boolean true
        
        // Test with null value
        $response = $this->postJson(route('admin.settings.financial.update'), [
            'withdrawal_enabled' => null,
        ]);
        
        $response->assertStatus(422); // Required field
    }
    
    /**
     * Test that toggle methods set proper boolean values
     */
    public function test_toggle_methods_set_proper_boolean_values()
    {
        // Create an admin user
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);
        
        // Authenticate as admin
        $this->actingAs($admin->fresh());
        
        // Enable withdrawal
        $response = $this->putJson(route('admin.settings.enable-withdrawal'));
        
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Withdrawal access enabled globally.'
                 ]);
        
        $this->assertTrue(\App\Models\GlobalSetting::isWithdrawalEnabled());
        
        // Disable withdrawal
        $response = $this->putJson(route('admin.settings.disable-withdrawal'));
        
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Withdrawal access disabled globally.'
                 ]);
        
        $this->assertFalse(\App\Models\GlobalSetting::isWithdrawalEnabled());
    }
}