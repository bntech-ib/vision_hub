<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of products
     */
    public function index(Request $request)
    {
        $query = Product::with('seller:id,name,email');
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('seller', function($sellerQuery) use ($search) {
                      $sellerQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        
        $products = $query->latest()->paginate(20);
        
        $categories = [
            'electronics' => 'Electronics',
            'clothing' => 'Clothing',
            'books' => 'Books',
            'home' => 'Home & Garden',
            'sports' => 'Sports & Outdoors',
            'other' => 'Other',
        ];
        
        $statuses = [
            'draft' => 'Draft',
            'pending' => 'Pending Review',
            'active' => 'Active',
            'out_of_stock' => 'Out of Stock',
            'discontinued' => 'Discontinued',
        ];
        
        return view('admin.products.index', compact('products', 'categories', 'statuses'));
    }
    
    /**
     * Show the form for creating a new product
     */
    public function create()
    {
        $sellers = User::select('id', 'name', 'email')->get();
        
        $categories = [
            'electronics' => 'Electronics',
            'clothing' => 'Clothing',
            'books' => 'Books',
            'home' => 'Home & Garden',
            'sports' => 'Sports & Outdoors',
            'other' => 'Other',
        ];
        
        $statuses = [
            'draft' => 'Draft',
            'pending' => 'Pending Review',
            'active' => 'Active',
            'out_of_stock' => 'Out of Stock',
            'discontinued' => 'Discontinued',
        ];
        
        return view('admin.products.create', compact('sellers', 'categories', 'statuses'));
    }
    
    /**
     * Store a newly created product
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'seller_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'status' => 'required|string|in:draft,pending,active,out_of_stock,discontinued',
            'is_featured' => 'boolean',
        ]);
        
        Product::create($validated);
        
        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }
    
    /**
     * Display the specified product
     */
    public function show(Product $product)
    {
        $product->load('seller:id,name,email');
        
        $stats = [
            'stock_quantity' => $product->stock_quantity,
            'rating' => $product->rating,
            'total_reviews' => $product->total_reviews,
            'is_featured' => $product->is_featured,
        ];
        
        return view('admin.products.show', compact('product', 'stats'));
    }
    
    /**
     * Show the form for editing the specified product
     */
    public function edit(Product $product)
    {
        $sellers = User::select('id', 'name', 'email')->get();
        
        $categories = [
            'electronics' => 'Electronics',
            'clothing' => 'Clothing',
            'books' => 'Books',
            'home' => 'Home & Garden',
            'sports' => 'Sports & Outdoors',
            'other' => 'Other',
        ];
        
        $statuses = [
            'draft' => 'Draft',
            'pending' => 'Pending Review',
            'active' => 'Active',
            'out_of_stock' => 'Out of Stock',
            'discontinued' => 'Discontinued',
        ];
        
        return view('admin.products.edit', compact('product', 'sellers', 'categories', 'statuses'));
    }
    
    /**
     * Update the specified product
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'seller_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'status' => 'required|string|in:draft,pending,active,out_of_stock,discontinued',
            'is_featured' => 'boolean',
        ]);
        
        $product->update($validated);
        
        return redirect()->route('admin.products.show', $product)
            ->with('success', 'Product updated successfully.');
    }
    
    /**
     * Remove the specified product
     */
    public function destroy(Product $product)
    {
        $product->delete();
        
        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }
}