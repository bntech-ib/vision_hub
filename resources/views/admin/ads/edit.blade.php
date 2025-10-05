@extends('admin.layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Edit Advertisement</h1>
        <div>
            <a href="{{ route('admin.ads.show', $ad) }}" class="btn btn-outline-primary me-2">View</a>
            <a href="{{ route('admin.ads.index') }}" class="btn btn-secondary">Back to List</a>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.ads.update', $ad) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="advertiser_id" class="form-label">Advertiser</label>
                            <select class="form-control" id="advertiser_id" name="advertiser_id" required>
                                <option value="">Select Advertiser</option>
                                @foreach($advertisers as $advertiser)
                                    <option value="{{ $advertiser->id }}" {{ (old('advertiser_id', $ad->advertiser_id ?? '') == $advertiser->id) ? 'selected' : '' }}>
                                        {{ $advertiser->name }} ({{ $advertiser->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('advertiser_id')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $ad->title) }}" required>
                            @error('title')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4" required>{{ old('description', $ad->description) }}</textarea>
                            @error('description')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="target_url" class="form-label">Target URL</label>
                            <input type="url" class="form-control" id="target_url" name="target_url" value="{{ old('target_url', $ad->target_url) }}" required>
                            @error('target_url')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-control" id="category" name="category" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $key => $value)
                                    <option value="{{ $key }}" {{ (old('category', $ad->category ?? '') == $key) ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="budget" class="form-label">Budget (₦)</label>
                            <input type="number" step="0.01" class="form-control" id="budget" name="budget" value="{{ old('budget', $ad->budget) }}" required>
                            @error('budget')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="reward_amount" class="form-label">Reward Amount (₦)</label>
                            <input type="number" step="0.01" class="form-control" id="reward_amount" name="reward_amount" value="{{ old('reward_amount', $ad->reward_amount) }}" required>
                            @error('reward_amount')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-control" id="status" name="status" required>
                                <option value="pending" {{ (old('status', $ad->status ?? '') == 'pending') ? 'selected' : '' }}>Pending</option>
                                <option value="active" {{ (old('status', $ad->status ?? '') == 'active') ? 'selected' : '' }}>Active</option>
                                <option value="paused" {{ (old('status', $ad->status ?? '') == 'paused') ? 'selected' : '' }}>Paused</option>
                                <option value="completed" {{ (old('status', $ad->status ?? '') == 'completed') ? 'selected' : '' }}>Completed</option>
                                <option value="rejected" {{ (old('status', $ad->status ?? '') == 'rejected') ? 'selected' : '' }}>Rejected</option>
                            </select>
                            @error('status')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="{{ old('start_date', $ad->start_date ? $ad->start_date->format('Y-m-d') : '') }}" required>
                            @error('start_date')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="{{ old('end_date', $ad->end_date ? $ad->end_date->format('Y-m-d') : '') }}" required>
                            @error('end_date')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="image" class="form-label">Image</label>
                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                    @if($ad->image_url)
                        <div class="mt-2">
                            <p>Current Image:</p>
                            <img src="{{ Storage::url($ad->image_url) }}" alt="Current Image" class="img-fluid" style="max-height: 150px;">
                        </div>
                    @endif
                    @error('image')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="d-flex justify-content-end">
                    <a href="{{ route('admin.ads.index') }}" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Advertisement</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection