@extends('admin.layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Product Details</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-outline-secondary me-2">
            <i class="bi bi-arrow-left"></i> Back to Products
        </a>
        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-primary me-2">
            <i class="bi bi-pencil"></i> Edit Product
        </a>
        <form action="{{ route('admin.products.destroy', $product) }}" method="POST" 
              onsubmit="return confirm('Are you sure you want to delete this product?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger">
                <i class="bi bi-trash"></i> Delete Product
            </button>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Product Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-3"><strong>Name:</strong></div>
                    <div class="col-sm-9">{{ $product->name }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Description:</strong></div>
                    <div class="col-sm-9">{{ $product->description }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Seller:</strong></div>
                    <div class="col-sm-9">{{ $product->seller->name ?? 'N/A' }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Category:</strong></div>
                    <div class="col-sm-9">
                        <span class="badge bg-secondary">{{ $categories[$product->category] ?? $product->category }}</span>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Price:</strong></div>
                    <div class="col-sm-9">${{ number_format($product->price, 2) }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Stock Quantity:</strong></div>
                    <div class="col-sm-9">{{ $product->stock_quantity }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Status:</strong></div>
                    <div class="col-sm-9">
                        @if($product->status === 'active')
                            <span class="badge bg-success">Active</span>
                        @elseif($product->status === 'pending')
                            <span class="badge bg-warning">Pending</span>
                        @elseif($product->status === 'draft')
                            <span class="badge bg-secondary">Draft</span>
                        @elseif($product->status === 'out_of_stock')
                            <span class="badge bg-danger">Out of Stock</span>
                        @else
                            <span class="badge bg-dark">Discontinued</span>
                        @endif
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Featured:</strong></div>
                    <div class="col-sm-9">
                        @if($product->is_featured)
                            <span class="badge bg-primary">Featured Product</span>
                        @else
                            <span class="badge bg-secondary">Normal Product</span>
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
                        <h6 class="text-muted">Stock</h6>
                        <h4>{{ $stats['stock_quantity'] }}</h4>
                    </div>
                    <div class="col-6">
                        <h6 class="text-muted">Reviews</h6>
                        <h4>{{ $stats['total_reviews'] }}</h4>
                    </div>
                </div>
                <div class="row mb-3">
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
                <div class="row">
                    <div class="col-12">
                        <h6 class="text-muted">Featured Status</h6>
                        <h4>
                            @if($stats['is_featured'])
                                <span class="badge bg-primary">Featured</span>
                            @else
                                <span class="badge bg-secondary">Not Featured</span>
                            @endif
                        </h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection