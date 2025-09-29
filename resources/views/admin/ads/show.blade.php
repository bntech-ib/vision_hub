@extends('admin.layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Advertisement Details</h1>
        <div>
            <a href="{{ route('admin.ads.edit', $ad) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('admin.ads.index') }}" class="btn btn-secondary">Back to List</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Advertisement Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Title:</strong> {{ $ad->title }}</p>
                            <p><strong>Advertiser:</strong> {{ $ad->advertiser->name ?? 'N/A' }} ({{ $ad->advertiser->email ?? '' }})</p>
                            <p><strong>Category:</strong> {{ ucfirst($ad->category) }}</p>
                            <p><strong>Status:</strong> 
                                <span class="badge bg-{{ $ad->status == 'active' ? 'success' : ($ad->status == 'pending' ? 'warning' : ($ad->status == 'rejected' ? 'danger' : 'secondary')) }}">
                                    {{ ucfirst($ad->status) }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Budget:</strong> ₦{{ number_format($ad->budget, 2) }}</p>
                            <p><strong>Spent:</strong> ₦{{ number_format($ad->spent, 2) }}</p>
                            <p><strong>Remaining:</strong> ₦{{ number_format($ad->budget - $ad->spent, 2) }}</p>
                            <p><strong>Start Date:</strong> {{ $ad->start_date && !is_null($ad->start_date) ? $ad->start_date->format('M d, Y') : 'Not set' }}</p>
                            <p><strong>End Date:</strong> {{ $ad->end_date && !is_null($ad->end_date) ? $ad->end_date->format('M d, Y') : 'Not set' }}</p>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Description:</strong>
                        <p class="mt-2">{{ $ad->description }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Target URL:</strong>
                        <p class="mt-2"><a href="{{ $ad->target_url }}" target="_blank">{{ $ad->target_url }}</a></p>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Performance Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-light rounded">
                                <h4>{{ number_format($stats['total_impressions']) }}</h4>
                                <p class="mb-0">Impressions</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-light rounded">
                                <h4>{{ number_format($stats['total_clicks']) }}</h4>
                                <p class="mb-0">Clicks</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-light rounded">
                                <h4>{{ number_format($stats['ctr'], 2) }}%</h4>
                                <p class="mb-0">CTR</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-light rounded">
                                <h4>{{ number_format($stats['spent_amount'], 2) }}</h4>
                                <p class="mb-0">Spent (₦)</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Advertisement Image</h5>
                </div>
                <div class="card-body text-center">
                    @if($ad->image_url)
                        <img src="{{ Storage::url($ad->image_url) }}" alt="{{ $ad->title }}" class="img-fluid rounded">
                    @else
                        <div class="bg-light p-5 rounded">
                            <i class="fas fa-image fa-3x text-muted"></i>
                            <p class="mt-2">No image available</p>
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    @if($ad->status == 'pending')
                        <form action="{{ route('admin.ads.approve', $ad) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm w-100 mb-2">Approve</button>
                        </form>
                        <form action="{{ route('admin.ads.reject', $ad) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm w-100">Reject</button>
                        </form>
                    @elseif($ad->status == 'active')
                        <form action="{{ route('admin.ads.pause', $ad) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-warning btn-sm w-100">Pause</button>
                        </form>
                    @elseif($ad->status == 'paused')
                        <form action="{{ route('admin.ads.approve', $ad) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm w-100">Resume</button>
                        </form>
                    @endif
                    
                    <form action="{{ route('admin.ads.destroy', $ad) }}" method="POST" class="mt-3" onsubmit="return confirm('Are you sure you want to delete this advertisement?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm w-100">Delete Advertisement</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection