@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <h4>Welcome, {{ Auth::user()->name }}!</h4>
                    
                    @if(Auth::user()->currentPackage)
                        <div class="alert alert-info">
                            <h5>Your Package: {{ Auth::user()->currentPackage->name }}</h5>
                            <p>{{ Auth::user()->currentPackage->description }}</p>
                            <ul>
                                @foreach(Auth::user()->currentPackage->features as $feature)
                                    <li>{{ $feature }}</li>
                                @endforeach
                            </ul>
                            @if(Auth::user()->package_expires_at)
                                <p><strong>Expires:</strong> {{ Auth::user()->package_expires_at->format('M d, Y') }}</p>
                            @endif
                        </div>
                    @else
                        <div class="alert alert-warning">
                            You don't have an active package.
                        </div>
                    @endif
                    
                    <p>You are logged in!</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection