@extends('admin.layouts.app')

@section('content')
<div class="container">
    <h1>Sponsored Post Details</h1>
    <div class="card">
        <div class="card-body">
            <h3>{{ $sponsored_post->title }}</h3>
            <p><strong>Category:</strong> {{ $sponsored_post->category }}</p>
            <p><strong>Status:</strong> {{ ucfirst($sponsored_post->status) }}</p>
            <p><strong>Budget:</strong> {{ $sponsored_post->budget }}</p>
            <p><strong>Start Date:</strong> {{ $sponsored_post->start_date }}</p>
            <p><strong>End Date:</strong> {{ $sponsored_post->end_date }}</p>
            <p><strong>Description:</strong> {{ $sponsored_post->description }}</p>
            <p><strong>Image:</strong><br><img src="{{ $sponsored_post->image_url }}" alt="Image" style="max-width:200px;"></p>
            <p><strong>Target URL:</strong> <a href="{{ $sponsored_post->target_url }}" target="_blank">{{ $sponsored_post->target_url }}</a></p>
        </div>
    </div>
    <a href="{{ route('admin.sponsored-posts.edit', $sponsored_post->id) }}" class="btn btn-warning mt-3">Edit</a>
    <a href="{{ route('admin.sponsored-posts.index') }}" class="btn btn-secondary mt-3">Back to List</a>
</div>
@endsection