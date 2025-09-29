<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Course;
use App\Models\Advertisement;
use App\Models\Product;
use App\Models\SponsoredPost;
use App\Models\Image;
use App\Models\User;

class ImageRelationshipTest extends TestCase
{
    /**
     * Test that Course model can have images
     */
    public function test_course_can_have_images()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['instructor_id' => $user->id]);
        $image = Image::factory()->create(['uploaded_by' => $user->id]);
        
        $course->images()->attach($image->id);
        
        $this->assertTrue($course->images->contains($image));
        $this->assertEquals(1, $course->images()->count());
    }
    
    /**
     * Test that Advertisement model can have images
     */
    public function test_advertisement_can_have_images()
    {
        $user = User::factory()->create();
        $ad = Advertisement::factory()->create(['advertiser_id' => $user->id]);
        $image = Image::factory()->create(['uploaded_by' => $user->id]);
        
        $ad->images()->attach($image->id);
        
        $this->assertTrue($ad->images->contains($image));
        $this->assertEquals(1, $ad->images()->count());
    }
    
    /**
     * Test that Product model can have images
     */
    public function test_product_can_have_images()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['seller_id' => $user->id]);
        $image = Image::factory()->create(['uploaded_by' => $user->id]);
        
        $product->imageRecords()->attach($image->id);
        
        $this->assertTrue($product->imageRecords->contains($image));
        $this->assertEquals(1, $product->imageRecords()->count());
    }
    
    /**
     * Test that SponsoredPost model can have images
     */
    public function test_sponsored_post_can_have_images()
    {
        $user = User::factory()->create();
        $post = SponsoredPost::factory()->create(['user_id' => $user->id]);
        $image = Image::factory()->create(['uploaded_by' => $user->id]);
        
        $post->images()->attach($image->id);
        
        $this->assertTrue($post->images->contains($image));
        $this->assertEquals(1, $post->images()->count());
    }
}