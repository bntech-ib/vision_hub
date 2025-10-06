# Welcome Bonus Wallet Update

## Issue
During user registration, the welcome bonus was only being added to the `welcome_bonus` field but not to the user's wallet balance as requested.

## Solution
Modified the registration process in `AuthController` to add the welcome bonus to both the `welcome_bonus` field and the `wallet_balance` field.

## Changes Made

### 1. Updated AuthController
Changed the welcome bonus handling in the registration method:

**Before:**
```php
// Award welcome bonus from the package to new user
$packageWelcomeBonus = (float) $accessKey->package->welcome_bonus ?? 0;
if ($packageWelcomeBonus > 0) {
    $user->addToWelcomeBonus($packageWelcomeBonus);
    
    // Log the welcome bonus transaction
    $user->transactions()->create([
        'amount' => $packageWelcomeBonus,
        'type' => 'welcome_bonus',
        'description' => 'Welcome bonus from ' . $accessKey->package->name . ' package',
        'status' => 'completed',
    ]);
}
```

**After:**
```php
// Award welcome bonus from the package to new user
$packageWelcomeBonus = (float) $accessKey->package->welcome_bonus ?? 0;
if ($packageWelcomeBonus > 0) {
    // Add welcome bonus to both welcome_bonus field and wallet_balance
    $user->addToWalletAndWelcomeBonus($packageWelcomeBonus, $packageWelcomeBonus);
    
    // Log the welcome bonus transaction
    $user->transactions()->create([
        'amount' => $packageWelcomeBonus,
        'type' => 'welcome_bonus',
        'description' => 'Welcome bonus from ' . $accessKey->package->name . ' package',
        'status' => 'completed',
    ]);
}
```

### 2. Utilized Existing User Model Method
The solution leverages the existing `addToWalletAndWelcomeBonus()` method in the User model which was already implemented:

```php
/**
 * Add to wallet balance and welcome bonus
 */
public function addToWalletAndWelcomeBonus(float $walletAmount, float $welcomeBonusAmount): void
{
    $this->increment('wallet_balance', $walletAmount);
    $this->increment('welcome_bonus', $welcomeBonusAmount);
}
```

## Benefits
1. Users now receive their welcome bonus in their wallet balance immediately upon registration
2. The welcome bonus is still tracked in the `welcome_bonus` field for reporting purposes
3. A transaction record is still created for audit purposes
4. The change is minimal and leverages existing functionality

## Testing
Created a new test file `tests/Feature/WelcomeBonusWalletTest.php` to verify:
1. New users receive welcome bonus in both wallet balance and welcome bonus field
2. Users with zero welcome bonus packages receive zero in both fields
3. Transaction records are still created correctly