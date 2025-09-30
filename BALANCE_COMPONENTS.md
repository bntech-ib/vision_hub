# Balance Overview Components

This document explains how to use the balance overview components that have been created for the VisionHub application.

## Components Included

1. **Balance Overview Card** - Displays user's wallet balance and related financial information
2. **Withdraw Funds Modal** - Form for requesting fund withdrawals
3. **Add Funds Modal** - Form for adding funds to the wallet

## How to Use

### 1. Balance Overview Card

To include the balance overview card in a Blade template, use:

```blade
@include('components.balance-overview-card', [
    'user' => $user,
    'pendingWithdrawals' => $pendingWithdrawals ?? 0,
    'totalEarnings' => $totalEarnings ?? 0
])
```

The component expects the following variables:
- `$user` - The user object with wallet_balance property
- `$pendingWithdrawals` - Amount of pending withdrawals (optional)
- `$totalEarnings` - Total earnings (optional)

### 2. Withdraw Funds Modal

To include the withdraw modal in a Blade template, use:

```blade
@include('components.withdraw-modal', [
    'user' => $user
])
```

The component expects the following variables:
- `$user` - The user object with wallet_balance property

### 3. Add Funds Modal

To include the add funds modal in a Blade template, use:

```blade
@include('components.add-funds-modal')
```

This component doesn't require any variables.

## Required Dependencies

These components require the following Bootstrap 5 components:
- Cards
- Modals
- Forms
- Buttons
- Icons (Bootstrap Icons)

Make sure to include Bootstrap 5 CSS and JS files in your layout:

```html
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
```

## API Integration

The components include basic JavaScript for form handling. In a production environment, you would need to integrate with the following API endpoints:

1. **Withdraw Funds**: `POST /api/v1/wallet/withdraw`
2. **Add Funds**: `POST /api/v1/wallet/add-funds`

Example API integration for withdrawal:

```javascript
document.getElementById('submitWithdrawal').addEventListener('click', function() {
    const amount = document.getElementById('withdrawAmount').value;
    const paymentMethod = document.getElementById('paymentMethod').value;
    
    fetch('/api/v1/wallet/withdraw', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + accessToken
        },
        body: JSON.stringify({
            amount: amount,
            paymentMethod: paymentMethod
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Withdrawal request submitted successfully!');
            // Refresh the balance overview
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while processing your request.');
    });
});
```

## Customization

You can customize the appearance of these components by modifying the Blade files:

1. `resources/views/components/balance-overview-card.blade.php`
2. `resources/views/components/withdraw-modal.blade.php`
3. `resources/views/components/add-funds-modal.blade.php`

## Example Usage in User Dashboard

```blade
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            @include('components.balance-overview-card', [
                'user' => $user,
                'pendingWithdrawals' => $pendingWithdrawals ?? 0,
                'totalEarnings' => $totalEarnings ?? 0
            ])
        </div>
    </div>
    
    <!-- Other dashboard content -->
    
    @include('components.withdraw-modal', ['user' => $user])
    @include('components.add-funds-modal')
</div>
@endsection
```