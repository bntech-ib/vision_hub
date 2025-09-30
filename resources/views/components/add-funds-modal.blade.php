<!-- Add Funds Modal -->
<div class="modal fade" id="addFundsModal" tabindex="-1" aria-labelledby="addFundsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addFundsModalLabel">Add Funds</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addFundsForm">
                    <div class="mb-3">
                        <label for="addAmount" class="form-label">Amount (₦)</label>
                        <input type="number" class="form-control" id="addAmount" name="amount" step="0.01" min="0" placeholder="Enter amount to add">
                    </div>
                    
                    <div class="mb-3">
                        <label for="fundingMethod" class="form-label">Funding Method</label>
                        <select class="form-select" id="fundingMethod" name="fundingMethod">
                            <option value="">Select funding method</option>
                            <option value="card">Credit/Debit Card</option>
                            <option value="bank">Bank Transfer</option>
                            <option value="paypal">PayPal</option>
                            <option value="wallet">Mobile Wallet</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="cardDetails" style="display: none;">
                        <h6>Card Details</h6>
                        <div class="mb-2">
                            <label for="cardNumber" class="form-label">Card Number</label>
                            <input type="text" class="form-control" id="cardNumber" name="cardNumber" placeholder="1234 5678 9012 3456">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label for="expiryDate" class="form-label">Expiry Date</label>
                                <input type="text" class="form-control" id="expiryDate" name="expiryDate" placeholder="MM/YY">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="cvv" class="form-label">CVV</label>
                                <input type="text" class="form-control" id="cvv" name="cvv" placeholder="123">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="submitAddFunds">Add Funds</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show/hide card details based on funding method selection
    const fundingMethodSelect = document.getElementById('fundingMethod');
    const cardDetails = document.getElementById('cardDetails');
    
    fundingMethodSelect.addEventListener('change', function() {
        if (this.value === 'card') {
            cardDetails.style.display = 'block';
        } else {
            cardDetails.style.display = 'none';
        }
    });
    
    // Submit add funds form
    document.getElementById('submitAddFunds').addEventListener('click', function() {
        // Here you would typically make an AJAX request to your API
        // For now, we'll just show an alert
        alert('Add funds request submitted! In a real application, this would connect to the API.');
        // Close the modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('addFundsModal'));
        modal.hide();
    });
});
</script>