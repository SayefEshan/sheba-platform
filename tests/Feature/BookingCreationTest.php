<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookingCreationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_booking()
    {
        // Create an active service
        $service = Service::factory()->create([
            'price' => 1500.00,
            'is_active' => true
        ]);

        $bookingData = [
            'service_id' => $service->id,
            'customer_name' => 'John Doe',
            'customer_phone' => '01712345678',
            'customer_email' => 'john@example.com',
            'customer_address' => '123 Main St, Dhaka',
            'scheduled_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'notes' => 'Please bring cleaning supplies'
        ];

        $response = $this->postJson('/api/v1/bookings', $bookingData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'booking' => [
                        'id',
                        'booking_id',
                        'service_id',
                        'customer_name',
                        'customer_phone',
                        'customer_email',
                        'customer_address',
                        'service_price',
                        'status',
                        'scheduled_at',
                        'notes',
                        'created_at',
                        'updated_at',
                        'service' => [
                            'id',
                            'name',
                            'slug',
                            'price',
                            'description',
                            'duration_minutes',
                            'service_category' => [
                                'id',
                                'name',
                                'slug'
                            ]
                        ]
                    ],
                    'booking_id'
                ]
            ]);

        // Verify the booking was created with correct data
        $this->assertDatabaseHas('bookings', [
            'service_id' => $service->id,
            'customer_name' => 'John Doe',
            'customer_phone' => '01712345678',
            'customer_email' => 'john@example.com',
            'customer_address' => '123 Main St, Dhaka',
            'service_price' => 1500.00,
            'status' => 'pending'
        ]);
    }

    /** @test */
    public function it_cannot_book_inactive_service()
    {
        // Create an inactive service
        $service = Service::factory()->create([
            'is_active' => false
        ]);

        $bookingData = [
            'service_id' => $service->id,
            'customer_name' => 'John Doe',
            'customer_phone' => '01712345678',
            'customer_email' => 'john@example.com'
        ];

        $response = $this->postJson('/api/v1/bookings', $bookingData);

        $response->assertStatus(400)
            ->assertJson([
                'status' => 'error',
                'message' => 'Service is not available for booking'
            ]);
    }
}
