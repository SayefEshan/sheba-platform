<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Service;
use App\Models\ServiceCategory;

class ServiceController extends Controller
{
    /**
     * Display a listing of services with pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min($request->get('per_page', 10), 50); // Max 50 items per page
        $category = $request->get('category');
        $search = $request->get('search');

        $query = Service::active()
            ->withCategory()
            ->orderBy('created_at', 'desc');

        // Filter by category
        if ($category) {
            $query->whereHas('serviceCategory', function ($q) use ($category) {
                $q->where('slug', $category);
            });
        }

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
     * Display the specified service.
     */
    public function show(string $id): JsonResponse
    {
        $service = Service::active()
            ->withCategory()
            ->find($id);

        if (!$service) {
            return response()->json([
                'status' => 'error',
                'message' => 'Service not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Service retrieved successfully',
            'data' => $service
        ]);
    }

    /**
     * Get all service categories.
     */
    public function categories(): JsonResponse
    {
        $categories = ServiceCategory::active()
            ->withCount('activeServices')
            ->orderBy('name')
            ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Categories retrieved successfully',
            'data' => $categories
        ]);
    }

    /**
     * Get services by category.
     */
    public function byCategory(string $categorySlug, Request $request): JsonResponse
    {
        $category = ServiceCategory::active()
            ->where('slug', $categorySlug)
            ->first();

        if (!$category) {
            return response()->json([
                'status' => 'error',
                'message' => 'Category not found'
            ], 404);
        }

        $perPage = min($request->get('per_page', 10), 50);

        $services = Service::active()
            ->where('service_category_id', $category->id)
            ->withCategory()
            ->orderBy('name')
            ->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'message' => 'Services retrieved successfully',
            'data' => [
                'category' => $category,
                'services' => $services->items(),
                'pagination' => [
                    'current_page' => $services->currentPage(),
                    'last_page' => $services->lastPage(),
                    'per_page' => $services->perPage(),
                    'total' => $services->total(),
                ]
            ]
        ]);
    }
}
