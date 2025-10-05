@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Support Options</h3>
                    <a href="{{ route('admin.support.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Add New Support Option
                    </a>
                </div>
                <div class="card-body">
                    @if($supportOptions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Avatar</th>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Sort Order</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($supportOptions as $supportOption)
                                        <tr>
                                            <td>{{ $supportOption->id }}</td>
                                            <td>
                                                @if($supportOption->avatar)
                                                    <img src="{{ Storage::url($supportOption->avatar) }}" alt="Avatar" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                                @else
                                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                        <i class="bi bi-person"></i>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>{{ $supportOption->title }}</td>
                                            <td>{{ Str::limit($supportOption->description, 50) }}</td>
                                            <td>{{ $supportOption->sort_order }}</td>
                                            <td>
                                                @if($supportOption->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td>{{ $supportOption->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.support.show', $supportOption) }}" 
                                                       class="btn btn-sm btn-info" 
                                                       title="View">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.support.edit', $supportOption) }}" 
                                                       class="btn btn-sm btn-warning" 
                                                       title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <form action="{{ route('admin.support.destroy', $supportOption) }}" 
                                                          method="POST" 
                                                          onsubmit="return confirm('Are you sure you want to delete this support option?');"
                                                          class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center">
                            {{ $supportOptions->links() }}
                        </div>
                    @else
                        <div class="text-center">
                            <p class="text-muted">No support options found.</p>
                            <a href="{{ route('admin.support.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Create Your First Support Option
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection