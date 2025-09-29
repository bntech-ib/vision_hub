@extends('admin.layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Course Details</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.courses.index') }}" class="btn btn-sm btn-outline-secondary me-2">
            <i class="bi bi-arrow-left"></i> Back to Courses
        </a>
        <a href="{{ route('admin.courses.edit', $course) }}" class="btn btn-sm btn-primary me-2">
            <i class="bi bi-pencil"></i> Edit Course
        </a>
        <form action="{{ route('admin.courses.destroy', $course) }}" method="POST" 
              onsubmit="return confirm('Are you sure you want to delete this course?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger">
                <i class="bi bi-trash"></i> Delete Course
            </button>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Course Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-3"><strong>Title:</strong></div>
                    <div class="col-sm-9">{{ $course->title }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Description:</strong></div>
                    <div class="col-sm-9">{{ $course->description }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Instructor:</strong></div>
                    <div class="col-sm-9">{{ $course->instructor->name ?? 'N/A' }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Category:</strong></div>
                    <div class="col-sm-9">
                        <span class="badge bg-secondary">{{ $categories[$course->category] ?? $course->category }}</span>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Level:</strong></div>
                    <div class="col-sm-9">
                        <span class="badge bg-info">{{ ucfirst($course->level) }}</span>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Price:</strong></div>
                    <div class="col-sm-9">${{ number_format($course->price, 2) }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Duration:</strong></div>
                    <div class="col-sm-9">{{ $course->duration_hours }} hours</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Status:</strong></div>
                    <div class="col-sm-9">
                        @if($course->status === 'active')
                            <span class="badge bg-success">Active</span>
                        @elseif($course->status === 'pending')
                            <span class="badge bg-warning">Pending</span>
                        @elseif($course->status === 'draft')
                            <span class="badge bg-secondary">Draft</span>
                        @else
                            <span class="badge bg-danger">Archived</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Statistics</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-6">
                        <h6 class="text-muted">Enrollments</h6>
                        <h4>{{ $stats['enrollment_count'] }}</h4>
                    </div>
                    <div class="col-6">
                        <h6 class="text-muted">Views</h6>
                        <h4>{{ $stats['view_count'] }}</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <h6 class="text-muted">Rating</h6>
                        @if($stats['rating'])
                            <h4>
                                <span class="text-warning">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= $stats['rating'])
                                            <i class="bi bi-star-fill"></i>
                                        @else
                                            <i class="bi bi-star"></i>
                                        @endif
                                    @endfor
                                </span>
                                {{ $stats['rating'] }}
                            </h4>
                        @else
                            <h4>N/A</h4>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Recent Enrollments</h5>
            </div>
            <div class="card-body">
                @if($course->enrollments->count() > 0)
                    <ul class="list-group">
                        @foreach($course->enrollments as $enrollment)
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <span>{{ $enrollment->user->name ?? 'Unknown User' }}</span>
                                    <small class="text-muted">{{ $enrollment->created_at->format('M d, Y') }}</small>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted">No recent enrollments.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection