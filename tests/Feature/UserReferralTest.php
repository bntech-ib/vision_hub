<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserReferralTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_have_referrals()
    {
        // Create a referrer user
        $referrer = User::factory()->create([
            'referral_code' => 'ABC123',
        ]);

        // Create referred users
        $referred1 = User::factory()->create([
            'referred_by' => $referrer->id,
        ]);

        $referred2 = User::factory()->create([
            'referred_by' => $referrer->id,
        ]);

        // Check that the referrer has 2 referrals
        $this->assertEquals(2, $referrer->referrals()->count());

        // Check that the referred users have the correct referrer
        $this->assertEquals($referrer->id, $referred1->referredBy->id);
        $this->assertEquals($referrer->id, $referred2->referredBy->id);

        // Check that the referrals relationship works
        $referrals = $referrer->referrals;
        $this->assertCount(2, $referrals);
        $this->assertTrue($referrals->contains($referred1));
        $this->assertTrue($referrals->contains($referred2));
    }

    /** @test */
    public function user_without_referrals_has_zero_count()
    {
        $user = User::factory()->create([
            'referral_code' => 'DEF456',
        ]);

        $this->assertEquals(0, $user->referrals()->count());
    }

    /** @test */
    public function user_can_be_referred_by_another_user()
    {
        $referrer = User::factory()->create([
            'referral_code' => 'GHI789',
        ]);

        $referred = User::factory()->create([
            'referred_by' => $referrer->id,
        ]);

        $this->assertEquals($referrer->id, $referred->referredBy->id);
    }
}