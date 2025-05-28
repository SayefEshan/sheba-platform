<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Booking;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookingStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_retrieve_booking_status()
    {
        // Create a service
        $service = Service::factory()->create();

        // Create a booking
        $booking = Booking::factory()->create([
            'service_id' => $service->id,
            'status' => 'pending',
            'scheduled_at' => now()->addDays(2)
        ]);

        $response = $this->getJson("/api/v1/bookings/{$booking->booking_id}/status");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'booking_id',
                    'status',
                    'status_color',
                    'scheduled_at',
                    'confirmed_at',
                    'completed_at',
                    'created_at',
                    'can_be_cancelled'
                ]
            ])
            ->assertJson([
                'status' => 'success',
                'message' => 'Booking status retrieved successfully',
                'data' => [
                    'booking_id' => $booking->booking_id,
                    'status' => 'pending',
                    'status_color' => 'warning',
                    'can_be_cancelled' => true
                ]
            ]);
    }

    public function test_it_returns_404_for_invalid_booking_id()
    {
        $response = $this->getJson('/api/v1/bookings/INVALID123/status');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'No query results for model [App\\Models\\Booking] INVALID123'
            ]);
    }

}
