<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    /**
     * Display a listing of bookings with filtering and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min($request->get('per_page', 20), 100);
        $status = $request->get('status');
        $search = $request->get('search');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $serviceId = $request->get('service_id');

        $query = Booking::withService()
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        // Filter by service
        if ($serviceId) {
            $query->where('service_id', $serviceId);
        }

        // Search functionality
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('booking_id', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        // Date range filter
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $bookings = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'message' => 'Bookings retrieved successfully',
            'data' => [
                'bookings' => $bookings->items(),
                'pagination' => [
                    'current_page' => $bookings->currentPage(),
                    'last_page' => $bookings->lastPage(),
                    'per_page' => $bookings->perPage(),
                    'total' => $bookings->total(),
                    'from' => $bookings->firstItem(),
                    'to' => $bookings->lastItem(),
                ]
            ]
        ]);
    }

    /**
     * Display the specified booking.
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

    /**
     * Update booking status.
     */
    public function updateStatus(Request $request, Booking $booking): JsonResponse
    {
        $request->validate([
            'status' => [
                'required',
                Rule::in(['pending', 'confirmed', 'in_progress', 'completed', 'cancelled'])
            ],
            'admin_notes' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $oldStatus = $booking->status;
            $newStatus = $request->status;

            // Validate status transitions
            if (!$this->isValidStatusTransition($oldStatus, $newStatus)) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Cannot change status from '{$oldStatus}' to '{$newStatus}'"
                ], 400);
            }

            $updateData = [
                'status' => $newStatus,
            ];

            if ($request->filled('admin_notes')) {
                $updateData['admin_notes'] = $request->admin_notes;
            }

            // Set timestamps based on status
            switch ($newStatus) {
                case 'confirmed':
                    if (!$booking->confirmed_at) {
                        $updateData['confirmed_at'] = now();
                    }
                    break;
                case 'completed':
                    if (!$booking->completed_at) {
                        $updateData['completed_at'] = now();
                    }
                    if (!$booking->confirmed_at) {
                        $updateData['confirmed_at'] = now();
                    }
                    break;
            }

            $booking->update($updateData);

            DB::commit();

            Log::info('Booking status updated by admin', [
                'booking_id' => $booking->booking_id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'admin_id' => $request->user()->id
            ]);

            // TODO: Send notification to customer (SMS/Email) - bonus feature

            return response()->json([
                'status' => 'success',
                'message' => 'Booking status updated successfully',
                'data' => [
                    'booking_id' => $booking->booking_id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'updated_at' => $booking->updated_at
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Booking status update failed', [
                'error' => $e->getMessage(),
                'booking_id' => $booking->booking_id,
                'admin_id' => $request->user()->id
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update booking status. Please try again.'
            ], 500);
        }
    }

    /**
     * Get dashboard statistics.
     */
    public function dashboardStats(): JsonResponse
    {
        try {
            $stats = DB::select("
                SELECT 
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_bookings,
                    COUNT(CASE WHEN status = 'confirmed' THEN 1 END) as confirmed_bookings,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_bookings,
                    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_bookings,
                    COUNT(*) as total_bookings,
                    SUM(CASE WHEN status = 'completed' THEN service_price ELSE 0 END) as total_revenue,
                    COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as today_bookings
                FROM bookings
            ")[0];

            // Recent bookings
            $recentBookings = Booking::withService()
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            // Top services by booking count
            $topServices = DB::select("
                SELECT 
                    s.id,
                    s.name,
                    COUNT(b.id) as booking_count,
                    SUM(CASE WHEN b.status = 'completed' THEN b.service_price ELSE 0 END) as revenue
                FROM services s
                LEFT JOIN bookings b ON s.id = b.service_id
                GROUP BY s.id, s.name
                ORDER BY booking_count DESC
                LIMIT 5
            ");

            // Monthly revenue (last 6 months)
            $monthlyRevenue = DB::select("
                SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    SUM(CASE WHEN status = 'completed' THEN service_price ELSE 0 END) as revenue,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_bookings
                FROM bookings 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month DESC
            ");

            return response()->json([
                'status' => 'success',
                'message' => 'Dashboard statistics retrieved successfully',
                'data' => [
                    'overview' => [
                        'pending_bookings' => (int) $stats->pending_bookings,
                        'confirmed_bookings' => (int) $stats->confirmed_bookings,
                        'completed_bookings' => (int) $stats->completed_bookings,
                        'cancelled_bookings' => (int) $stats->cancelled_bookings,
                        'total_bookings' => (int) $stats->total_bookings,
                        'total_revenue' => (float) $stats->total_revenue,
                        'today_bookings' => (int) $stats->today_bookings,
                    ],
                    'recent_bookings' => $recentBookings,
                    'top_services' => $topServices,
                    'monthly_revenue' => $monthlyRevenue,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Dashboard stats retrieval failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve dashboard statistics'
            ], 500);
        }
    }

    /**
     * Check if status transition is valid.
     */
    private function isValidStatusTransition(string $from, string $to): bool
    {
        $validTransitions = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['in_progress', 'cancelled', 'completed'],
            'in_progress' => ['completed', 'cancelled'],
            'completed' => [], // Cannot change from completed
            'cancelled' => [], // Cannot change from cancelled
        ];

        return in_array($to, $validTransitions[$from] ?? []);
    }

    /**
     * Store a newly created booking (admin can create bookings for customers).
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'required|string|regex:/^(\+8801|01)[3-9]\d{8}$/|max:20',
            'customer_email' => 'nullable|email|max:100',
            'customer_address' => 'nullable|string|max:500',
            'scheduled_at' => 'nullable|date|after:now',
            'notes' => 'nullable|string|max:1000',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $service = \App\Models\Service::active()->find($request->service_id);

            if (!$service) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Service is not available for booking'
                ], 400);
            }

            $booking = Booking::create([
                'service_id' => $service->id,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'customer_email' => $request->customer_email,
                'customer_address' => $request->customer_address,
                'service_price' => $service->price,
                'scheduled_at' => $request->scheduled_at,
                'notes' => $request->notes,
                'admin_notes' => $request->admin_notes,
                'status' => 'pending',
            ]);

            $booking->load(['service', 'service.serviceCategory']);

            DB::commit();

            Log::info('Booking created by admin', [
                'booking_id' => $booking->booking_id,
                'admin_id' => $request->user()->id
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Booking created successfully',
                'data' => $booking
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Admin booking creation failed', [
                'error' => $e->getMessage(),
                'admin_id' => $request->user()->id
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create booking. Please try again.'
            ], 500);
        }
    }

    /**
     * Remove the specified booking (soft delete or hard delete based on business logic).
     */
    public function destroy(Booking $booking): JsonResponse
    {
        try {
            // Only allow deletion of pending or cancelled bookings
            if (!in_array($booking->status, ['pending', 'cancelled'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Only pending or cancelled bookings can be deleted'
                ], 400);
            }

            $bookingId = $booking->booking_id;
            $booking->delete();

            Log::info('Booking deleted by admin', [
                'booking_id' => $bookingId,
                'admin_id' => request()->user()->id
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Booking deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Booking deletion failed', [
                'error' => $e->getMessage(),
                'booking_id' => $booking->booking_id,
                'admin_id' => request()->user()->id
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete booking. Please try again.'
            ], 500);
        }
    }
}
