@extends('admin.layouts.app')
@section('content')
<div class="container">
    <h1>Withdrawal Request Details</h1>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Withdrawal #{{ $withdrawal->id }}</h5>
            <p><strong>User:</strong> {{ $withdrawal->user->name ?? 'N/A' }} ({{ $withdrawal->user->email ?? '' }})</p>
            <p><strong>Status:</strong> {{ ucfirst($withdrawal->status) }}</p>
            <p><strong>Amount:</strong> {{ number_format($withdrawal->amount, 2) }}</p>
            <p><strong>Payment Method:</strong> {{ $withdrawal->payment_method ?? '-' }}</p>
            <p><strong>Account Details:</strong> <pre>{{ json_encode($withdrawal->payment_details, JSON_PRETTY_PRINT) }}</pre></p>
            <p><strong>Requested At:</strong> {{ $withdrawal->created_at }}</p>
            <p><strong>Processed At:</strong> {{ $withdrawal->processed_at ?? '-' }}</p>
            @if($withdrawal->transaction_id)
            <p><strong>Transaction ID:</strong> {{ $withdrawal->transaction_id }}</p>
            @endif
            <p><strong>Notes:</strong> {{ $withdrawal->notes ?? '-' }}</p>
            <p><strong>Rejection Reason:</strong> {{ $withdrawal->rejection_reason ?? '-' }}</p>
        </div>
    </div>
    <a href="{{ route('admin.withdrawals.index') }}" class="btn btn-secondary mt-3">Back to List</a>
</div>
@endsection
