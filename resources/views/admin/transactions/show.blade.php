@extends('admin.layouts.app')
@section('content')
<div class="container">
    <h1>Transaction Details</h1>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Transaction #{{ $transaction->transaction_id }}</h5>
            <p><strong>User:</strong> {{ $transaction->user->name ?? 'N/A' }} ({{ $transaction->user->email ?? '' }})</p>
            <p><strong>Type:</strong> {{ ucfirst($transaction->type) }}</p>
            <p><strong>Status:</strong> {{ ucfirst($transaction->status) }}</p>
            <p><strong>Amount:</strong> {{ number_format($transaction->amount, 2) }}</p>
            <p><strong>Payment Method:</strong> {{ $transaction->payment_method ?? '-' }}</p>
            <p><strong>Description:</strong> {{ $transaction->description }}</p>
            <p><strong>Created At:</strong> {{ $transaction->created_at }}</p>
            <p><strong>Updated At:</strong> {{ $transaction->updated_at }}</p>
            <p><strong>Metadata:</strong> <pre>{{ json_encode($transaction->metadata, JSON_PRETTY_PRINT) }}</pre></p>
        </div>
    </div>
    <a href="{{ route('admin.transactions.index') }}" class="btn btn-secondary mt-3">Back to List</a>
</div>
@endsection
