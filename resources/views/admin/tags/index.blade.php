@extends('admin.layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Tags</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('admin.tags.create') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus"></i> Create Tag
            </a>
            <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
            <button type="button" class="btn btn-sm btn-outline-secondary">Bulk Actions</button>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.tags.index') }}" class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Search tags...">
                    </div>
                    <div class="col-md-3">
                        <label for="color" class="form-label">Color</label>
                        <input type="color" class="form-control" id="color" name="color" value="{{ request('color') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="created_by" class="form-label">Created By</label>
                        <input type="text" class="form-control" id="created_by" name="created_by" value="{{ request('created_by') }}" placeholder="User ID">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">Filter</button>
                        <a href="{{ route('admin.tags.index') }}" class="btn btn-secondary">Clear</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Color</th>
                        <th>Images</th>
                        <th>Created By</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tags as $tag)
                    <tr>
                        <td>
                            <a href="{{ route('admin.tags.show', $tag) }}">{{ $tag->name }}</a>
                            <small class="text-muted d-block">{{ $tag->slug }}</small>
                        </td>
                        <td>{{ Str::limit($tag->description, 50) }}</td>
                        <td>
                            <span class="badge" style="background-color: {{ $tag->color }}; color: {{ getContrastColor($tag->color) }};">
                                {{ $tag->color }}
                            </span>
                        </td>
                        <td>{{ $tag->images_count }}</td>
                        <td>
                            @if($tag->creator)
                                <a href="{{ route('admin.users.show', $tag->creator) }}">{{ $tag->creator->name }}</a>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>{{ $tag->created_at->format('M d, Y') }}</td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('admin.tags.show', $tag) }}" class="btn btn-outline-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.tags.edit', $tag) }}" class="btn btn-outline-secondary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-outline-danger" title="Delete" 
                                        onclick="deleteTag({{ $tag->id }})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No tags found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-between align-items-center">
            <div>
                Showing {{ $tags->firstItem() }} to {{ $tags->lastItem() }} of {{ $tags->total() }} tags
            </div>
            <div>
                {{ $tags->links() }}
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function deleteTag(tagId) {
    if (confirm('Are you sure you want to delete this tag?')) {
        fetch(`/admin/tags/${tagId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the tag.');
        });
    }
}

// Helper function to determine text color based on background color
function getContrastColor(hexColor) {
    // Convert hex to RGB
    const r = parseInt(hexColor.substr(1, 2), 16);
    const g = parseInt(hexColor.substr(3, 2), 16);
    const b = parseInt(hexColor.substr(5, 2), 16);
    
    // Calculate brightness
    const brightness = (r * 299 + g * 587 + b * 114) / 1000;
    
    // Return black or white based on brightness
    return brightness > 128 ? '#000000' : '#ffffff';
}
</script>
@endpush