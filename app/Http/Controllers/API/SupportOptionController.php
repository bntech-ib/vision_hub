<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SupportOption;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

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

        return response()->json([
            'success' => true,
            'data' => $supportOptions,
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
            'icon' => 'nullable|string|max:255',
            'whatsapp_number' => 'nullable|string|max:255',
            'whatsapp_message' => 'nullable|string|max:1000',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

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
            'icon' => 'nullable|string|max:255',
            'whatsapp_number' => 'nullable|string|max:255',
            'whatsapp_message' => 'nullable|string|max:1000',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

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
                'icon' => $option->icon,
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