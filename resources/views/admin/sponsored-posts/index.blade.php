@extends('admin.layouts.app')

@section('content')
<div class="container">
    <h1>Sponsored Posts</h1>
    <a href="{{ route('admin.sponsored-posts.create') }}" class="btn btn-primary mb-3">Create Sponsored Post</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Category</th>
                <th>Status</th>
                <th>Budget</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($posts as $post)
            <tr>
                <td>{{ $post->id }}</td>
                <td>{{ $post->title }}</td>
                <td>{{ $post->category }}</td>
                <td>{{ ucfirst($post->status) }}</td>
                <td>{{ $post->budget }}</td>
                <td>{{ $post->start_date }}</td>
                <td>{{ $post->end_date }}</td>
                <td>
                    <a href="{{ route('admin.sponsored-posts.show', $post) }}" class="btn btn-info btn-sm">View</a>
                    <a href="{{ route('admin.sponsored-posts.edit', $post) }}" class="btn btn-warning btn-sm">Edit</a>
                    <form action="{{ route('admin.sponsored-posts.destroy', $post) }}" method="POST" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $posts->links() }}
</div>
@endsection