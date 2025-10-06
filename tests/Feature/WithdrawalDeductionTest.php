<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Models\Transaction;

class WithdrawalDeductionTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function withdrawal_is_deducted_from_selected_method_and_creates_transaction_record()
    {
        // Create a user with both wallet balance and referral earnings
        $user = User::factory()->create([
            'wallet_balance' => 1000,
            'referral_earnings' => 500,
            'bank_account_holder_name' => 'John Doe',
            'bank_account_number' => '1234567890',
            'bank_name' => 'Test Bank',
            'bank_account_bound_at' => now()
        ]);

        // Create a withdrawal request from wallet balance
        $withdrawal = WithdrawalRequest::factory()->create([
            'user_id' => $user->id,
            'amount' => 100,
            'payment_method_id' => 1,
            'payment_method' => 'Wallet Balance',
            'status' => 'pending'
        ]);

        // Approve the withdrawal (this should deduct from wallet balance)
        $response = $this->actingAs($user)->postJson("/api/admin/withdrawals/{$withdrawal->id}/approve", [
            'notes' => 'Approved for testing',
            'transaction_id' => 'TEST_TXN_001'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Wallet balance withdrawal approved successfully'
        ]);

        // Refresh user data
        $user->refresh();

        // Check that wallet balance was deducted
        $this->assertEquals(900, $user->wallet_balance); // 1000 - 100
        $this->assertEquals(500, $user->referral_earnings); // Unchanged

        // Check that a transaction record was created
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'type' => 'withdrawal',
            'amount' => -100,
            'description' => 'Withdrawal approved - Wallet Balance',
            'status' => 'completed',
            'transaction_id' => 'TEST_TXN_001',
            'reference_type' => WithdrawalRequest::class,
            'reference_id' => $withdrawal->id
        ]);

        // Check that withdrawal status was updated
        $this->assertDatabaseHas('withdrawal_requests', [
            'id' => $withdrawal->id,
            'status' => 'approved',
            'transaction_id' => 'TEST_TXN_001'
        ]);
    }

    /** @test */
    public function referral_earnings_withdrawal_is_deducted_correctly()
    {
        // Create a user with both wallet balance and referral earnings
        $user = User::factory()->create([
            'wallet_balance' => 1000,
            'referral_earnings' => 500,
            'bank_account_holder_name' => 'John Doe',
            'bank_account_number' => '1234567890',
            'bank_name' => 'Test Bank',
            'bank_account_bound_at' => now()
        ]);

        // Create a withdrawal request from referral earnings
        $withdrawal = WithdrawalRequest::factory()->create([
            'user_id' => $user->id,
            'amount' => 50,
            'payment_method_id' => 2,
            'payment_method' => 'Referral Earnings',
            'status' => 'pending'
        ]);

        // Approve the withdrawal (this should deduct from referral earnings)
        $response = $this->actingAs($user)->postJson("/api/admin/withdrawals/{$withdrawal->id}/approve", [
            'notes' => 'Approved for testing',
            'transaction_id' => 'TEST_TXN_002'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Referral earnings withdrawal approved successfully'
        ]);

        // Refresh user data
        $user->refresh();

        // Check that referral earnings were deducted
        $this->assertEquals(1000, $user->wallet_balance); // Unchanged
        $this->assertEquals(450, $user->referral_earnings); // 500 - 50

        // Check that a transaction record was created
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'type' => 'withdrawal',
            'amount' => -50,
            'description' => 'Withdrawal approved - Referral Earnings',
            'status' => 'completed',
            'transaction_id' => 'TEST_TXN_002',
            'reference_type' => WithdrawalRequest::class,
            'reference_id' => $withdrawal->id
        ]);

        // Check that withdrawal status was updated
        $this->assertDatabaseHas('withdrawal_requests', [
            'id' => $withdrawal->id,
            'status' => 'approved',
            'transaction_id' => 'TEST_TXN_002'
        ]);
    }

    /** @test */
    public function user_cannot_withdraw_more_than_available_balance()
    {
        // Create a user with limited wallet balance
        $user = User::factory()->create([
            'wallet_balance' => 50,
            'referral_earnings' => 500,
            'bank_account_holder_name' => 'John Doe',
            'bank_account_number' => '1234567890',
            'bank_name' => 'Test Bank',
            'bank_account_bound_at' => now()
        ]);

        // Create a withdrawal request for more than available wallet balance
        $withdrawal = WithdrawalRequest::factory()->create([
            'user_id' => $user->id,
            'amount' => 100, // More than available wallet balance
            'payment_method_id' => 1,
            'payment_method' => 'Wallet Balance',
            'status' => 'pending'
        ]);

        // Try to approve the withdrawal
        $response = $this->actingAs($user)->postJson("/api/admin/withdrawals/{$withdrawal->id}/approve", [
            'notes' => 'Approved for testing'
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'User has insufficient wallet balance for this withdrawal'
        ]);

        // Check that user balances are unchanged
        $user->refresh();
        $this->assertEquals(50, $user->wallet_balance);
        $this->assertEquals(500, $user->referral_earnings);

        // Check that withdrawal status is still pending
        $this->assertDatabaseHas('withdrawal_requests', [
            'id' => $withdrawal->id,
            'status' => 'pending'
        ]);
    }
}