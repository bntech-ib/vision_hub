<!-- Withdraw Funds Modal -->
<div class="modal fade" id="withdrawModal" tabindex="-1" aria-labelledby="withdrawModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="withdrawModalLabel">Withdraw Funds</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="withdrawForm">
                    <div class="mb-3">
                        <label for="withdrawAmount" class="form-label">Amount (₦)</label>
                        <input type="number" class="form-control" id="withdrawAmount" name="amount" step="0.01" min="0" placeholder="Enter amount to withdraw">
                        <div class="form-text">Available balance: ₦{{ number_format($user->wallet_balance, 2) }}</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="paymentMethod" class="form-label">Payment Method</label>
                        <select class="form-select" id="paymentMethod" name="paymentMethod">
                            <option value="">Select payment method</option>
                            <option value="bank">Bank Transfer</option>
                            <option value="paypal">PayPal</option>
                            <option value="wallet">Mobile Wallet</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="bankDetails" style="display: none;">
                        <h6>Bank Details</h6>
                        <div class="mb-2">
                            <label for="accountName" class="form-label">Account Name</label>
                            <input type="text" class="form-control" id="accountName" name="accountName" placeholder="Enter account name">
                        </div>
                        <div class="mb-2">
                            <label for="accountNumber" class="form-label">Account Number</label>
                            <input type="text" class="form-control" id="accountNumber" name="accountNumber" placeholder="Enter account number">
                        </div>
                        <div class="mb-2">
                            <label for="bankName" class="form-label">Bank Name</label>
                            <input type="text" class="form-control" id="bankName" name="bankName" placeholder="Enter bank name">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="submitWithdrawal">Withdraw</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show/hide bank details based on payment method selection
    const paymentMethodSelect = document.getElementById('paymentMethod');
    const bankDetails = document.getElementById('bankDetails');
    
    paymentMethodSelect.addEventListener('change', function() {
        if (this.value === 'bank') {
            bankDetails.style.display = 'block';
        } else {
            bankDetails.style.display = 'none';
        }
    });
    
    // Submit withdrawal form
    document.getElementById('submitWithdrawal').addEventListener('click', function() {
        // Here you would typically make an AJAX request to your API
        // For now, we'll just show an alert
        alert('Withdrawal request submitted! In a real application, this would connect to the API.');
        // Close the modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('withdrawModal'));
        modal.hide();
    });
});
</script>