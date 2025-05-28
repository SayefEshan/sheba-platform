<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ServiceListingTest extends TestCase
{
    use RefreshDatabase;    

    /** @test */
    public function it_can_list_all_active_services()
    {
        // Create a category
        $category = ServiceCategory::factory()->create();

        // Create 2 active services
        Service::factory()->count(2)->create([
            'service_category_id' => $category->id
        ]);

        // Create 1 inactive service
        Service::factory()->inactive()->create([
            'service_category_id' => $category->id
        ]);

        $response = $this->getJson('/api/v1/services');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'services' => [
                        '*' => [
                            'id',
                            'name',
                            'slug',
                            'price',
                            'description',
                            'duration_minutes',
                            'is_active',
                            'service_category' => [
                                'id',
                                'name',
                                'slug',
                            ],
                        ],
                    ],
                    'pagination' => [
                        'current_page',
                        'last_page',
                        'per_page',
                        'total',
                        'from',
                        'to',
                    ],
                ],
            ]);

        // Should only return active services
        $responseData = $response->json('data.services');
        $this->assertCount(2, $responseData); // Only 2 active services
    }
}
