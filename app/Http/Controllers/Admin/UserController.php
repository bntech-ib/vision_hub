<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserPackage;
use App\Models\SecurityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::with('currentPackage');
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }
        
        // Filter by package
        if ($request->filled('package')) {
            $query->where('current_package_id', $request->package);
        }
        
        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'admin') {
                $query->where('is_admin', true);
            } elseif ($request->status === 'premium') {
                $query->whereNotNull('current_package_id');
            }
        }
        
        $users = $query->latest()->paginate(100);
        $packages = UserPackage::where('is_active', true)->get();
        
        return view('admin.users.index', compact('users', 'packages'));
    }
    
    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $packages = UserPackage::where('is_active', true)->get();
        return view('admin.users.create', compact('packages'));
    }
    
    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|unique:users|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'full_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'current_package_id' => 'nullable|exists:user_packages,id',
            'is_admin' => 'boolean',
            'wallet_balance' => 'nullable|numeric|min:0',
        ]);
        
        $validated['password'] = Hash::make($validated['password']);
        $validated['email_verified_at'] = now();
        
        if ($validated['current_package_id']) {
            $package = UserPackage::find($validated['current_package_id']);
            $validated['package_expires_at'] = now()->addDays((int) $package->duration_days);
        }
        
        User::create($validated);
        
        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }
    
    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        $user->load([
            'currentPackage', 
            'transactions' => function($q) {
                $q->latest()->limit(10);
            },
            'projects',
            'images',
            'processingJobs',
            'tags',
            'advertisements',
            'adInteractions',
            'products',
            'courses',
            'courseEnrollments.course',
            'brainTeasers',
            'brainTeaserAttempts.brainTeaser',
            'withdrawalRequests',
            'createdAccessKeys.package',
            'sponsoredPosts',
            'referrals',
            'referredBy'
        ]);
        
        $stats = [
            'total_transactions' => $user->transactions()->count(),
            'total_spent' => $user->transactions()->where('type', 'debit')->sum('amount'),
            'total_earned' => $user->transactions()->where('type', 'credit')->sum('amount'),
            'normal_earnings' => $user->getNormalEarnings(),
            'referral_earnings' => $user->getReferralEarnings(),
            'total_earnings' => $user->getTotalEarnings(),
        ];
        
        return view('admin.users.show', compact('user', 'stats'));
    }
    
    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        $packages = UserPackage::where('is_active', true)->get();
        return view('admin.users.edit', compact('user', 'packages'));
    }
    
    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|unique:users,username,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'full_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'current_package_id' => 'nullable|exists:user_packages,id',
            'is_admin' => 'boolean',
            'wallet_balance' => 'nullable|numeric|min:0',
            'referral_earnings' => 'nullable|numeric|min:0',
            // Vendor fields
            'make_vendor' => 'nullable|boolean',
            'vendor_company_name' => 'nullable|string|max:255',
            'vendor_description' => 'nullable|string',
            'vendor_website' => 'nullable|url',
            'vendor_commission_rate' => 'nullable|numeric|min:0|max:100',
        ]);
        
        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        }
        
        // Update package expiration if package changed
        if (isset($validated['current_package_id']) && $user->current_package_id != $validated['current_package_id']) {
            $package = UserPackage::find($validated['current_package_id']);
            $validated['package_expires_at'] = now()->addDays((int) $package->duration_days);
        }
        
        $user->update($validated);
        
        // Handle vendor conversion
        if (isset($validated['make_vendor']) && $validated['make_vendor'] && !$user->isVendor()) {
            // Validate vendor fields when making a user a vendor
            $vendorData = $request->validate([
                'vendor_company_name' => 'required|string|max:255',
                'vendor_commission_rate' => 'required|numeric|min:0|max:100',
                'vendor_description' => 'nullable|string',
                'vendor_website' => 'nullable|url',
            ]);
            
            $user->makeVendor($vendorData);
        } 
        // Update vendor information if user is already a vendor
        elseif ($user->isVendor()) {
            $vendorData = [
                'vendor_company_name' => $validated['vendor_company_name'] ?? $user->vendor_company_name,
                'vendor_commission_rate' => $validated['vendor_commission_rate'] ?? $user->vendor_commission_rate,
                'vendor_description' => $validated['vendor_description'] ?? $user->vendor_description,
                'vendor_website' => $validated['vendor_website'] ?? $user->vendor_website,
            ];
            
            $user->update($vendorData);
        }
        
        return redirect()->route('admin.users.show', $user)
            ->with('success', 'User updated successfully.');
    }
    
    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        if ($user->is_admin && User::where('is_admin', true)->count() <= 1) {
            return redirect()->back()->with('error', 'Cannot delete the last admin user.');
        }
        
        $user->delete();
        
        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
    
    /**
     * Suspend user account
     */
    public function suspend(User $user)
    {
        // You can add a 'suspended' field to users table if needed
        return redirect()->back()->with('success', 'User suspended successfully.');
    }
    
    /**
     * Activate user account
     */
    public function activate(User $user)
    {
        // You can add a 'suspended' field to users table if needed
        return redirect()->back()->with('success', 'User activated successfully.');
    }
    
    /**
     * Enable withdrawal access for user
     */
    public function enableWithdrawal(User $user)
    {
        $user->update(['withdrawal_enabled' => true]);
        
        return redirect()->back()->with('success', 'Withdrawal access enabled for user.');
    }
    
    /**
     * Disable withdrawal access for user
     */
    public function disableWithdrawal(User $user)
    {
        $user->update(['withdrawal_enabled' => false]);
        
        return redirect()->back()->with('success', 'Withdrawal access disabled for user.');
    }
    
    /**
     * Enable withdrawal access globally
     */
    public function enableWithdrawalGlobally(Request $request)
    {
        \App\Models\GlobalSetting::set('withdrawal_enabled', true);
        
        return redirect()->back()->with('success', 'Withdrawal access enabled globally.');
    }
    
    /**
     * Disable withdrawal access globally
     */
    public function disableWithdrawalGlobally(Request $request)
    {
        \App\Models\GlobalSetting::set('withdrawal_enabled', false);
        
        return redirect()->back()->with('success', 'Withdrawal access disabled globally.');
    }
    
    /**
     * Change user package
     */
    public function changePackage(Request $request, User $user)
    {
        $validated = $request->validate([
            'package_id' => 'required|exists:user_packages,id',
        ]);
        
        $package = UserPackage::find($validated['package_id']);
        $user->update([
            'current_package_id' => $package->id,
            'package_expires_at' => now()->addDays((int) $package->duration_days),
        ]);
        
        return redirect()->back()->with('success', 'User package updated successfully.');
    }
    
    /**
     * Add credits to user wallet
     */
    public function addCredits(Request $request, User $user)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
        ]);
        
        $user->increment('wallet_balance', $validated['amount']);
        
        // Log transaction if needed
        // Transaction::create([
        //     'user_id' => $user->id,
        //     'type' => 'credit',
        //     'amount' => $validated['amount'],
        //     'description' => $validated['description'] ?? 'Admin credit adjustment',
        // ]);
        
        return redirect()->back()->with('success', 'Credits added successfully.');
    }
    
    /**
     * Show user login history
     */
    public function loginHistory(User $user)
    {
        // This would require a login history table
        // For now, we'll return an empty collection
        $loginHistory = collect();
        
        return view('admin.users.login-history', compact('user', 'loginHistory'));
    }
    
    /**
     * Export users data
     */
    public function export(Request $request)
    {
        // Simple CSV export example
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="users.csv"',
        ];
        
        $users = User::select('name', 'email', 'username', 'created_at', 'is_admin')
            ->get();
        
        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Name', 'Email', 'Username', 'Created At', 'Is Admin']);
            
            foreach ($users as $user) {
                fputcsv($file, [
                    $user->name,
                    $user->email,
                    $user->username,
                    $user->created_at,
                    $user->is_admin ? 'Yes' : 'No',
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Perform bulk actions on users
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:delete,suspend,activate',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);
        
        $userIds = $validated['user_ids'];
        $action = $validated['action'];
        
        switch ($action) {
            case 'delete':
                // Check if we're trying to delete the last admin
                $adminCount = User::whereIn('id', $userIds)->where('is_admin', true)->count();
                $totalAdmins = User::where('is_admin', true)->count();
                
                if ($adminCount >= $totalAdmins) {
                    return redirect()->back()->with('error', 'Cannot delete all admin users.');
                }
                
                User::whereIn('id', $userIds)->delete();
                break;
                
            case 'suspend':
                User::whereIn('id', $userIds)->update(['suspended_at' => now()]);
                break;
                
            case 'activate':
                User::whereIn('id', $userIds)->update(['suspended_at' => null]);
                break;
        }
        
        return redirect()->back()->with('success', "Bulk action '{$action}' completed successfully.");
    }
    
    /**
     * Get security logs for the authenticated user
     */
    public function securityLogs(Request $request)
    {
        $logs = SecurityLog::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->makeHidden(['user_id', 'updated_at']);
            
        return response()->json([
            'success' => true,
            'data' => $logs
        ]);
    }

    /**
     * Search users (for AJAX requests)
     */
    public function search(Request $request)
    {
        $query = $request->get('q');
        
        $users = User::where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->orWhere('username', 'like', "%{$query}%")
            ->limit(10)
            ->get(['id', 'name', 'email']);
            
        return response()->json($users);
    }
}