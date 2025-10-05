<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SupportOption;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class SupportOptionController extends Controller
{
    /**
     * Display a listing of the support options.
     */
    public function index(): JsonResponse
    {
        $supportOptions = SupportOption::active()
            ->orderBy('sort_order')
            ->get();

        $result = [];
        foreach ($supportOptions as $option) {
            // Manually generate the WhatsApp link
            $whatsappLink = null;
            if ($option->whatsapp_number && $option->whatsapp_message) {
                $number = preg_replace('/[^0-9]/', '', $option->whatsapp_number);
                $message = rawurlencode($option->whatsapp_message);
                $whatsappLink = "https://wa.me/{$number}?text={$message}";
            }
            
            $result[] = [
                'id' => $option->id,
                'title' => $option->title,
                'description' => $option->description,
                'avatar' => $option->avatar ? Storage::url($option->avatar) : null,
                'whatsapp_link' => $whatsappLink,
                'whatsapp_number' => $option->whatsapp_number,
                'whatsapp_message' => $option->whatsapp_message,
                'sort_order' => $option->sort_order,
                'is_active' => $option->is_active,
                'created_at' => $option->created_at,
                'updated_at' => $option->updated_at,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $result,
            'message' => 'Support options retrieved successfully'
        ]);
    }

    /**
     * Store a newly created support option in storage.
     */
    public function store(Request $request): JsonResponse
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

        $supportOption = SupportOption::create($validated);

        return response()->json([
            'success' => true,
            'data' => $supportOption,
            'message' => 'Support option created successfully'
        ], 201);
    }

    /**
     * Display the specified support option.
     */
    public function show(SupportOption $supportOption): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $supportOption,
            'message' => 'Support option retrieved successfully'
        ]);
    }

    /**
     * Update the specified support option in storage.
     */
    public function update(Request $request, SupportOption $supportOption): JsonResponse
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
            if ($supportOption->avatar) {
                Storage::disk('public')->delete($supportOption->avatar);
            }
            
            $validated['avatar'] = $request->file('avatar')->store('support-avatars', 'public');
        }

        $supportOption->update($validated);

        return response()->json([
            'success' => true,
            'data' => $supportOption,
            'message' => 'Support option updated successfully'
        ]);
    }

    /**
     * Remove the specified support option from storage.
     */
    public function destroy(SupportOption $supportOption): JsonResponse
    {
        // Delete avatar if it exists
        if ($supportOption->avatar) {
            Storage::disk('public')->delete($supportOption->avatar);
        }
        
        $supportOption->delete();

        return response()->json([
            'success' => true,
            'message' => 'Support option deleted successfully'
        ]);
    }

    /**
     * Get only active support options for public API.
     */
    public function publicIndex(): JsonResponse
    {
        $supportOptions = SupportOption::active()
            ->orderBy('sort_order')
            ->get();

        $result = [];
        foreach ($supportOptions as $option) {
            // Manually generate the WhatsApp link
            $whatsappLink = null;
            if ($option->whatsapp_number && $option->whatsapp_message) {
                $number = preg_replace('/[^0-9]/', '', $option->whatsapp_number);
                $message = rawurlencode($option->whatsapp_message);
                $whatsappLink = "https://wa.me/{$number}?text={$message}";
            }
            
            $result[] = [
                'id' => $option->id,
                'title' => $option->title,
                'description' => $option->description,
                'avatar' => $option->avatar ? Storage::url($option->avatar) : null,
                'whatsapp_link' => $whatsappLink,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $result,
            'message' => 'Support options retrieved successfully'
        ]);
    }
}