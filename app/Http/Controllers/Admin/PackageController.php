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
     * Display a listing of the packages
     */
    public function index(Request $request): View
    {
        $query = UserPackage::withCount('users');
        
        // Apply filters if provided
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        if ($request->has('status')) {
            if ($request->status == 'active') {
                $query->where('is_active', true);
            } elseif ($request->status == 'inactive') {
                $query->where('is_active', false);
            }
        }
        
        if ($request->has('min_price') && $request->min_price) {
            $query->where('price', '>=', $request->min_price);
        }
        
        $packages = $query->paginate(15);
        
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
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'features' => 'nullable|string',
            'ad_views_limit' => 'integer|min:0',
            'daily_earning_limit' => 'numeric|min:0',
            'ad_limits' => 'integer|min:0',
            'course_access_limit' => 'nullable|integer|min:0',
            'marketplace_access' => 'boolean',
            'brain_teaser_access' => 'boolean',
            'is_active' => 'boolean',
            'referral_earning_percentage' => 'nullable|numeric|min:0|regex:/^\d+(\.\d{1,2})?$/'
        ]);

        // Convert checkbox values
        $validated['marketplace_access'] = $request->has('marketplace_access');
        $validated['brain_teaser_access'] = $request->has('brain_teaser_access');
        $validated['is_active'] = $request->has('is_active');
        
        // Process features if provided as JSON string
        if (isset($validated['features'])) {
            $features = json_decode($validated['features'], true);
            if (is_array($features)) {
                $validated['features'] = $features;
            } else {
                // If it's not valid JSON, treat as comma-separated string
                $validated['features'] = array_filter(array_map('trim', explode(',', $validated['features'])));
            }
        }

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
            ->with(['transactions' => function ($query) {
                // Transactions are linked to users, not packages directly
                // We'll load all transactions for users with this package
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
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'duration_days' => 'sometimes|required|integer|min:1',
            'features' => 'nullable|string',
            'ad_views_limit' => 'integer|min:0',
            'daily_earning_limit' => 'numeric|min:0',
            'ad_limits' => 'integer|min:0',
            'course_access_limit' => 'nullable|integer|min:0',
            'marketplace_access' => 'boolean',
            'brain_teaser_access' => 'boolean',
            'is_active' => 'boolean',
            'referral_earning_percentage' => 'nullable|numeric|min:0|regex:/^\d+(\.\d{1,2})?$/'
        ]);

        // Convert checkbox values
        $validated['marketplace_access'] = $request->has('marketplace_access');
        $validated['brain_teaser_access'] = $request->has('brain_teaser_access');
        $validated['is_active'] = $request->has('is_active');
        
        // Process features if provided as JSON string
        if (isset($validated['features'])) {
            $features = json_decode($validated['features'], true);
            if (is_array($features)) {
                $validated['features'] = $features;
            } else {
                // If it's not valid JSON, treat as comma-separated string
                $validated['features'] = array_filter(array_map('trim', explode(',', $validated['features'])));
            }
        }

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
            ->with(['transactions' => function ($query) {
                // Transactions are linked to users, not packages directly
                $query->latest();
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
        // Get all users with this package
        $userIds = User::where('current_package_id', $package->id)->pluck('id');
        
        // Sum transactions for these users
        return \App\Models\Transaction::whereIn('user_id', $userIds)
            ->where('status', 'completed')
            ->where('type', 'purchase') // Only count purchase transactions
            ->sum('amount');
    }

    /**
     * Calculate monthly revenue for a package
     */
    private function calculateMonthlyRevenue(UserPackage $package): float
    {
        // Get all users with this package
        $userIds = User::where('current_package_id', $package->id)->pluck('id');
        
        // Sum transactions for these users in the current month
        return \App\Models\Transaction::whereIn('user_id', $userIds)
            ->where('status', 'completed')
            ->where('type', 'purchase') // Only count purchase transactions
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');
    }
}