@extends('admin.layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Tag</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('admin.tags.show', $tag) }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-eye"></i> View
            </a>
            <a href="{{ route('admin.tags.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Tags
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Tag Details</h5>
            </div>
            <div class="card-body">
                <form id="editTagForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $tag->name) }}" required>
                        <div class="form-text">The name of the tag.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4">{{ old('description', $tag->description) }}</textarea>
                        <div class="form-text">Optional description of the tag.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="color" class="form-label">Color</label>
                        <input type="color" class="form-control form-control-color" id="color" name="color" value="{{ old('color', $tag->color) }}" title="Choose color">
                        <div class="form-text">Select a color to represent this tag.</div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update Tag</button>
                    <a href="{{ route('admin.tags.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Tag Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-5"><strong>ID:</strong></div>
                    <div class="col-sm-7">{{ $tag->id }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-5"><strong>Slug:</strong></div>
                    <div class="col-sm-7">{{ $tag->slug }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-5"><strong>Created:</strong></div>
                    <div class="col-sm-7">{{ $tag->created_at->format('M d, Y H:i') }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-5"><strong>Images:</strong></div>
                    <div class="col-sm-7">{{ $tag->images_count }}</div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Preview</h5>
            </div>
            <div class="card-body text-center">
                <span class="badge" style="background-color: {{ $tag->color }}; color: {{ getContrastColor($tag->color) }}; font-size: 1.2rem;">
                    {{ $tag->name }}
                </span>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('editTagForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const tagId = {{ $tag->id }};
    
    fetch(`/admin/tags/${tagId}`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-HTTP-Method-Override': 'PUT'
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.href = data.tag ? `/admin/tags/${data.tag.id}` : '/admin/tags';
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the tag.');
    });
});

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