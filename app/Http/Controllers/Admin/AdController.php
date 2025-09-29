<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdController extends Controller
{
    /**
     * Display a listing of the advertisements
     */
    public function index(Request $request)
    {
        $query = Advertisement::query();
        
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        
        $ads = $query->with('advertiser:id,name')->latest()->paginate(20);
        
        $categories = [
            'general' => 'General',
            'technology' => 'Technology',
            'business' => 'Business',
            'education' => 'Education',
            'entertainment' => 'Entertainment',
            'health' => 'Health & Fitness',
            'lifestyle' => 'Lifestyle',
            'travel' => 'Travel',
        ];
        
        $statuses = [
            'pending' => 'Pending',
            'active' => 'Active',
            'paused' => 'Paused',
            'completed' => 'Completed',
            'rejected' => 'Rejected',
        ];
        
        return view('admin.ads.index', compact('ads', 'categories', 'statuses'));
    }
    
    /**
     * Show the form for creating a new advertisement
     */
    public function create()
    {
        $advertisers = User::select('id', 'name', 'email')->get();
        
        $categories = [
            'general' => 'General',
            'technology' => 'Technology',
            'business' => 'Business',
            'education' => 'Education',
            'entertainment' => 'Entertainment',
            'health' => 'Health & Fitness',
            'lifestyle' => 'Lifestyle',
            'travel' => 'Travel',
        ];
        
        return view('admin.ads.create', compact('advertisers', 'categories'));
    }
    
    /**
     * Store a newly created advertisement
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'advertiser_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'target_url' => 'required|url',
            'category' => 'required|string',
            'budget' => 'required|numeric|min:0',
            'reward_amount' => 'required|numeric|min:0',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image_url'] = $request->file('image')->store('ad-images', 'public');
        }
        
        $validated['status'] = 'pending'; // Default status for admin review
        
        Advertisement::create($validated);
        
        return redirect()->route('admin.ads.index')
            ->with('success', 'Advertisement created successfully.');
    }
    
    /**
     * Display the specified advertisement
     */
    public function show(Advertisement $ad)
    {
        $ad->load(['advertiser:id,name,email', 'interactions' => function($q) {
            $q->latest()->limit(10);
        }]);
        
        $stats = [
            'total_impressions' => $ad->impressions,
            'total_clicks' => $ad->clicks,
            'ctr' => $ad->impressions > 0 ? ($ad->clicks / $ad->impressions * 100) : 0,
            'spent_amount' => $ad->spent,
            'remaining_budget' => $ad->budget - $ad->spent,
        ];
        
        return view('admin.ads.show', compact('ad', 'stats'));
    }
    
    /**
     * Show the form for editing the specified advertisement
     */
    public function edit(Advertisement $ad)
    {
        $advertisers = User::select('id', 'name', 'email')->get();
        
        $categories = [
            'general' => 'General',
            'technology' => 'Technology',
            'business' => 'Business',
            'education' => 'Education',
            'entertainment' => 'Entertainment',
            'health' => 'Health & Fitness',
            'lifestyle' => 'Lifestyle',
            'travel' => 'Travel',
        ];
        
        return view('admin.ads.edit', compact('ad', 'advertisers', 'categories'));
    }
    
    /**
     * Update the specified advertisement
     */
    public function update(Request $request, Advertisement $ad)
    {
        $validated = $request->validate([
            'advertiser_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'target_url' => 'required|url',
            'category' => 'required|string',
            'budget' => 'required|numeric|min:0',
            'reward_amount' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:pending,active,paused,completed,rejected',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($ad->image_url) {
                Storage::disk('public')->delete($ad->image_url);
            }
            $validated['image_url'] = $request->file('image')->store('ad-images', 'public');
        }
        
        $ad->update($validated);
        
        return redirect()->route('admin.ads.show', $ad)
            ->with('success', 'Advertisement updated successfully.');
    }
    
    /**
     * Remove the specified advertisement
     */
    public function destroy(Advertisement $ad)
    {
        // Delete associated image
        if ($ad->image_url) {
            Storage::disk('public')->delete($ad->image_url);
        }
        
        $ad->delete();
        
        return redirect()->route('admin.ads.index')
            ->with('success', 'Advertisement deleted successfully.');
    }
    
    /**
     * Approve advertisement
     */
    public function approve(Advertisement $ad)
    {
        $ad->update(['status' => 'active']);
        
        return redirect()->back()->with('success', 'Advertisement approved successfully.');
    }
    
    /**
     * Reject advertisement
     */
    public function reject(Advertisement $ad, Request $request)
    {
        $ad->update(['status' => 'rejected']);
        
        return redirect()->back()->with('success', 'Advertisement rejected successfully.');
    }
    
    /**
     * Pause advertisement
     */
    public function pause(Advertisement $ad)
    {
        $ad->update(['status' => 'paused']);
        
        return redirect()->back()->with('success', 'Advertisement paused successfully.');
    }
}