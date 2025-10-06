<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Models\Transaction;

class WithdrawalDetailsTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin user
        $this->admin = User::factory()->create([
            'is_admin' => true
        ]);
        
        // Create regular user with some balance
        $this->user = User::factory()->create([
            'wallet_balance' => 1000,
            'bank_account_holder_name' => 'Test User',
            'bank_account_number' => '1234567890',
            'bank_name' => 'Test Bank',
            'bank_account_bound_at' => now()
        ]);
    }

    /** @test */
    public function admin_can_approve_withdrawal_and_receive_detailed_information()
    {
        // Create a withdrawal request
        $withdrawal = WithdrawalRequest::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 100,
            'payment_method_id' => 1,
            'payment_method' => 'Wallet Balance',
            'payment_details' => [
                'accountName' => 'Test User',
                'accountNumber' => '1234567890',
                'bankName' => 'Test Bank'
            ],
            'status' => 'pending'
        ]);

        // Create transaction record
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'withdrawal_request',
            'amount' => -100,
            'status' => 'pending',
            'reference_type' => WithdrawalRequest::class,
            'reference_id' => $withdrawal->id
        ]);

        // Authenticate as admin
        $this->actingAs($this->admin);

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
        
        // Check that detailed withdrawal information is returned
        $response->assertJsonStructure([
            'data' => [
                'withdrawal' => [
                    'id',
                    'userId',
                    'amount',
                    'currency',
                    'paymentMethod' => [
                        'id',
                        'name'
                    ],
                    'accountDetails' => [
                        'accountName',
                        'accountNumber',
                        'bankName'
                    ],
                    'status',
                    'requestedAt',
                    'processedAt',
                    'processedBy',
                    'notes',
                    'transactionId',
                    'transaction' => [
                        'id',
                        'transactionId',
                        'type',
                        'amount',
                        'description',
                        'status',
                        'createdAt',
                        'updatedAt'
                    ]
                ]
            ]
        ]);
        
        // Verify that the withdrawal status was updated
        $this->assertDatabaseHas('withdrawal_requests', [
            'id' => $withdrawal->id,
            'status' => 'approved',
            'notes' => 'Approved by admin',
            'transaction_id' => 'txn_12345'
        ]);
        
        // Verify that the transaction status was updated
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'completed',
            'transaction_id' => 'txn_12345'
        ]);
    }

    /** @test */
    public function user_can_view_detailed_withdrawal_information()
    {
        // Create a withdrawal request
        $withdrawal = WithdrawalRequest::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 50,
            'payment_method_id' => 2,
            'payment_method' => 'Referral Earnings',
            'payment_details' => [
                'accountName' => 'Test User',
                'accountNumber' => '1234567890',
                'bankName' => 'Test Bank'
            ],
            'status' => 'approved',
            'processed_at' => now(),
            'notes' => 'Processed successfully'
        ]);

        // Create transaction record
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'withdrawal_request',
            'amount' => -50,
            'status' => 'completed',
            'reference_type' => WithdrawalRequest::class,
            'reference_id' => $withdrawal->id,
            'transaction_id' => 'txn_67890'
        ]);

        // Authenticate as user
        $this->actingAs($this->user);

        // Get withdrawal requests
        $response = $this->getJson('/api/v1/wallet/withdrawals');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Withdrawals retrieved successfully'
        ]);
        
        // Check that detailed withdrawal information is returned
        $response->assertJsonStructure([
            'data' => [
                'withdrawals' => [
                    '*' => [
                        'id',
                        'userId',
                        'amount',
                        'currency',
                        'paymentMethod' => [
                            'id',
                            'name'
                        ],
                        'accountDetails' => [
                            'accountName',
                            'accountNumber',
                            'bankName'
                        ],
                        'status',
                        'requestedAt',
                        'processedAt',
                        'notes',
                        'transactionId',
                        'transaction' => [
                            'id',
                            'transactionId',
                            'type',
                            'amount',
                            'description',
                            'status',
                            'createdAt',
                            'updatedAt'
                        ]
                    ]
                ]
            ]
        ]);
    }
}