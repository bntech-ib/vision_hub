<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\UserPackage; // Changed from Package to UserPackage
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
        $packages = UserPackage::all(); // Changed from Package to UserPackage
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
        $packages = UserPackage::where('is_active', true) // Changed from Package to UserPackage
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
            'daily_earning_limit' => 'required|numeric|min:0',
            'ad_limits' => 'required|integer|min:0',
            'course_access_limit' => 'nullable|integer|min:0',
            'marketplace_access' => 'boolean',
            'brain_teaser_access' => 'boolean',
            'is_active' => 'boolean',
            'referral_earning_percentage' => 'nullable|numeric|min:0|max:100'
        ]);

        $package = UserPackage::create($validatedData); // Changed from Package to UserPackage

        return response()->json([
            'success' => true,
            'message' => 'Package created successfully',
            'data' => $package
        ], 201);
    }

    /**
     * Display the specified package.
     *
     * @param  \App\Models\UserPackage  $package // Changed from Package to UserPackage
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(UserPackage $package): JsonResponse // Changed from Package to UserPackage
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
     * @param  \App\Models\UserPackage  $package // Changed from Package to UserPackage
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, UserPackage $package): JsonResponse // Changed from Package to UserPackage
    {
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'duration_days' => 'sometimes|required|integer|min:1',
            'features' => 'nullable|array',
            'ad_views_limit' => 'integer|min:0',
            'daily_earning_limit' => 'sometimes|required|numeric|min:0',
            'ad_limits' => 'sometimes|required|integer|min:0',
            'course_access_limit' => 'nullable|integer|min:0',
            'marketplace_access' => 'boolean',
            'brain_teaser_access' => 'boolean',
            'is_active' => 'boolean',
            'referral_earning_percentage' => 'nullable|numeric|min:0|max:100'
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
     * @param  \App\Models\UserPackage  $package // Changed from Package to UserPackage
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(UserPackage $package): JsonResponse // Changed from Package to UserPackage
    {
        $package->delete();

        return response()->json([
            'success' => true,
            'message' => 'Package deleted successfully'
        ]);
    }
}