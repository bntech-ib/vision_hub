<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportOption;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class SupportController extends Controller
{
    /**
     * Display a listing of the support options.
     */
    public function index(): View
    {
        $supportOptions = SupportOption::orderBy('sort_order')->paginate(10);
        
        return view('admin.support.index', compact('supportOptions'));
    }

    /**
     * Show the form for creating a new support option.
     */
    public function create(): View
    {
        return view('admin.support.create');
    }

    /**
     * Store a newly created support option in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'whatsapp_number' => 'nullable|string|max:255',
            'whatsapp_message' => 'nullable|string|max:1000',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('support-avatars', 'public');
        }

        SupportOption::create($validated);

        return redirect()->route('admin.support.index')
            ->with('success', 'Support option created successfully.');
    }

    /**
     * Display the specified support option.
     */
    public function show(SupportOption $support): View
    {
        return view('admin.support.show', compact('support'));
    }

    /**
     * Show the form for editing the specified support option.
     */
    public function edit(SupportOption $support): View
    {
        return view('admin.support.edit', compact('support'));
    }

    /**
     * Update the specified support option in storage.
     */
    public function update(Request $request, SupportOption $support): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'whatsapp_number' => 'nullable|string|max:255',
            'whatsapp_message' => 'nullable|string|max:1000',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if it exists
            if ($support->avatar) {
                Storage::disk('public')->delete($support->avatar);
            }
            
            $validated['avatar'] = $request->file('avatar')->store('support-avatars', 'public');
        }

        $support->update($validated);

        return redirect()->route('admin.support.index')
            ->with('success', 'Support option updated successfully.');
    }

    /**
     * Remove the specified support option from storage.
     */
    public function destroy(SupportOption $supportOption): RedirectResponse
    {
        // Delete avatar if it exists
        if ($supportOption->avatar) {
            Storage::disk('public')->delete($supportOption->avatar);
        }
        
        $supportOption->delete();

        return redirect()->route('admin.support.index')
            ->with('success', 'Support option deleted successfully.');
    }
}