@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Support Option Details</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th>ID:</th>
                                    <td>{{ $supportOption->id }}</td>
                                </tr>
                                <tr>
                                    <th>Title:</th>
                                    <td>{{ $supportOption->title }}</td>
                                </tr>
                                <tr>
                                    <th>Description:</th>
                                    <td>{{ $supportOption->description }}</td>
                                </tr>
                                <tr>
                                    <th>Avatar:</th>
                                    <td>
                                        @if($supportOption->avatar)
                                            <img src="{{ Storage::url($supportOption->avatar) }}" alt="Avatar" class="rounded-circle" style="width: 60px; height: 60px; object-fit: cover;">
                                        @else
                                            <span class="text-muted">None</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Sort Order:</th>
                                    <td>{{ $supportOption->sort_order }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th>WhatsApp Number:</th>
                                    <td>{{ $supportOption->whatsapp_number ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>WhatsApp Message:</th>
                                    <td>{{ $supportOption->whatsapp_message ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        @if($supportOption->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Created At:</th>
                                    <td>{{ $supportOption->created_at->format('M d, Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At:</th>
                                    <td>{{ $supportOption->updated_at->format('M d, Y H:i:s') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.support.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Support Options
                        </a>
                        <div>
                            <a href="{{ route('admin.support.edit', $supportOption) }}" class="btn btn-warning">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <form action="{{ route('admin.support.destroy', $supportOption) }}" 
                                  method="POST" 
                                  onsubmit="return confirm('Are you sure you want to delete this support option?');"
                                  class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection