<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\StoreBookingRequest;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBookingRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();

            // Get the service to book
            $service = Service::active()->find($validated['service_id']);

            if (!$service) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Service is not available for booking'
                ], 400);
            }

            // Create the booking
            $booking = Booking::create([
                'service_id' => $service->id,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_email' => $validated['customer_email'] ?? null,
                'customer_address' => $validated['customer_address'] ?? null,
                'service_price' => $service->price,
                'scheduled_at' => $validated['scheduled_at'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'pending',
            ]);

            // Load the booking with service information
            $booking->load(['service', 'service.serviceCategory']);

            DB::commit();

            // TODO: Send notification (SMS/Email) - will implement in bonus features

            Log::info('New booking created', ['booking_id' => $booking->booking_id]);

            return response()->json([
                'status' => 'success',
                'message' => 'Booking created successfully',
                'data' => [
                    'booking' => $booking,
                    'booking_id' => $booking->booking_id,
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Booking creation failed', [
                'error' => $e->getMessage(),
                'request_data' => $request->validated()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create booking. Please try again.'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Booking $booking): JsonResponse
    {
        $booking->load(['service', 'service.serviceCategory']);

        return response()->json([
            'status' => 'success',
            'message' => 'Booking retrieved successfully',
            'data' => $booking
        ]);
    }

    public function status(Booking $booking): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Booking status retrieved successfully',
            'data' => [
                'booking_id' => $booking->booking_id,
                'status' => $booking->status,
                'status_color' => $booking->status_color,
                'scheduled_at' => $booking->scheduled_at,
                'confirmed_at' => $booking->confirmed_at,
                'completed_at' => $booking->completed_at,
                'created_at' => $booking->created_at,
                'can_be_cancelled' => $booking->canBeCancelled(),
            ]
        ]);
    }

    /**
     * Cancel a booking (if allowed).
     */
    public function cancel(Booking $booking): JsonResponse
    {
        if (!$booking->canBeCancelled()) {
            return response()->json([
                'status' => 'error',
                'message' => 'This booking cannot be cancelled'
            ], 400);
        }

        $booking->update(['status' => 'cancelled']);

        Log::info('Booking cancelled', ['booking_id' => $booking->booking_id]);

        return response()->json([
            'status' => 'success',
            'message' => 'Booking cancelled successfully'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
