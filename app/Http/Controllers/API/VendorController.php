<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VendorAccessKey;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class VendorController extends Controller
{
    /**
     * Get vendor access keys with buyer information
     */
    public function accessKeys(Request $request): JsonResponse
    {
        // Only vendors can access their own access keys
        $user = $request->user();
        
        if (!$user->isVendor()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only vendors can access this endpoint.'
            ], 403);
        }

        $query = $user->createdVendorAccessKeys()
            ->with(['accessKey.package', 'buyer'])
            ->orderBy('created_at', 'desc');

        // Filter by status if provided
        if ($request->filled('status')) {
            if ($request->status === 'sold') {
                $query->where('is_sold', true);
            } elseif ($request->status === 'unsold') {
                $query->where('is_sold', false);
            }
        }

        // Filter by package if provided
        if ($request->filled('package_id')) {
            $query->whereHas('accessKey', function ($q) use ($request) {
                $q->where('package_id', $request->package_id);
            });
        }

        $vendorAccessKeys = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $vendorAccessKeys,
            'message' => 'Vendor access keys retrieved successfully'
        ]);
    }

    /**
     * Get vendor statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isVendor()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only vendors can access this endpoint.'
            ], 403);
        }

        $stats = [
            'total_access_keys' => $user->getTotalVendorAccessKeys(),
            'sold_access_keys' => $user->getTotalSoldVendorAccessKeys(),
            'total_earnings' => $user->getTotalVendorEarnings(),
            'unsold_access_keys' => $user->getTotalVendorAccessKeys() - $user->getTotalSoldVendorAccessKeys(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Vendor statistics retrieved successfully'
        ]);
    }
}