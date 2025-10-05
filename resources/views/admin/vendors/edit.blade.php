@extends('admin.layouts.app')

@section('title', 'Edit Vendor')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Vendor</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.vendors.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Vendors
                        </a>
                    </div>
                </div>
                
                <form action="{{ route('admin.vendors.update', $vendor) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Name *</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $vendor->name) }}" required>
                                    @error('name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email *</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email', $vendor->email) }}" required>
                                    @error('email')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="vendor_company_name">Company Name *</label>
                                    <input type="text" class="form-control @error('vendor_company_name') is-invalid @enderror" 
                                           id="vendor_company_name" name="vendor_company_name" 
                                           value="{{ old('vendor_company_name', $vendor->vendor_company_name) }}" required>
                                    @error('vendor_company_name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="vendor_commission_rate">Commission Rate (%) *</label>
                                    <input type="number" step="0.01" min="0" max="100" 
                                           class="form-control @error('vendor_commission_rate') is-invalid @enderror" 
                                           id="vendor_commission_rate" name="vendor_commission_rate" 
                                           value="{{ old('vendor_commission_rate', $vendor->vendor_commission_rate) }}" required>
                                    @error('vendor_commission_rate')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="vendor_website">Website</label>
                                    <input type="url" class="form-control @error('vendor_website') is-invalid @enderror" 
                                           id="vendor_website" name="vendor_website" 
                                           value="{{ old('vendor_website', $vendor->vendor_website) }}">
                                    @error('vendor_website')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="vendor_description">Description</label>
                                    <textarea class="form-control @error('vendor_description') is-invalid @enderror" 
                                              id="vendor_description" name="vendor_description" rows="3">{{ old('vendor_description', $vendor->vendor_description) }}</textarea>
                                    @error('vendor_description')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Vendor
                        </button>
                        <a href="{{ route('admin.vendors.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection