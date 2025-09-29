<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccessKey;
use App\Models\UserPackage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Str;

class AccessKeyController extends Controller
{
    /**
     * Display a listing of access keys
     */
    public function index(Request $request): View
    {
        $query = AccessKey::with(['package', 'creator', 'user']);

        if ($request->filled('search')) {
            $query->where('key', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('package')) {
            $query->where('package_id', $request->package);
        }

        if ($request->filled('status')) {
            if ($request->status === 'used') {
                $query->where('is_used', true);
            } elseif ($request->status === 'unused') {
                $query->where('is_used', false)->where('is_active', true);
            } elseif ($request->status === 'expired') {
                $query->where('is_active', false);
            }
        }

        $accessKeys = $query->orderBy('created_at', 'desc')->paginate(20);
        $packages = UserPackage::where('is_active', true)->get();

        return view('admin.access-keys.index', compact('accessKeys', 'packages'));
    }

    /**
     * Show the form for creating a new access key
     */
    public function create(): View
    {
        $packages = UserPackage::where('is_active', true)->get();
        return view('admin.access-keys.create', compact('packages'));
    }

    /**
     * Store a newly created access key
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'package_id' => 'required|exists:user_packages,id',
            'quantity' => 'required|integer|min:1|max:100',
            'expires_at' => 'nullable|date|after:tomorrow',
        ]);

        $accessKeys = [];
        for ($i = 0; $i < $validated['quantity']; $i++) {
            $accessKeys[] = AccessKey::create([
                'key' => strtoupper(Str::random(16)), // Generate unique access key
                'package_id' => $validated['package_id'],
                'created_by' => auth()->id(),
                'expires_at' => $validated['expires_at'],
                'is_active' => true,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => $validated['quantity'] . ' access key(s) created successfully',
            'access_keys' => $accessKeys
        ], 201);
    }

    /**
     * Display the specified access key
     */
    public function show(AccessKey $accessKey): View
    {
        $accessKey->load(['package', 'creator', 'user']);
        return view('admin.access-keys.show', compact('accessKey'));
    }

    /**
     * Remove the specified access key
     */
    public function destroy(AccessKey $accessKey): JsonResponse
    {
        // Don't allow deletion of used access keys
        if ($accessKey->is_used) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete access key that has already been used'
            ], 400);
        }

        $accessKey->delete();

        return response()->json([
            'success' => true,
            'message' => 'Access key deleted successfully'
        ]);
    }

    /**
     * Deactivate an access key
     */
    public function deactivate(AccessKey $accessKey): JsonResponse
    {
        if ($accessKey->is_used) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot deactivate access key that has already been used'
            ], 400);
        }

        $accessKey->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Access key deactivated successfully'
        ]);
    }

    /**
     * Activate an access key
     */
    public function activate(AccessKey $accessKey): JsonResponse
    {
        $accessKey->update(['is_active' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Access key activated successfully'
        ]);
    }
}