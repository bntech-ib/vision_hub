@extends('admin.layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Brain Teaser</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.brain-teasers.show', $brainTeaser) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Brain Teaser
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Brain Teaser Details</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.brain-teasers.update', $brainTeaser) }}">
            @csrf
            @method('PUT')
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="creator_id" class="form-label">Creator</label>
                    <select name="creator_id" id="creator_id" class="form-select">
                        <option value="">Select Creator (optional)</option>
                        @foreach($creators as $creator)
                            <option value="{{ $creator->id }}" {{ (old('creator_id', $brainTeaser->creator_id) == $creator->id) ? 'selected' : '' }}>
                                {{ $creator->name }} ({{ $creator->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('creator_id')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6">
                    <label for="title" class="form-label">Title *</label>
                    <input type="text" name="title" id="title" class="form-control" 
                           value="{{ old('title', $brainTeaser->title) }}" required>
                    @error('title')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="mb-3">
                <label for="question" class="form-label">Question *</label>
                <textarea name="question" id="question" class="form-control" rows="3" required>{{ old('question', $brainTeaser->question) }}</textarea>
                @error('question')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
            </div>
            
            <div class="mb-3">
                <label class="form-label">Options *</label>
                <div id="options-container">
                    @foreach($options as $index => $option)
                    <div class="row mb-2 option-row">
                        <div class="col-md-11">
                            <input type="text" name="options[]" class="form-control" 
                                   value="{{ old('options.' . $index, $option) }}" 
                                   placeholder="Option {{ $index + 1 }}" required>
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-danger remove-option">-</button>
                        </div>
                    </div>
                    @endforeach
                </div>
                <button type="button" id="add-option" class="btn btn-secondary btn-sm">+ Add Option</button>
                @error('options')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label for="correct_answer" class="form-label">Correct Answer *</label>
                <input type="text" name="correct_answer" id="correct_answer" class="form-control" 
                       value="{{ old('correct_answer', $brainTeaser->correct_answer) }}" required>
                <div class="form-text">Enter the exact correct answer text</div>
                @error('correct_answer')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label for="explanation" class="form-label">Explanation *</label>
                <textarea name="explanation" id="explanation" class="form-control" rows="3" required>{{ old('explanation', $brainTeaser->explanation) }}</textarea>
                <div class="form-text">Explain why this is the correct answer</div>
                @error('explanation')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="category" class="form-label">Category *</label>
                    <select name="category" id="category" class="form-select" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}" {{ (old('category', $brainTeaser->category) == $key) ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('category')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-4">
                    <label for="difficulty" class="form-label">Difficulty *</label>
                    <select name="difficulty" id="difficulty" class="form-select" required>
                        <option value="">Select Difficulty</option>
                        @foreach($difficulties as $key => $label)
                            <option value="{{ $key }}" {{ (old('difficulty', $brainTeaser->difficulty) == $key) ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('difficulty')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-4">
                    <label for="reward_amount" class="form-label">Reward Amount ($) *</label>
                    <input type="number" name="reward_amount" id="reward_amount" class="form-control" 
                           step="0.01" min="0" value="{{ old('reward_amount', $brainTeaser->reward_amount) }}" required>
                    @error('reward_amount')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="status" class="form-label">Status *</label>
                    <select name="status" id="status" class="form-select" required>
                        <option value="">Select Status</option>
                        @foreach($statuses as $key => $label)
                            <option value="{{ $key }}" {{ (old('status', $brainTeaser->status) == $key) ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('status')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6">
                    <label for="is_daily" class="form-label">Daily Challenge</label>
                    <div class="form-check">
                        <input type="checkbox" name="is_daily" id="is_daily" class="form-check-input" 
                               value="1" {{ old('is_daily', $brainTeaser->is_daily) ? 'checked' : '' }}>
                        <label for="is_daily" class="form-check-label">Mark as daily challenge</label>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-end">
                <a href="{{ route('admin.brain-teasers.show', $brainTeaser) }}" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Brain Teaser</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const optionsContainer = document.getElementById('options-container');
    const addOptionButton = document.getElementById('add-option');
    
    // Add new option
    addOptionButton.addEventListener('click', function() {
        const optionCount = document.querySelectorAll('.option-row').length;
        if (optionCount >= 6) {
            alert('Maximum of 6 options allowed');
            return;
        }
        
        const newRow = document.createElement('div');
        newRow.className = 'row mb-2 option-row';
        newRow.innerHTML = `
            <div class="col-md-11">
                <input type="text" name="options[]" class="form-control" placeholder="Option ${optionCount + 1}" required>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-danger remove-option">-</button>
            </div>
        `;
        optionsContainer.appendChild(newRow);
    });
    
    // Remove option
    optionsContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-option')) {
            const optionRows = document.querySelectorAll('.option-row');
            if (optionRows.length > 2) {
                e.target.closest('.option-row').remove();
            } else {
                alert('At least 2 options are required');
            }
        }
    });
});
</script>
@endsection