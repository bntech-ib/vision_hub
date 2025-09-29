@extends('admin.layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Product</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.products.show', $product) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Product
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Product Details</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.products.update', $product) }}">
            @csrf
            @method('PUT')
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="seller_id" class="form-label">Seller *</label>
                    <select name="seller_id" id="seller_id" class="form-select" required>
                        <option value="">Select Seller</option>
                        @foreach($sellers as $seller)
                            <option value="{{ $seller->id }}" {{ (old('seller_id', $product->seller_id) == $seller->id) ? 'selected' : '' }}>
                                {{ $seller->name }} ({{ $seller->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('seller_id')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6">
                    <label for="name" class="form-label">Name *</label>
                    <input type="text" name="name" id="name" class="form-control" 
                           value="{{ old('name', $product->name) }}" required>
                    @error('name')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Description *</label>
                <textarea name="description" id="description" class="form-control" rows="4" required>{{ old('description', $product->description) }}</textarea>
                @error('description')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="category" class="form-label">Category *</label>
                    <select name="category" id="category" class="form-select" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}" {{ (old('category', $product->category) == $key) ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('category')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-4">
                    <label for="price" class="form-label">Price ($) *</label>
                    <input type="number" name="price" id="price" class="form-control" 
                           step="0.01" min="0" value="{{ old('price', $product->price) }}" required>
                    @error('price')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-4">
                    <label for="stock_quantity" class="form-label">Stock Quantity *</label>
                    <input type="number" name="stock_quantity" id="stock_quantity" class="form-control" 
                           min="0" value="{{ old('stock_quantity', $product->stock_quantity) }}" required>
                    @error('stock_quantity')
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
                            <option value="{{ $key }}" {{ (old('status', $product->status) == $key) ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('status')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6">
                    <label for="is_featured" class="form-label">Featured Product</label>
                    <div class="form-check">
                        <input type="checkbox" name="is_featured" id="is_featured" class="form-check-input" 
                               value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}>
                        <label for="is_featured" class="form-check-label">Mark as featured product</label>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-end">
                <a href="{{ route('admin.products.show', $product) }}" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Product</button>
            </div>
        </form>
    </div>
</div>
@endsection