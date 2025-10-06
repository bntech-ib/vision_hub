<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\WithdrawalRequest;

class UnlimitedWithdrawalTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function user_can_make_multiple_withdrawals_in_a_day_without_limit()
    {
        // Create a user with sufficient balance
        $user = User::factory()->create([
            'wallet_balance' => 10000, // 10,000 balance
            'referral_earnings' => 5000,
            'bank_account_holder_name' => 'John Doe',
            'bank_account_number' => '1234567890',
            'bank_name' => 'Test Bank',
            'bank_account_bound_at' => now()
        ]);

        // Make first withdrawal
        $response1 = $this->actingAs($user)->postJson('/api/v1/wallet/withdraw', [
            'amount' => 500, // Well below the old 500 daily limit
            'payment_method_id' => 1
        ]);

        $response1->assertStatus(201);
        $response1->assertJson([
            'success' => true,
            'message' => 'Withdrawal request submitted successfully'
        ]);

        // Make second withdrawal that would have exceeded the old 500 daily limit
        $response2 = $this->actingAs($user)->postJson('/api/v1/wallet/withdraw', [
            'amount' => 600, // This would have exceeded the old 500 daily limit
            'payment_method_id' => 1
        ]);

        $response2->assertStatus(201);
        $response2->assertJson([
            'success' => true,
            'message' => 'Withdrawal request submitted successfully'
        ]);

        // Make third withdrawal that would definitely exceed the old limit
        $response3 = $this->actingAs($user)->postJson('/api/v1/wallet/withdraw', [
            'amount' => 700, // Total now would be 1800, far exceeding the old 500 daily limit
            'payment_method_id' => 1
        ]);

        $response3->assertStatus(201);
        $response3->assertJson([
            'success' => true,
            'message' => 'Withdrawal request submitted successfully'
        ]);

        // Verify all three withdrawals were created
        $this->assertEquals(3, WithdrawalRequest::count());
    }

    /** @test */
    public function user_still_cannot_withdraw_more_than_their_balance()
    {
        // Create a user with limited balance
        $user = User::factory()->create([
            'wallet_balance' => 100, // Only 100 balance
            'referral_earnings' => 5000,
            'bank_account_holder_name' => 'John Doe',
            'bank_account_number' => '1234567890',
            'bank_name' => 'Test Bank',
            'bank_account_bound_at' => now()
        ]);

        // Try to withdraw more than balance
        $response = $this->actingAs($user)->postJson('/api/v1/wallet/withdraw', [
            'amount' => 200, // More than the available 100 balance
            'payment_method_id' => 1
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Insufficient wallet balance'
        ]);
    }

    /** @test */
    public function user_can_still_withdraw_from_referral_earnings()
    {
        // Create a user with referral earnings
        $user = User::factory()->create([
            'wallet_balance' => 1000,
            'referral_earnings' => 5000, // 5000 referral earnings
            'bank_account_holder_name' => 'John Doe',
            'bank_account_number' => '1234567890',
            'bank_name' => 'Test Bank',
            'bank_account_bound_at' => now()
        ]);

        // Withdraw from referral earnings
        $response = $this->actingAs($user)->postJson('/api/v1/wallet/withdraw', [
            'amount' => 1000, // More than the old 500 daily limit
            'payment_method_id' => 2 // 2 = referral earnings
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Withdrawal request submitted successfully'
        ]);
    }
}