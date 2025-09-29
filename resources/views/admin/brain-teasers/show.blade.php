@extends('admin.layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Brain Teaser Details</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.brain-teasers.index') }}" class="btn btn-sm btn-outline-secondary me-2">
            <i class="bi bi-arrow-left"></i> Back to Brain Teasers
        </a>
        <a href="{{ route('admin.brain-teasers.edit', $brainTeaser) }}" class="btn btn-sm btn-primary me-2">
            <i class="bi bi-pencil"></i> Edit Brain Teaser
        </a>
        <form action="{{ route('admin.brain-teasers.destroy', $brainTeaser) }}" method="POST" 
              onsubmit="return confirm('Are you sure you want to delete this brain teaser?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger">
                <i class="bi bi-trash"></i> Delete Brain Teaser
            </button>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Brain Teaser Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-3"><strong>Title:</strong></div>
                    <div class="col-sm-9">{{ $brainTeaser->title }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Question:</strong></div>
                    <div class="col-sm-9">{{ $brainTeaser->question }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Options:</strong></div>
                    <div class="col-sm-9">
                        <ul>
                            @foreach($options as $option)
                                <li>{{ $option }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Correct Answer:</strong></div>
                    <div class="col-sm-9">{{ $brainTeaser->correct_answer }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Explanation:</strong></div>
                    <div class="col-sm-9">{{ $brainTeaser->explanation }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Creator:</strong></div>
                    <div class="col-sm-9">{{ $brainTeaser->creator->name ?? 'N/A' }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Category:</strong></div>
                    <div class="col-sm-9">
                        <span class="badge bg-secondary">{{ $categories[$brainTeaser->category] ?? $brainTeaser->category }}</span>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Difficulty:</strong></div>
                    <div class="col-sm-9">
                        @if($brainTeaser->difficulty === 'easy')
                            <span class="badge bg-success">Easy</span>
                        @elseif($brainTeaser->difficulty === 'medium')
                            <span class="badge bg-warning">Medium</span>
                        @else
                            <span class="badge bg-danger">Hard</span>
                        @endif
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Reward Amount:</strong></div>
                    <div class="col-sm-9">${{ number_format($brainTeaser->reward_amount, 2) }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Status:</strong></div>
                    <div class="col-sm-9">
                        @if($brainTeaser->status === 'active')
                            <span class="badge bg-success">Active</span>
                        @elseif($brainTeaser->status === 'pending')
                            <span class="badge bg-warning">Pending</span>
                        @elseif($brainTeaser->status === 'draft')
                            <span class="badge bg-secondary">Draft</span>
                        @else
                            <span class="badge bg-danger">Archived</span>
                        @endif
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Daily Challenge:</strong></div>
                    <div class="col-sm-9">
                        @if($brainTeaser->is_daily)
                            <span class="badge bg-primary">Yes</span>
                        @else
                            <span class="badge bg-secondary">No</span>
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
                        <h6 class="text-muted">Total Attempts</h6>
                        <h4>{{ $stats['total_attempts'] }}</h4>
                    </div>
                    <div class="col-6">
                        <h6 class="text-muted">Correct Attempts</h6>
                        <h4>{{ $stats['correct_attempts'] }}</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <h6 class="text-muted">Success Rate</h6>
                        <h4>
                            @if($stats['success_rate'] > 0)
                                {{ number_format($stats['success_rate'], 1) }}%
                            @else
                                N/A
                            @endif
                        </h4>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Recent Attempts</h5>
            </div>
            <div class="card-body">
                @if($brainTeaser->attempts->count() > 0)
                    <ul class="list-group">
                        @foreach($brainTeaser->attempts as $attempt)
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <span>
                                        @if($attempt->is_correct)
                                            <span class="text-success">✓</span>
                                        @else
                                            <span class="text-danger">✗</span>
                                        @endif
                                        {{ $attempt->user->name ?? 'Unknown User' }}
                                    </span>
                                    <small class="text-muted">{{ $attempt->created_at->format('M d, Y H:i') }}</small>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted">No recent attempts.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection