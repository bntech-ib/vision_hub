<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\WithdrawalRequest;

class WithdrawalRequestTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function user_can_request_withdrawal_from_wallet_balance()
    {
        $user = User::factory()->create([
            'wallet_balance' => 1000,
            'referral_earnings' => 500,
            'bank_account_holder_name' => 'John Doe',
            'bank_account_number' => '1234567890',
            'bank_name' => 'Test Bank',
            'bank_account_bound_at' => now()
        ]);

        $response = $this->actingAs($user)->postJson('/api/v1/wallet/withdraw', [
            'amount' => 100,
            'payment_method_id' => 1
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Withdrawal request submitted successfully'
        ]);

        $this->assertDatabaseHas('withdrawal_requests', [
            'user_id' => $user->id,
            'amount' => 100,
            'payment_method_id' => 1,
            'payment_method' => 'Wallet Balance'
        ]);
    }

    /** @test */
    public function user_can_request_withdrawal_from_referral_earnings()
    {
        $user = User::factory()->create([
            'wallet_balance' => 1000,
            'referral_earnings' => 500,
            'bank_account_holder_name' => 'John Doe',
            'bank_account_number' => '1234567890',
            'bank_name' => 'Test Bank',
            'bank_account_bound_at' => now()
        ]);

        $response = $this->actingAs($user)->postJson('/api/v1/wallet/withdraw', [
            'amount' => 100,
            'payment_method_id' => 2
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Withdrawal request submitted successfully'
        ]);

        $this->assertDatabaseHas('withdrawal_requests', [
            'user_id' => $user->id,
            'amount' => 100,
            'payment_method_id' => 2,
            'payment_method' => 'Referral Earnings'
        ]);
    }

    /** @test */
    public function user_cannot_withdraw_more_than_wallet_balance()
    {
        $user = User::factory()->create([
            'wallet_balance' => 50,
            'referral_earnings' => 500,
            'bank_account_holder_name' => 'John Doe',
            'bank_account_number' => '1234567890',
            'bank_name' => 'Test Bank',
            'bank_account_bound_at' => now()
        ]);

        $response = $this->actingAs($user)->postJson('/api/v1/wallet/withdraw', [
            'amount' => 100,
            'payment_method_id' => 1
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Insufficient wallet balance'
        ]);
    }

    /** @test */
    public function user_cannot_withdraw_more_than_referral_earnings()
    {
        $user = User::factory()->create([
            'wallet_balance' => 1000,
            'referral_earnings' => 50,
            'bank_account_holder_name' => 'John Doe',
            'bank_account_number' => '1234567890',
            'bank_name' => 'Test Bank',
            'bank_account_bound_at' => now()
        ]);

        $response = $this->actingAs($user)->postJson('/api/v1/wallet/withdraw', [
            'amount' => 100,
            'payment_method_id' => 2
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Insufficient referral earnings balance'
        ]);
    }

    /** @test */
    public function withdrawal_request_fails_with_invalid_payment_method_id()
    {
        $user = User::factory()->create([
            'wallet_balance' => 1000,
            'referral_earnings' => 500,
            'bank_account_holder_name' => 'John Doe',
            'bank_account_number' => '1234567890',
            'bank_name' => 'Test Bank',
            'bank_account_bound_at' => now()
        ]);

        $response = $this->actingAs($user)->postJson('/api/v1/wallet/withdraw', [
            'amount' => 100,
            'payment_method_id' => 3 // Invalid ID
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function user_cannot_request_withdrawal_without_bound_bank_account()
    {
        $user = User::factory()->create([
            'wallet_balance' => 1000,
            'referral_earnings' => 500
            // No bank account details bound
        ]);

        $response = $this->actingAs($user)->postJson('/api/v1/wallet/withdraw', [
            'amount' => 100,
            'payment_method_id' => 1
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'You must bind your bank account details before requesting a withdrawal.'
        ]);
    }
}