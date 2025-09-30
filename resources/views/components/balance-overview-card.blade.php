<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Balance Overview</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Current Balance</h6>
                        <h3 class="mb-0">₦{{ number_format($user->wallet_balance, 2) }}</h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
                        <i class="bi bi-wallet2 text-primary fs-4"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Available Balance</h6>
                        <h3 class="mb-0">₦{{ number_format($user->wallet_balance - $pendingWithdrawals ?? 0, 2) }}</h3>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded-circle">
                        <i class="bi bi-cash-coin text-success fs-4"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Total Earnings</h6>
                        <h3 class="mb-0">₦{{ number_format($totalEarnings ?? 0, 2) }}</h3>
                    </div>
                    <div class="bg-info bg-opacity-10 p-3 rounded-circle">
                        <i class="bi bi-graph-up text-info fs-4"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Pending Withdrawals</h6>
                        <h3 class="mb-0">₦{{ number_format($pendingWithdrawals ?? 0, 2) }}</h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-3 rounded-circle">
                        <i class="bi bi-hourglass-split text-warning fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="d-flex gap-2 mt-3">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#withdrawModal">
                <i class="bi bi-arrow-down-circle me-1"></i> Withdraw Funds
            </button>
            <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#addFundsModal">
                <i class="bi bi-arrow-up-circle me-1"></i> Add Funds
            </button>
        </div>
    </div>
</div>