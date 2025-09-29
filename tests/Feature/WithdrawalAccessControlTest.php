<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Models\GlobalSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class WithdrawalAccessControlTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_with_disabled_withdrawal_cannot_request_withdrawal()
    {
        // Set global withdrawal setting to false
        GlobalSetting::set('withdrawal_enabled', false);
        
        $user = User::factory()->create([
            'wallet_balance' => 1000
        ]);

        // Authenticate the user with Sanctum
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/wallet/withdraw', [
            'amount' => 100,
            'paymentMethod' => [
                'type' => 'bank',
                'name' => 'Bank Transfer'
            ],
            'accountDetails' => [
                'accountName' => 'John Doe',
                'accountNumber' => '1234567890',
                'bankName' => 'Test Bank'
            ]
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'Withdrawal access has been disabled by admin. Please contact support for assistance.'
        ]);

        $this->assertDatabaseCount('withdrawal_requests', 0);
    }

    /** @test */
    public function user_with_enabled_withdrawal_can_request_withdrawal()
    {
        // Set global withdrawal setting to true
        GlobalSetting::set('withdrawal_enabled', true);
        
        $user = User::factory()->create([
            'wallet_balance' => 1000
        ]);

        // Authenticate the user with Sanctum
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/wallet/withdraw', [
            'amount' => 100,
            'paymentMethod' => [
                'type' => 'bank',
                'name' => 'Bank Transfer'
            ],
            'accountDetails' => [
                'accountName' => 'John Doe',
                'accountNumber' => '1234567890',
                'bankName' => 'Test Bank'
            ]
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Withdrawal request submitted successfully'
        ]);

        $this->assertDatabaseCount('withdrawal_requests', 1);
        $this->assertDatabaseHas('withdrawal_requests', [
            'user_id' => $user->id,
            'amount' => 100,
            'status' => 'pending'
        ]);
    }

    /** @test */
    public function admin_can_enable_user_withdrawal_access()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        // Set global withdrawal setting to false initially
        GlobalSetting::set('withdrawal_enabled', false);

        // Authenticate the admin with Sanctum
        Sanctum::actingAs($admin);

        // Enable globally
        $response = $this->putJson("/admin/settings/enable-withdrawal");

        $response->assertStatus(302); // Redirect back
        $this->assertTrue(GlobalSetting::isWithdrawalEnabled());
    }

    /** @test */
    public function admin_can_disable_user_withdrawal_access()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        // Set global withdrawal setting to true initially
        GlobalSetting::set('withdrawal_enabled', true);

        // Authenticate the admin with Sanctum
        Sanctum::actingAs($admin);

        // Disable globally
        $response = $this->putJson("/admin/settings/disable-withdrawal");

        $response->assertStatus(302); // Redirect back
        $this->assertFalse(GlobalSetting::isWithdrawalEnabled());
    }
}