<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FinancialSettingsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that financial settings can be updated
     */
    public function test_financial_settings_can_be_updated()
    {
        // Create an admin user
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);
        
        // Authenticate as admin
        $this->actingAs($admin->fresh());
        
        // Prepare financial settings data
        $data = [
            'withdrawal_enabled' => true,
        ];
        
        // Make request to update financial settings
        $response = $this->post(route('admin.settings.financial.update'), $data);
        
        // Assert redirect (since it's not an AJAX request in this test)
        $response->assertStatus(302);
        
        // Check that the setting was saved
        $this->assertTrue(\App\Models\GlobalSetting::isWithdrawalEnabled());
    }
    
    /**
     * Test that withdrawal portal can be enabled/disabled globally
     */
    public function test_withdrawal_portal_can_be_toggled()
    {
        // Create an admin user
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);
        
        // Authenticate as admin
        $this->actingAs($admin->fresh());
        
        // Enable withdrawal portal
        $response = $this->put(route('admin.settings.enable-withdrawal'));
        $response->assertStatus(302);
        $this->assertTrue(\App\Models\GlobalSetting::isWithdrawalEnabled());
        
        // Disable withdrawal portal
        $response = $this->put(route('admin.settings.disable-withdrawal'));
        $response->assertStatus(302);
        $this->assertFalse(\App\Models\GlobalSetting::isWithdrawalEnabled());
    }
}