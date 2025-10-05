<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserPackage;
use App\Models\AccessKey;
use App\Models\VendorAccessKey;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Str;

class VendorController extends Controller
{
    /**
     * Display a listing of vendors
     */
    public function index(Request $request): View
    {
        $query = User::where('is_vendor', true)->withCount(['createdVendorAccessKeys', 'soldVendorAccessKeys']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('vendor_company_name', 'like', '%' . $request->search . '%');
            });
        }

        $vendors = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.vendors.index', compact('vendors'));
    }

    /**
     * Show the form for creating a new vendor
     */
    public function create(): View
    {
        $users = User::where('is_vendor', false)->get();
        return view('admin.vendors.create', compact('users'));
    }

    /**
     * Store a newly created vendor
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'vendor_company_name' => 'required|string|max:255',
            'vendor_description' => 'nullable|string',
            'vendor_website' => 'nullable|url',
            'vendor_commission_rate' => 'required|numeric|min:0|max:100',
        ]);

        $user = User::findOrFail($validated['user_id']);
        
        // Make the user a vendor
        $user->makeVendor([
            'vendor_company_name' => $validated['vendor_company_name'],
            'vendor_description' => $validated['vendor_description'],
            'vendor_website' => $validated['vendor_website'],
            'vendor_commission_rate' => $validated['vendor_commission_rate'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User successfully converted to vendor',
            'vendor' => $user
        ], 201);
    }

    /**
     * Display the specified vendor
     */
    public function show(User $vendor): View
    {
        $vendor->load(['createdVendorAccessKeys.accessKey.package', 'soldVendorAccessKeys.buyer']);
        
        // Get statistics
        $stats = [
            'total_access_keys' => $vendor->getTotalVendorAccessKeys(),
            'sold_access_keys' => $vendor->getTotalSoldVendorAccessKeys(),
            'total_earnings' => $vendor->getTotalVendorEarnings(),
        ];

        return view('admin.vendors.show', compact('vendor', 'stats'));
    }

    /**
     * Show the form for editing the specified vendor
     */
    public function edit(User $vendor): View
    {
        return view('admin.vendors.edit', compact('vendor'));
    }

    /**
     * Update the specified vendor
     */
    public function update(Request $request, User $vendor): JsonResponse
    {
        $validated = $request->validate([
            'vendor_company_name' => 'required|string|max:255',
            'vendor_description' => 'nullable|string',
            'vendor_website' => 'nullable|url',
            'vendor_commission_rate' => 'required|numeric|min:0|max:100',
        ]);

        $vendor->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Vendor updated successfully',
            'vendor' => $vendor->fresh()
        ]);
    }

    /**
     * Remove the specified vendor
     */
    public function destroy(User $vendor): JsonResponse
    {
        // Check if vendor has any sold access keys
        if ($vendor->getTotalSoldVendorAccessKeys() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot remove vendor with sold access keys'
            ], 400);
        }

        // Remove vendor status but keep the user account
        $vendor->update([
            'is_vendor' => false,
            'vendor_company_name' => null,
            'vendor_description' => null,
            'vendor_website' => null,
            'vendor_commission_rate' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Vendor status removed successfully'
        ]);
    }

    /**
     * Generate access keys for a vendor
     */
    public function generateAccessKeys(Request $request, User $vendor): JsonResponse
    {
        $validated = $request->validate([
            'package_id' => 'required|exists:user_packages,id',
            'quantity' => 'required|integer|min:1|max:100',
            'expires_at' => 'nullable|date|after:tomorrow',
            'commission_rate' => 'required|numeric|min:0|max:100',
        ]);

        $package = UserPackage::findOrFail($validated['package_id']);
        
        $vendorAccessKeys = [];
        for ($i = 0; $i < $validated['quantity']; $i++) {
            // Create the actual access key
            $accessKey = AccessKey::create([
                'key' => strtoupper(Str::random(16)),
                'package_id' => $validated['package_id'],
                'created_by' => auth()->id(),
                'expires_at' => $validated['expires_at'] ?? null,
                'is_active' => true,
            ]);

            // Create the vendor access key record
            $vendorAccessKeys[] = VendorAccessKey::create([
                'vendor_id' => $vendor->id,
                'access_key_id' => $accessKey->id,
                'commission_rate' => $validated['commission_rate'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => $validated['quantity'] . ' access key(s) generated for vendor successfully',
            'vendor_access_keys' => $vendorAccessKeys
        ], 201);
    }

    /**
     * Get vendor access keys
     */
    public function accessKeys(User $vendor, Request $request): View
    {
        $query = $vendor->createdVendorAccessKeys()
            ->with(['accessKey.package', 'buyer'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            if ($request->status === 'sold') {
                $query->where('is_sold', true);
            } elseif ($request->status === 'unsold') {
                $query->where('is_sold', false);
            }
        }

        $vendorAccessKeys = $query->paginate(20);

        return view('admin.vendors.access-keys', compact('vendor', 'vendorAccessKeys'));
    }
}