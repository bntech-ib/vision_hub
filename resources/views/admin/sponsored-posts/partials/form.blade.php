<div class="mb-3">
    <label for="title" class="form-label">Title</label>
    <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $post->title ?? '') }}" required>
</div>
<div class="mb-3">
    <label for="category" class="form-label">Category</label>
    <input type="text" class="form-control" id="category" name="category" value="{{ old('category', $post->category ?? '') }}" required>
</div>
<div class="mb-3">
    <label for="status" class="form-label">Status</label>
    <select class="form-control" id="status" name="status" required>
        @foreach(['active','inactive','pending','completed','rejected'] as $status)
            <option value="{{ $status }}" {{ old('status', $post->status ?? '') == $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label for="budget" class="form-label">Budget</label>
    <input type="number" step="0.01" class="form-control" id="budget" name="budget" value="{{ old('budget', $post->budget ?? '') }}" required>
</div>
<div class="mb-3">
    <label for="start_date" class="form-label">Start Date</label>
    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ old('start_date', isset($post->start_date) ? $post->start_date->format('Y-m-d') : '') }}" required>
</div>
<div class="mb-3">
    <label for="end_date" class="form-label">End Date</label>
    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ old('end_date', isset($post->end_date) ? $post->end_date->format('Y-m-d') : '') }}" required>
</div>
<div class="mb-3">
    <label for="image_url" class="form-label">Image URL</label>
    <input type="url" class="form-control" id="image_url" name="image_url" value="{{ old('image_url', $post->image_url ?? '') }}" required>
</div>
<div class="mb-3">
    <label for="target_url" class="form-label">Target URL</label>
    <input type="url" class="form-control" id="target_url" name="target_url" value="{{ old('target_url', $post->target_url ?? '') }}" required>
</div>
<div class="mb-3">
    <label for="description" class="form-label">Description</label>
    <textarea class="form-control" id="description" name="description" rows="4" required>{{ old('description', $post->description ?? '') }}</textarea>
</div>