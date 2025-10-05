# Financial Settings Save Fix

## Issue
The financial settings save functionality was not working due to several issues:

1. **Form Method Mismatch**: The financial settings form was using `@method('PUT')` but the route was defined as a POST method
2. **Nested Forms**: The financial settings template had nested forms which is invalid HTML and caused submission issues
3. **Response Type Mismatch**: The UserController methods for enabling/disabling withdrawals were returning redirects instead of JSON responses for AJAX calls

## Fixes Implemented

### 1. Fixed Form Method
Removed `@method('PUT')` from the financial settings form since the route is defined as POST:

```blade
<!-- Before -->
<form id="financial-settings-form" method="POST" action="{{ route('admin.settings.financial.update') }}">
    @csrf
    @method('PUT')
    
<!-- After -->
<form id="financial-settings-form" method="POST" action="{{ route('admin.settings.financial.update') }}">
    @csrf
```

### 2. Restructured Nested Forms
Replaced nested forms with a single button that uses JavaScript for AJAX calls:

```blade
<!-- Before -->
<div class="d-flex gap-2">
    @if(\App\Models\GlobalSetting::isWithdrawalEnabled())
        <form action="{{ route('admin.settings.disable-withdrawal') }}" method="POST" class="d-inline">
            @csrf
            @method('PUT')
            <button type="submit" class="btn btn-danger btn-lg">
                <i class="bi bi-x-circle"></i> Close Withdrawal Portal
            </button>
        </form>
    @else
        <form action="{{ route('admin.settings.enable-withdrawal') }}" method="POST" class="d-inline">
            @csrf
            @method('PUT')
            <button type="submit" class="btn btn-success btn-lg">
                <i class="bi bi-check-circle"></i> Open Withdrawal Portal
            </button>
        </form>
    @endif
</div>

<!-- After -->
<div class="d-flex gap-2">
    @if(\App\Models\GlobalSetting::isWithdrawalEnabled())
        <button type="button" class="btn btn-danger btn-lg" id="toggle-withdrawal-btn" data-action="disable">
            <i class="bi bi-x-circle"></i> Close Withdrawal Portal
        </button>
    @else
        <button type="button" class="btn btn-success btn-lg" id="toggle-withdrawal-btn" data-action="enable">
            <i class="bi bi-check-circle"></i> Open Withdrawal Portal
        </button>
    @endif
</div>
```

### 3. Added JavaScript Handler
Added JavaScript to handle the withdrawal portal toggle with AJAX:

```javascript
// Handle withdrawal portal toggle
$('#toggle-withdrawal-btn').on('click', function() {
    var btn = $(this);
    var action = btn.data('action');
    var originalText = btn.html();
    
    btn.prop('disabled', true).html('<i class="bi bi-arrow-repeat spinner-border spinner-border-sm"></i> Processing...');
    
    var url = action === 'enable' ? "{{ route('admin.settings.enable-withdrawal') }}" : "{{ route('admin.settings.disable-withdrawal') }}";
    
    $.ajax({
        url: url,
        method: 'PUT',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                // Toggle the button
                if (action === 'enable') {
                    btn.removeClass('btn-success').addClass('btn-danger')
                       .html('<i class="bi bi-x-circle"></i> Close Withdrawal Portal')
                       .data('action', 'disable');
                    $('.withdrawal-status').html('<span class="text-success"><i class="bi bi-unlock"></i> Withdrawals OPEN</span>');
                    $('.portal-status').html('<strong>OPEN</strong>');
                } else {
                    btn.removeClass('btn-danger').addClass('btn-success')
                       .html('<i class="bi bi-check-circle"></i> Open Withdrawal Portal')
                       .data('action', 'enable');
                    $('.withdrawal-status').html('<span class="text-danger"><i class="bi bi-lock"></i> Withdrawals CLOSED</span>');
                    $('.portal-status').html('<strong>CLOSED</strong>');
                }
                showAlert('success', response.message);
            } else {
                showAlert('error', response.message || 'An error occurred');
            }
        },
        error: function(xhr) {
            var errorMsg = 'An error occurred while processing request';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            showAlert('error', errorMsg);
        },
        complete: function() {
            btn.prop('disabled', false);
        }
    });
});
```

### 4. Updated Controller Methods
Updated the UserController methods to return JSON responses for AJAX calls:

```php
/**
 * Enable withdrawal access globally
 */
public function enableWithdrawalGlobally()
{
    \App\Models\GlobalSetting::set('withdrawal_enabled', true);
    
    return response()->json([
        'success' => true,
        'message' => 'Withdrawal access enabled globally.'
    ]);
}

/**
 * Disable withdrawal access globally
 */
public function disableWithdrawalGlobally()
{
    \App\Models\GlobalSetting::set('withdrawal_enabled', false);
    
    return response()->json([
        'success' => true,
        'message' => 'Withdrawal access disabled globally.'
    ]);
}
```

## Testing
The financial settings save functionality should now work correctly:

1. The main financial settings form will submit properly using POST method
2. The withdrawal portal toggle will work with AJAX calls
3. Both operations will provide user feedback through the alert system
4. The UI will update dynamically without page refresh

## Routes
All relevant routes are properly defined:
- `POST admin/settings/financial` → `admin.settings.financial.update`
- `PUT admin/settings/enable-withdrawal` → `admin.settings.enable-withdrawal`
- `PUT admin/settings/disable-withdrawal` → `admin.settings.disable-withdrawal`