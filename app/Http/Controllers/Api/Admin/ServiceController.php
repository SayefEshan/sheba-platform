<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;

class ServiceController extends Controller
{
    /**
     * Display a listing of services with filtering and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min($request->get('per_page', 15), 100);
        $category = $request->get('category');
        $status = $request->get('status'); // active, inactive, all
        $search = $request->get('search');

        $query = Service::with('serviceCategory')
            ->orderBy('created_at', 'desc');

        // Filter by category
        if ($category) {
            $query->whereHas('serviceCategory', function ($q) use ($category) {
                $q->where('slug', $category);
            });
        }

        // Filter by status
        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }
        // 'all' shows both active and inactive

        // Search functionality
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $services = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'message' => 'Services retrieved successfully',
            'data' => [
                'services' => $services->items(),
                'pagination' => [
                    'current_page' => $services->currentPage(),
                    'last_page' => $services->lastPage(),
                    'per_page' => $services->perPage(),
                    'total' => $services->total(),
                    'from' => $services->firstItem(),
                    'to' => $services->lastItem(),
                ]
            ]
        ]);
    }

    /**
     * Store a newly created service.
     */
    public function store(StoreServiceRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();

            // Generate slug from name
            $validated['slug'] = Str::slug($validated['name']);

            // Ensure slug is unique
            $originalSlug = $validated['slug'];
            $counter = 1;
            while (Service::where('slug', $validated['slug'])->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }

            $service = Service::create($validated);

            // Load the service with category
            $service->load('serviceCategory');

            DB::commit();

            Log::info('Service created by admin', [
                'service_id' => $service->id,
                'admin_id' => Auth::id()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Service created successfully',
                'data' => $service
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Service creation failed', [
                'error' => $e->getMessage(),
                'admin_id' => Auth::id(),
                'request_data' => $request->validated()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create service. Please try again.'
            ], 500);
        }
    }

    /**
     * Display the specified service.
     */
    public function show(Service $service): JsonResponse
    {
        $service->load(['serviceCategory', 'bookings' => function ($query) {
            $query->select('id', 'service_id', 'status', 'created_at')
                ->orderBy('created_at', 'desc')
                ->limit(10);
        }]);

        return response()->json([
            'status' => 'success',
            'message' => 'Service retrieved successfully',
            'data' => $service
        ]);
    }

    /**
     * Update the specified service.
     */
    public function update(UpdateServiceRequest $request, Service $service): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();

            // If name is being updated, regenerate slug
            if (isset($validated['name']) && $validated['name'] !== $service->name) {
                $newSlug = Str::slug($validated['name']);

                // Ensure slug is unique (excluding current service)
                $originalSlug = $newSlug;
                $counter = 1;
                while (Service::where('slug', $newSlug)->where('id', '!=', $service->id)->exists()) {
                    $newSlug = $originalSlug . '-' . $counter;
                    $counter++;
                }

                $validated['slug'] = $newSlug;
            }

            $service->update($validated);

            // Load the service with category
            $service->load('serviceCategory');

            DB::commit();

            Log::info('Service updated by admin', [
                'service_id' => $service->id,
                'admin_id' => Auth::id()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Service updated successfully',
                'data' => $service
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Service update failed', [
                'error' => $e->getMessage(),
                'service_id' => $service->id,
                'admin_id' => Auth::id(),
                'request_data' => $request->validated()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update service. Please try again.'
            ], 500);
        }
    }

    /**
     * Remove the specified service.
     */
    public function destroy(Service $service): JsonResponse
    {
        try {
            // Check if service has any bookings
            $bookingsCount = $service->bookings()->count();

            if ($bookingsCount > 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot delete service with existing bookings. Deactivate it instead.'
                ], 400);
            }

            $serviceId = $service->id;
            $service->delete();

            Log::info('Service deleted by admin', [
                'service_id' => $serviceId,
                'admin_id' => request()->user()->id
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Service deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Service deletion failed', [
                'error' => $e->getMessage(),
                'service_id' => $service->id,
                'admin_id' => request()->user()->id
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete service. Please try again.'
            ], 500);
        }
    }

    /**
     * Toggle service active status.
     */
    public function toggleStatus(Service $service): JsonResponse
    {
        try {
            $service->update(['is_active' => !$service->is_active]);

            $status = $service->is_active ? 'activated' : 'deactivated';

            Log::info("Service {$status} by admin", [
                'service_id' => $service->id,
                'admin_id' => request()->user()->id,
                'new_status' => $service->is_active
            ]);

            return response()->json([
                'status' => 'success',
                'message' => "Service {$status} successfully",
                'data' => [
                    'id' => $service->id,
                    'is_active' => $service->is_active
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Service status toggle failed', [
                'error' => $e->getMessage(),
                'service_id' => $service->id,
                'admin_id' => request()->user()->id
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update service status. Please try again.'
            ], 500);
        }
    }
}
