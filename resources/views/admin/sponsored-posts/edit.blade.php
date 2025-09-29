@extends('admin.layouts.app')

@section('content')
<div class="container">
    <h1>Edit Sponsored Post</h1>
    <form action="{{ route('admin.sponsored-posts.update', $sponsored_post->id) }}" method="POST">
        @csrf
        @method('PUT')
        @include('admin.sponsored-posts.partials.form', ['post' => $sponsored_post])
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('admin.sponsored-posts.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection