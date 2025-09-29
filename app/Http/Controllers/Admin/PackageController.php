<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserPackage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class PackageController extends Controller
{
    /**
     * Display a listing of user packages
     */
    public function index(Request $request): View
    {
        $query = UserPackage::withCount('users');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $packages = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.packages.index', compact('packages'));
    }

    /**
     * Show the form for creating a new package
     */
    public function create(): View
    {
        return view('admin.packages.create');
    }

    /**
     * Store a newly created package
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'nullable|integer|min:1',
            'features' => 'nullable|array',
            'ad_views_limit' => 'nullable|integer|min:0',
            'course_access_limit' => 'nullable|integer|min:0',
            'marketplace_access' => 'nullable|boolean',
            'brain_teaser_access' => 'nullable|boolean',
            'is_active' => 'nullable|boolean'
        ]);

        // Convert checkbox values
        $validated['marketplace_access'] = $request->has('marketplace_access');
        $validated['brain_teaser_access'] = $request->has('brain_teaser_access');
        $validated['is_active'] = $request->has('is_active');

        $package = UserPackage::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Package created successfully',
            'package' => $package
        ], 201);
    }

    /**
     * Display the specified package
     */
    public function show(UserPackage $package): View
    {
        $package->loadCount('users');
        
        $subscribers = User::where('current_package_id', $package->id)
            ->with(['transactions' => function ($query) use ($package) {
                $query->where('package_id', $package->id);
            }])
            ->paginate(20);

        $stats = [
            'total_subscribers' => User::where('current_package_id', $package->id)->count(),
            'active_subscribers' => User::where('current_package_id', $package->id)
                ->where(function ($query) {
                    $query->whereNull('package_expires_at')
                          ->orWhere('package_expires_at', '>', now());
                })
                ->count(),
            'total_revenue' => $this->calculatePackageRevenue($package),
            'monthly_revenue' => $this->calculateMonthlyRevenue($package)
        ];

        return view('admin.packages.show', compact('package', 'subscribers', 'stats'));
    }

    /**
     * Show the form for editing the specified package
     */
    public function edit(UserPackage $package): View
    {
        $package->loadCount('users');
        return view('admin.packages.edit', compact('package'));
    }

    /**
     * Update the specified package
     */
    public function update(Request $request, UserPackage $package): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'nullable|integer|min:1',
            'features' => 'nullable|array',
            'ad_views_limit' => 'nullable|integer|min:0',
            'course_access_limit' => 'nullable|integer|min:0',
            'marketplace_access' => 'nullable|boolean',
            'brain_teaser_access' => 'nullable|boolean',
            'is_active' => 'nullable|boolean'
        ]);

        // Convert checkbox values
        $validated['marketplace_access'] = $request->has('marketplace_access');
        $validated['brain_teaser_access'] = $request->has('brain_teaser_access');
        $validated['is_active'] = $request->has('is_active');

        $package->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Package updated successfully',
            'package' => $package->fresh()
        ]);
    }

    /**
     * Remove the specified package
     */
    public function destroy(UserPackage $package): JsonResponse
    {
        // Check if package has active subscribers
        $activeSubscribers = User::where('current_package_id', $package->id)
            ->where(function ($query) {
                $query->whereNull('package_expires_at')
                      ->orWhere('package_expires_at', '>', now());
            })
            ->count();

        if ($activeSubscribers > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete package with active subscribers'
            ], 400);
        }

        $package->delete();

        return response()->json([
            'success' => true,
            'message' => 'Package deleted successfully'
        ]);
    }

    /**
     * Activate a package
     */
    public function activate(UserPackage $package): JsonResponse
    {
        $package->update(['is_active' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Package activated successfully'
        ]);
    }

    /**
     * Deactivate a package
     */
    public function deactivate(UserPackage $package): JsonResponse
    {
        $package->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Package deactivated successfully'
        ]);
    }

    /**
     * Get package subscribers
     */
    public function subscribers(UserPackage $package): JsonResponse
    {
        $subscribers = User::where('current_package_id', $package->id)
            ->with(['transactions' => function ($query) use ($package) {
                $query->where('package_id', $package->id)->latest();
            }])
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $subscribers
        ]);
    }

    /**
     * Calculate total revenue for a package
     */
    private function calculatePackageRevenue(UserPackage $package): float
    {
        // This would be implemented based on your transaction model
        // return Transaction::where('package_id', $package->id)
        //     ->where('status', 'completed')
        //     ->sum('amount');
        return 0; // Placeholder
    }

    /**
     * Calculate monthly revenue for a package
     */
    private function calculateMonthlyRevenue(UserPackage $package): float
    {
        // This would be implemented based on your transaction model
        // return Transaction::where('package_id', $package->id)
        //     ->where('status', 'completed')
        //     ->whereMonth('created_at', now()->month)
        //     ->whereYear('created_at', now()->year)
        //     ->sum('amount');
        return 0; // Placeholder
    }
}