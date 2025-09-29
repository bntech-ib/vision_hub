@extends('admin.layouts.app')

@section('content')
<div class="container">
    <h1>Create Sponsored Post</h1>
    <form action="{{ route('admin.sponsored-posts.store') }}" method="POST">
        @csrf
        @include('admin.sponsored-posts.partials.form')
        <button type="submit" class="btn btn-primary">Create</button>
        <a href="{{ route('admin.sponsored-posts.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
