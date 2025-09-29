@extends('admin.layouts.app')
@section('content')
<div class="container-fluid">
    <h1 class="mb-4">Transactions</h1>
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-2">
            <input type="text" name="search" class="form-control" placeholder="Search by ID, user, method" value="{{ request('search') }}">
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">All Statuses</option>
                @foreach(['pending','completed','failed','refunded','partial_refund'] as $status)
                    <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="type" class="form-select">
                <option value="">All Types</option>
                @foreach(['payment','refund','withdrawal'] as $type)
                    <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <input type="text" name="payment_method" class="form-control" placeholder="Payment Method" value="{{ request('payment_method') }}">
        </div>
        <div class="col-md-2">
            <input type="number" step="0.01" name="amount_min" class="form-control" placeholder="Min Amount" value="{{ request('amount_min') }}">
        </div>
        <div class="col-md-2">
            <input type="number" step="0.01" name="amount_max" class="form-control" placeholder="Max Amount" value="{{ request('amount_max') }}">
        </div>
        <div class="col-md-2">
            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
        </div>
        <div class="col-md-2">
            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Amount</th>
                    <th>Payment Method</th>
                    <th>Description</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->transaction_id }}</td>
                    <td>{{ $transaction->user->name ?? 'N/A' }}</td>
                    <td>{{ ucfirst($transaction->type) }}</td>
                    <td>{{ ucfirst($transaction->status) }}</td>
                    <td>{{ number_format($transaction->amount, 2) }}</td>
                    <td>{{ $transaction->payment_method ?? '-' }}</td>
                    <td>{{ Str::limit($transaction->description, 40) }}</td>
                    <td>{{ $transaction->created_at }}</td>
                    <td>
                        <a href="{{ route('admin.transactions.show', $transaction->id) }}" class="btn btn-info btn-sm">View</a>
                        @if($transaction->status === 'completed')
                        <button class="btn btn-warning btn-sm refund-btn" data-id="{{ $transaction->id }}">Refund</button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center">No transactions found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $transactions->withQueryString()->links() }}
</div>

<!-- Refund Modal -->
<div class="modal fade" id="refundModal" tabindex="-1" aria-labelledby="refundModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="refundForm">
        <div class="modal-header">
          <h5 class="modal-title" id="refundModalLabel">Refund Transaction</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="transaction_id" id="refund_transaction_id">
          <div class="mb-3">
            <label for="refund_amount" class="form-label">Amount</label>
            <input type="number" step="0.01" class="form-control" name="amount" id="refund_amount" required>
          </div>
          <div class="mb-3">
            <label for="refund_reason" class="form-label">Reason</label>
            <textarea class="form-control" name="reason" id="refund_reason" rows="3" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Submit Refund</button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
$(function() {
    let refundModal = new bootstrap.Modal(document.getElementById('refundModal'));
    let refundForm = $('#refundForm');
    let refundBtn;

    $('.refund-btn').on('click', function() {
        refundBtn = $(this);
        let row = refundBtn.closest('tr');
        let amount = row.find('td').eq(4).text();
        $('#refund_transaction_id').val(refundBtn.data('id'));
        $('#refund_amount').val(amount);
        refundModal.show();
    });

    refundForm.on('submit', function(e) {
        e.preventDefault();
        let id = $('#refund_transaction_id').val();
        let data = refundForm.serialize();
        $.ajax({
            url: '/admin/transactions/' + id + '/refund',
            method: 'PUT',
            data: data,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || 'Refund failed.');
            }
        });
    });
});
</script>
@endpush
@endsection
