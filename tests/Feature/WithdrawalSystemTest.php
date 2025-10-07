<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Models\Transaction;

class WithdrawalSystemTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test user with some balance
        $this->user = User::factory()->create([
            'wallet_balance' => 1000,
            'referral_earnings' => 500,
            'bank_account_holder_name' => 'Test User',
            'bank_account_number' => '1234567890',
            'bank_name' => 'Test Bank'
        ]);
    }

    /** @test */
    public function user_can_request_withdrawal_from_wallet_balance()
    {
        $this->actingAs($this->user);

        // Request withdrawal from wallet balance
        $response = $this->postJson('/api/v1/wallet/withdraw', [
            'amount' => 100,
            'payment_method_id' => 1 // Wallet balance
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Withdrawal request submitted successfully'
        ]);

        // Check that the amount was deducted from wallet balance
        $this->assertEquals(900, $this->user->fresh()->wallet_balance);
        $this->assertEquals(500, $this->user->fresh()->referral_earnings); // Should be unchanged

        // Check that withdrawal request was created
        $this->assertDatabaseHas('withdrawal_requests', [
            'user_id' => $this->user->id,
            'amount' => 100,
            'payment_method_id' => 1,
            'status' => 'pending'
        ]);

        // Check that transaction record was created
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'type' => 'withdrawal_request',
            'amount' => -100,
            'status' => 'pending'
        ]);
    }

    /** @test */
    public function user_can_request_withdrawal_from_referral_earnings()
    {
        $this->actingAs($this->user);

        // Request withdrawal from referral earnings
        $response = $this->postJson('/api/v1/wallet/withdraw', [
            'amount' => 50,
            'payment_method_id' => 2 // Referral earnings
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Withdrawal request submitted successfully'
        ]);

        // Check that the amount was deducted from referral earnings
        $this->assertEquals(1000, $this->user->fresh()->wallet_balance); // Should be unchanged
        $this->assertEquals(450, $this->user->fresh()->referral_earnings);

        // Check that withdrawal request was created
        $this->assertDatabaseHas('withdrawal_requests', [
            'user_id' => $this->user->id,
            'amount' => 50,
            'payment_method_id' => 2,
            'status' => 'pending'
        ]);

        // Check that transaction record was created
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'type' => 'withdrawal_request',
            'amount' => -50,
            'status' => 'pending'
        ]);
    }

    /** @test */
    public function admin_can_approve_withdrawal_without_duplicate_deduction()
    {
        // Create a withdrawal request
        $withdrawal = WithdrawalRequest::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 100,
            'payment_method_id' => 1,
            'status' => 'pending'
        ]);

        // Create transaction record
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'withdrawal_request',
            'amount' => -100,
            'status' => 'pending',
            'reference_type' => WithdrawalRequest::class,
            'reference_id' => $withdrawal->id
        ]);

        // Create admin user
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        // Approve withdrawal
        $response = $this->postJson("/admin/withdrawals/{$withdrawal->id}/approve", [
            'notes' => 'Approved by admin',
            'transaction_id' => 'txn_12345'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Wallet balance withdrawal approved successfully'
        ]);

        // Check that user balances are unchanged (no duplicate deduction)
        $this->assertEquals(1000, $this->user->fresh()->wallet_balance);
        $this->assertEquals(500, $this->user->fresh()->referral_earnings);

        // Check that withdrawal status was updated
        $this->assertDatabaseHas('withdrawal_requests', [
            'id' => $withdrawal->id,
            'status' => 'approved'
        ]);

        // Check that transaction status was updated
        $this->assertDatabaseHas('transactions', [
            'reference_type' => WithdrawalRequest::class,
            'reference_id' => $withdrawal->id,
            'status' => 'completed',
            'transaction_id' => 'txn_12345'
        ]);
    }

    /** @test */
    public function admin_can_reject_withdrawal_and_refund_amount()
    {
        // Create a withdrawal request
        $withdrawal = WithdrawalRequest::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 100,
            'payment_method_id' => 1,
            'status' => 'pending'
        ]);

        // Create transaction record
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'withdrawal_request',
            'amount' => -100,
            'status' => 'pending',
            'reference_type' => WithdrawalRequest::class,
            'reference_id' => $withdrawal->id
        ]);

        // Create admin user
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        // Reject withdrawal
        $response = $this->postJson("/admin/withdrawals/{$withdrawal->id}/reject", [
            'reason' => 'Invalid bank details'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Withdrawal rejected successfully and amount refunded'
        ]);

        // Check that the amount was refunded to wallet balance
        $this->assertEquals(1100, $this->user->fresh()->wallet_balance); // Original 1000 + refunded 100
        $this->assertEquals(500, $this->user->fresh()->referral_earnings); // Should be unchanged

        // Check that withdrawal status was updated
        $this->assertDatabaseHas('withdrawal_requests', [
            'id' => $withdrawal->id,
            'status' => 'rejected'
        ]);

        // Check that original transaction status was updated to refunded
        $this->assertDatabaseHas('transactions', [
            'reference_type' => WithdrawalRequest::class,
            'reference_id' => $withdrawal->id,
            'status' => 'refunded'
        ]);

        // Check that refund transaction was created
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'type' => 'refund',
            'amount' => 100,
            'status' => 'completed'
        ]);
    }
}