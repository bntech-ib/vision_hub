@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Advertisements</h1>
        <a href="{{ route('admin.ads.create') }}" class="btn btn-primary">Create New Advertisement</a>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Filters</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.ads.index') }}">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Search by title, description or advertiser">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="">All Statuses</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="paused" {{ request('status') == 'paused' ? 'selected' : '' }}>Paused</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-control" id="category" name="category">
                                <option value="">All Categories</option>
                                <option value="general" {{ request('category') == 'general' ? 'selected' : '' }}>General</option>
                                <option value="technology" {{ request('category') == 'technology' ? 'selected' : '' }}>Technology</option>
                                <option value="business" {{ request('category') == 'business' ? 'selected' : '' }}>Business</option>
                                <option value="education" {{ request('category') == 'education' ? 'selected' : '' }}>Education</option>
                                <option value="entertainment" {{ request('category') == 'entertainment' ? 'selected' : '' }}>Entertainment</option>
                                <option value="health" {{ request('category') == 'health' ? 'selected' : '' }}>Health & Fitness</option>
                                <option value="lifestyle" {{ request('category') == 'lifestyle' ? 'selected' : '' }}>Lifestyle</option>
                                <option value="travel" {{ request('category') == 'travel' ? 'selected' : '' }}>Travel</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                    <a href="{{ route('admin.ads.index') }}" class="btn btn-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Ads Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Advertiser</th>
                            <th>Category</th>
                            <th>Budget</th>
                            <th>Status</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ads as $ad)
                        <tr>
                            <td>{{ $ad->id }}</td>
                            <td>{{ $ad->title }}</td>
                            <td>{{ $ad->advertiser->name ?? 'N/A' }}</td>
                            <td>{{ ucfirst($ad->category) }}</td>
                            <td>₦{{ number_format($ad->budget, 2) }}</td>
                            <td>
                                <span class="badge bg-{{ $ad->status == 'active' ? 'success' : ($ad->status == 'pending' ? 'warning' : ($ad->status == 'rejected' ? 'danger' : 'secondary')) }}">
                                    {{ ucfirst($ad->status) }}
                                </span>
                            </td>
                            <td>{{ $ad->start_date ? $ad->start_date->format('M d, Y') : 'N/A' }}</td>
                            <td>{{ $ad->end_date ? $ad->end_date->format('M d, Y') : 'N/A' }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.ads.show', $ad) }}" class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.ads.edit', $ad) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.ads.destroy', $ad) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this advertisement?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center">No advertisements found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center">
                {{ $ads->links() }}
            </div>
        </div>
    </div>
</div>
@endsection