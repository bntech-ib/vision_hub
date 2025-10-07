<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Advertisement;
use App\Models\AdInteraction;
use App\Models\User;

class AdvertisementInteractionsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_access_ad_interactions_through_adinteractions_relationship()
    {
        // Create an advertisement
        $advertisement = Advertisement::factory()->create();
        
        // Create a user
        $user = User::factory()->create();
        
        // Create some ad interactions
        AdInteraction::factory()->count(3)->create([
            'advertisement_id' => $advertisement->id,
            'user_id' => $user->id
        ]);

        // Test accessing adInteractions relationship
        $this->assertEquals(3, $advertisement->adInteractions()->count());
        
        // Load the advertisement with adInteractions
        $advertisementWithInteractions = Advertisement::with('adInteractions')->find($advertisement->id);
        $this->assertEquals(3, $advertisementWithInteractions->adInteractions->count());
    }

    /** @test */
    public function it_can_access_ad_interactions_through_interactions_alias()
    {
        // Create an advertisement
        $advertisement = Advertisement::factory()->create();
        
        // Create a user
        $user = User::factory()->create();
        
        // Create some ad interactions
        AdInteraction::factory()->count(2)->create([
            'advertisement_id' => $advertisement->id,
            'user_id' => $user->id
        ]);

        // Test accessing interactions relationship (alias)
        $this->assertEquals(2, $advertisement->interactions()->count());
        
        // Load the advertisement with interactions
        $advertisementWithInteractions = Advertisement::with('adInteractions')->find($advertisement->id);
        $this->assertEquals(2, $advertisementWithInteractions->interactions->count());
    }

    /** @test */
    public function both_relationships_return_the_same_data()
    {
        // Create an advertisement
        $advertisement = Advertisement::factory()->create();
        
        // Create a user
        $user = User::factory()->create();
        
        // Create some ad interactions
        AdInteraction::factory()->count(1)->create([
            'advertisement_id' => $advertisement->id,
            'user_id' => $user->id
        ]);

        // Load the advertisement with both relationships
        $advertisementWithAdInteractions = Advertisement::with('adInteractions')->find($advertisement->id);
        $advertisementWithInteractions = Advertisement::with('adInteractions')->find($advertisement->id);
        
        // Both should return the same data
        $this->assertEquals(
            $advertisementWithAdInteractions->adInteractions->count(),
            $advertisementWithInteractions->interactions->count()
        );
        
        $this->assertEquals(
            $advertisementWithAdInteractions->adInteractions->first()->id,
            $advertisementWithInteractions->interactions->first()->id
        );
    }
}