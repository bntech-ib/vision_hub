@extends('admin.layouts.app')
@section('content')
<div class="container-fluid">
    <h1 class="mb-4">Withdrawals</h1>
    <div class="row mb-3">
        <div class="col-md-2">
            <div class="card text-bg-primary mb-3">
                <div class="card-body">
                    <h6 class="card-title">Total</h6>
                    <p class="card-text">{{ $stats['total_withdrawals'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-bg-warning mb-3">
                <div class="card-body">
                    <h6 class="card-title">Pending</h6>
                    <p class="card-text">{{ $stats['pending_withdrawals'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-bg-success mb-3">
                <div class="card-body">
                    <h6 class="card-title">Approved</h6>
                    <p class="card-text">{{ $stats['approved_withdrawals'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-bg-info mb-3">
                <div class="card-body">
                    <h6 class="card-title">Pending Amount</h6>
                    <p class="card-text">{{ number_format($stats['total_amount_pending'], 2) }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-bg-success mb-3">
                <div class="card-body">
                    <h6 class="card-title">Approved Amount</h6>
                    <p class="card-text">{{ number_format($stats['total_amount_approved'], 2) }}</p>
                </div>
            </div>
        </div>
    </div>
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-2">
            <input type="text" name="search" class="form-control" placeholder="Search by ID, user, method" value="{{ request('search') }}">
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">All Statuses</option>
                @foreach(['pending','approved','rejected','processing'] as $status)
                    <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
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
                    <th>Status</th>
                    <th>Amount</th>
                    <th>Payment Method</th>
                    <th>Requested At</th>
                    <th>Processed At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($withdrawals as $withdrawal)
                <tr>
                    <td>{{ $withdrawal->id }}</td>
                    <td>{{ $withdrawal->user->name ?? 'N/A' }}</td>
                    <td>{{ ucfirst($withdrawal->status) }}</td>
                    <td>{{ number_format($withdrawal->amount, 2) }}</td>
                    <td>{{ $withdrawal->payment_method ?? '-' }}</td>
                    <td>{{ $withdrawal->created_at }}</td>
                    <td>{{ $withdrawal->processed_at ?? '-' }}</td>
                    <td>
                        <a href="{{ route('admin.withdrawals.show', $withdrawal->id) }}" class="btn btn-info btn-sm">View</a>
                        @if($withdrawal->status === 'pending')
                        <button class="btn btn-success btn-sm approve-btn" data-id="{{ $withdrawal->id }}">Approve</button>
                        <button class="btn btn-danger btn-sm reject-btn" data-id="{{ $withdrawal->id }}">Reject</button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center">No withdrawals found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $withdrawals->withQueryString()->links() }}
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="approveForm">
        <div class="modal-header">
          <h5 class="modal-title" id="approveModalLabel">Approve Withdrawal</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="withdrawal_id" id="approve_withdrawal_id">
          <div class="mb-3">
            <label for="approve_notes" class="form-label">Notes</label>
            <textarea class="form-control" name="notes" id="approve_notes" rows="3"></textarea>
          </div>
          <div class="mb-3">
            <label for="approve_transaction_id" class="form-label">Transaction ID (Optional)</label>
            <input type="text" class="form-control" name="transaction_id" id="approve_transaction_id">
            <div class="form-text">Enter transaction ID if available. If left blank, a system-generated ID will be used.</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Approve</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="rejectForm">
        <div class="modal-header">
          <h5 class="modal-title" id="rejectModalLabel">Reject Withdrawal</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="withdrawal_id" id="reject_withdrawal_id">
          <div class="mb-3">
            <label for="reject_reason" class="form-label">Reason</label>
            <textarea class="form-control" name="reason" id="reject_reason" rows="3" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Reject</button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
$(function() {
    let approveModal = new bootstrap.Modal(document.getElementById('approveModal'));
    let rejectModal = new bootstrap.Modal(document.getElementById('rejectModal'));
    let approveForm = $('#approveForm');
    let rejectForm = $('#rejectForm');
    let approveBtn, rejectBtn;

    $('.approve-btn').on('click', function() {
        approveBtn = $(this);
        $('#approve_withdrawal_id').val(approveBtn.data('id'));
        approveModal.show();
    });

    $('.reject-btn').on('click', function() {
        rejectBtn = $(this);
        $('#reject_withdrawal_id').val(rejectBtn.data('id'));
        rejectModal.show();
    });

    approveForm.on('submit', function(e) {
        e.preventDefault();
        let id = $('#approve_withdrawal_id').val();
        let data = approveForm.serialize();
        $.ajax({
            url: '/admin/withdrawals/' + id + '/approve',
            method: 'PUT',
            data: data,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || 'Approval failed.');
            }
        });
    });

    rejectForm.on('submit', function(e) {
        e.preventDefault();
        let id = $('#reject_withdrawal_id').val();
        let data = rejectForm.serialize();
        $.ajax({
            url: '/admin/withdrawals/' + id + '/reject',
            method: 'PUT',
            data: data,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || 'Rejection failed.');
            }
        });
    });
});
</script>
@endpush
@endsection
