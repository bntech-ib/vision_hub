<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ImageUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \\Illuminate\\Contracts\\Validation\\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'images' => 'required|array|min:1|max:10',
            'images.*' => [
                'required',
                'file',
                'image',
                'mimes:jpeg,png,jpg,gif,webp,svg',
                'max:10240', // 10MB
                'dimensions:min_width=10,min_height=10,max_width=8000,max_height=8000',
            ],
            'names' => 'nullable|array',
            'names.*' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'images.required' => 'Please select at least one image to upload.',
            'images.max' => 'You can upload a maximum of 10 images at once.',
            'images.*.required' => 'Each file must be a valid image.',
            'images.*.image' => 'The file must be an image.',
            'images.*.mimes' => 'The image must be a file of type: jpeg, png, jpg, gif, webp, svg.',
            'images.*.max' => 'The image must not be larger than 10MB.',
            'images.*.dimensions' => 'The image dimensions must be between 10x10 and 8000x8000 pixels.',
        ];
    }
}