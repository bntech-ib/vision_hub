<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BrainTeaser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BrainTeaserController extends Controller
{
    /**
     * Display a listing of the brain teasers
     */
    public function index(Request $request)
    {
        $query = BrainTeaser::query();
        
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
        
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        
        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $brainTeasers = $query->latest()->paginate(20);
        
        $categories = [
            'logic' => 'Logic',
            'math' => 'Math',
            'riddle' => 'Riddle',
            'word' => 'Word Play',
            'visual' => 'Visual',
            'other' => 'Other',
        ];
        
        $difficulties = [
            'easy' => 'Easy',
            'medium' => 'Medium',
            'hard' => 'Hard',
        ];
        
        $statuses = [
            'draft' => 'Draft',
            'pending' => 'Pending Review',
            'active' => 'Active',
            'archived' => 'Archived',
        ];
        
        return view('admin.brain-teasers.index', compact('brainTeasers', 'categories', 'difficulties', 'statuses'));
    }
    
    /**
     * Show the form for creating a new brain teaser
     */
    public function create()
    {
        $creators = User::select('id', 'name', 'email')->get();
        
        $categories = [
            'logic' => 'Logic',
            'math' => 'Math',
            'riddle' => 'Riddle',
            'word' => 'Word Play',
            'visual' => 'Visual',
            'other' => 'Other',
        ];
        
        $difficulties = [
            'easy' => 'Easy',
            'medium' => 'Medium',
            'hard' => 'Hard',
        ];
        
        $statuses = [
            'draft' => 'Draft',
            'pending' => 'Pending Review',
            'active' => 'Active',
            'archived' => 'Archived',
        ];
        
        return view('admin.brain-teasers.create', compact('creators', 'categories', 'difficulties', 'statuses'));
    }
    
    /**
     * Store a newly created brain teaser
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'question' => 'required|string',
            'options' => 'required|array|min:2',
            'options.*' => 'string|max:255',
            'correct_answer' => 'required|string|max:255',
            'explanation' => 'required|string',
            'category' => 'required|string',
            'difficulty' => 'required|string|in:easy,medium,hard',
            'reward_amount' => 'required|numeric|min:0',
            'status' => 'required|string|in:draft,pending,active,archived',
            'is_daily' => 'boolean',
            'creator_id' => 'nullable|exists:users,id',
        ]);
        
        // Convert options array to JSON
        $validated['options'] = json_encode($validated['options']);
        
        // Set the creator (either from form or current user)
        $validated['created_by'] = $validated['creator_id'] ?? Auth::id();
        
        BrainTeaser::create($validated);
        
        return redirect()->route('admin.brain-teasers.index')
            ->with('success', 'Brain teaser created successfully.');
    }
    
    /**
     * Display the specified brain teaser
     */
    public function show(BrainTeaser $brainTeaser)
    {
        $brainTeaser->load(['creator:id,name,email', 'attempts' => function($q) {
            $q->latest()->limit(10);
        }]);
        
        $stats = [
            'total_attempts' => $brainTeaser->total_attempts,
            'correct_attempts' => $brainTeaser->correct_attempts,
            'success_rate' => $brainTeaser->total_attempts > 0 ? ($brainTeaser->correct_attempts / $brainTeaser->total_attempts * 100) : 0,
        ];
        
        // Decode options for display
        $options = json_decode($brainTeaser->options, true);
        
        return view('admin.brain-teasers.show', compact('brainTeaser', 'stats', 'options'));
    }
    
    /**
     * Show the form for editing the specified brain teaser
     */
    public function edit(BrainTeaser $brainTeaser)
    {
        $creators = User::select('id', 'name', 'email')->get();
        
        $categories = [
            'logic' => 'Logic',
            'math' => 'Math',
            'riddle' => 'Riddle',
            'word' => 'Word Play',
            'visual' => 'Visual',
            'other' => 'Other',
        ];
        
        $difficulties = [
            'easy' => 'Easy',
            'medium' => 'Medium',
            'hard' => 'Hard',
        ];
        
        $statuses = [
            'draft' => 'Draft',
            'pending' => 'Pending Review',
            'active' => 'Active',
            'archived' => 'Archived',
        ];
        
        // Decode options for editing
        $options = json_decode($brainTeaser->options, true);
        
        return view('admin.brain-teasers.edit', compact('brainTeaser', 'creators', 'categories', 'difficulties', 'statuses', 'options'));
    }
    
    /**
     * Update the specified brain teaser
     */
    public function update(Request $request, BrainTeaser $brainTeaser)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'question' => 'required|string',
            'options' => 'required|array|min:2',
            'options.*' => 'string|max:255',
            'correct_answer' => 'required|string|max:255',
            'explanation' => 'required|string',
            'category' => 'required|string',
            'difficulty' => 'required|string|in:easy,medium,hard',
            'reward_amount' => 'required|numeric|min:0',
            'status' => 'required|string|in:draft,pending,active,archived',
            'is_daily' => 'boolean',
        ]);
        
        // Convert options array to JSON
        $validated['options'] = json_encode($validated['options']);
        
        $brainTeaser->update($validated);
        
        return redirect()->route('admin.brain-teasers.show', $brainTeaser)
            ->with('success', 'Brain teaser updated successfully.');
    }
    
    /**
     * Remove the specified brain teaser
     */
    public function destroy(BrainTeaser $brainTeaser)
    {
        $brainTeaser->delete();
        
        return redirect()->route('admin.brain-teasers.index')
            ->with('success', 'Brain teaser deleted successfully.');
    }
}