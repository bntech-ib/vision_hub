<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SponsoredPost;
use Illuminate\Http\Request;

class SponsoredPostController extends Controller
{
    public function index()
    {
        $posts = SponsoredPost::orderByDesc('created_at')->paginate(15);
        return view('admin.sponsored-posts.index', compact('posts'));
    }

    public function create()
    {
        $post = new SponsoredPost();
        return view('admin.sponsored-posts.create', compact('post'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image_url' => 'required|url',
            'target_url' => 'required|url',
            'category' => 'required|string|max:100',
            'budget' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive,pending,completed,rejected',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);
        $post = SponsoredPost::create($validated);
        return redirect()->route('admin.sponsored-posts.index')->with('success', 'Sponsored post created successfully.');
    }

    public function show(SponsoredPost $sponsored_post)
    {
        return view('admin.sponsored-posts.show', compact('sponsored_post'));
    }

    public function edit(SponsoredPost $sponsored_post)
    {
        return view('admin.sponsored-posts.edit', compact('sponsored_post'));
    }

    public function update(Request $request, SponsoredPost $sponsored_post)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image_url' => 'required|url',
            'target_url' => 'required|url',
            'category' => 'required|string|max:100',
            'budget' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive,pending,completed,rejected',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);
        $sponsored_post->update($validated);
        return redirect()->route('admin.sponsored-posts.index')->with('success', 'Sponsored post updated successfully.');
    }

    public function destroy(SponsoredPost $sponsored_post)
    {
        $sponsored_post->delete();
        return redirect()->route('admin.sponsored-posts.index')->with('success', 'Sponsored post deleted successfully.');
    }
}