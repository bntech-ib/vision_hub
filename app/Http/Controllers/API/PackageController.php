<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class PackageController extends Controller
{
    /**
     * Display a listing of the packages.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $packages = Package::all();
        return response()->json([
            'success' => true,
            'data' => $packages
        ]);
    }

    /**
     * Display a listing of available (active) packages.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function available(): JsonResponse
    {
        $packages = Package::where('is_active', true)
            ->orderBy('price')
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => $packages,
            'message' => 'Available packages retrieved successfully'
        ]);
    }

    /**
     * Store a newly created package in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'features' => 'nullable|array',
            'ad_views_limit' => 'integer|min:0',
            'course_access_limit' => 'nullable|integer|min:0',
            'marketplace_access' => 'boolean',
            'brain_teaser_access' => 'boolean',
            'is_active' => 'boolean'
        ]);

        $package = Package::create($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Package created successfully',
            'data' => $package
        ], 201);
    }

    /**
     * Display the specified package.
     *
     * @param  \App\Models\Package  $package
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Package $package): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $package
        ]);
    }

    /**
     * Update the specified package in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Package  $package
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Package $package): JsonResponse
    {
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'duration_days' => 'sometimes|required|integer|min:1',
            'features' => 'nullable|array',
            'ad_views_limit' => 'integer|min:0',
            'course_access_limit' => 'nullable|integer|min:0',
            'marketplace_access' => 'boolean',
            'brain_teaser_access' => 'boolean',
            'is_active' => 'boolean'
        ]);

        $package->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Package updated successfully',
            'data' => $package
        ]);
    }

    /**
     * Remove the specified package from storage.
     *
     * @param  \App\Models\Package  $package
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Package $package): JsonResponse
    {
        $package->delete();

        return response()->json([
            'success' => true,
            'message' => 'Package deleted successfully'
        ]);
    }
}