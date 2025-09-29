@extends('admin.layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Brain Teasers Management</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.brain-teasers.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add New Brain Teaser
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="mb-0">Brain Teasers List</h5>
            </div>
            <div class="col-md-6">
                <form method="GET" class="d-flex">
                    <input type="text" name="search" class="form-control form-control-sm me-2" 
                           placeholder="Search brain teasers..." value="{{ request('search') }}">
                    <select name="status" class="form-select form-select-sm me-2">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $key => $label)
                            <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <select name="category" class="form-select form-select-sm me-2">
                        <option value="">All Categories</option>
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <select name="difficulty" class="form-select form-select-sm me-2">
                        <option value="">All Difficulties</option>
                        @foreach($difficulties as $key => $label)
                            <option value="{{ $key }}" {{ request('difficulty') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-sm btn-outline-secondary">Filter</button>
                </form>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Difficulty</th>
                        <th>Reward</th>
                        <th>Status</th>
                        <th>Attempts</th>
                        <th>Success Rate</th>
                        <th>Daily</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($brainTeasers as $brainTeaser)
                    <tr>
                        <td>
                            <a href="{{ route('admin.brain-teasers.show', $brainTeaser) }}">{{ $brainTeaser->title }}</a>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $categories[$brainTeaser->category] ?? $brainTeaser->category }}</span>
                        </td>
                        <td>
                            @if($brainTeaser->difficulty === 'easy')
                                <span class="badge bg-success">Easy</span>
                            @elseif($brainTeaser->difficulty === 'medium')
                                <span class="badge bg-warning">Medium</span>
                            @else
                                <span class="badge bg-danger">Hard</span>
                            @endif
                        </td>
                        <td>${{ number_format($brainTeaser->reward_amount, 2) }}</td>
                        <td>
                            @if($brainTeaser->status === 'active')
                                <span class="badge bg-success">Active</span>
                            @elseif($brainTeaser->status === 'pending')
                                <span class="badge bg-warning">Pending</span>
                            @elseif($brainTeaser->status === 'draft')
                                <span class="badge bg-secondary">Draft</span>
                            @else
                                <span class="badge bg-danger">Archived</span>
                            @endif
                        </td>
                        <td>{{ $brainTeaser->total_attempts }}</td>
                        <td>
                            @if($brainTeaser->total_attempts > 0)
                                {{ number_format(($brainTeaser->correct_attempts / $brainTeaser->total_attempts) * 100, 1) }}%
                            @else
                                N/A
                            @endif
                        </td>
                        <td>
                            @if($brainTeaser->is_daily)
                                <span class="badge bg-primary">Daily</span>
                            @else
                                <span class="badge bg-secondary">Regular</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('admin.brain-teasers.show', $brainTeaser) }}" class="btn btn-outline-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.brain-teasers.edit', $brainTeaser) }}" class="btn btn-outline-secondary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.brain-teasers.destroy', $brainTeaser) }}" method="POST" 
                                      onsubmit="return confirm('Are you sure you want to delete this brain teaser?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center">No brain teasers found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{ $brainTeasers->links() }}
    </div>
</div>
@endsection