<?php

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the Laravel 12 bootstrap configuration and all of 
| them will be assigned to the "web" middleware group.
|
*/

Route::get('/', function () {
    if (Auth::check()) {
        return view('home');
    }
    return view('auth.login');
});

Route::get('/health', [ApiController::class, 'health']);

// Web Authentication Routes (for admin dashboard and user registration)
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');
    
    Route::get('/register', function () {
        return view('auth.register');
    })->name('register');
    
    Route::get('/admin/login', function () {
        return view('auth.admin-login');
    })->name('admin.login');

    Route::post('/web-login', function (\Illuminate\Http\Request $request) {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
        
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            if (Auth::user()->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }
            
            return redirect()->intended('/');
        }
        
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    })->name('login.post');
    
    Route::post('/web-register', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'accessKey' => 'required|string|exists:access_keys,key',
            'fullName' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'country' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'referrerCode' => 'nullable|string|exists:users,referral_code',
        ]);
        
        // Verify access key
        $accessKey = \App\Models\AccessKey::where('key', $validated['accessKey'])->first();
        
        if (!$accessKey || !$accessKey->isValid()) {
            return back()->withErrors([
                'accessKey' => 'The access key is invalid or has already been used.',
            ]);
        }
        
        // Find referrer if provided
        $referrer = null;
        if (!empty($validated['referrerCode'])) {
            $referrer = \App\Models\User::where('referral_code', $validated['referrerCode'])->first();
        }
        
        // Create user
        $user = \App\Models\User::create([
            'name' => $validated['fullName'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
            'country' => $validated['country'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'referral_code' => strtoupper(\Illuminate\Support\Str::random(6)),
            'current_package_id' => $accessKey->package_id,
            'package_expires_at' => $accessKey->package->duration_days ? 
                now()->addDays($accessKey->package->duration_days) : null,
            'referred_by' => $referrer ? $referrer->id : null,
        ]);
        
        // Mark access key as used
        $accessKey->update([
            'is_used' => true,
            'used_by' => $user->id,
            'used_at' => now(),
        ]);
        
        // Award referral bonuses if applicable
        if ($referrer) {
            // Award direct referral bonus (Level 1)
            $level1Bonus = 100; // Amount in your currency
            $referrer->addToWallet($level1Bonus);
            
            // Log the referral bonus
            \App\Models\ReferralBonus::create([
                'referrer_id' => $referrer->id,
                'referred_user_id' => $user->id,
                'level' => 1,
                'amount' => $level1Bonus,
                'description' => 'Direct referral bonus for ' . $user->username,
            ]);
            
            // Log the transaction for the referrer
            $referrer->transactions()->create([
                'amount' => $level1Bonus,
                'type' => 'referral',
                'description' => 'Direct referral bonus for ' . $user->username,
                'status' => 'completed',
            ]);
            
            // Award indirect referral bonus (Level 2)
            if ($referrer->referredBy) {
                $level2Bonus = 50; // Amount in your currency
                $referrer->referredBy->addToWallet($level2Bonus);
                
                // Log the referral bonus
                \App\Models\ReferralBonus::create([
                    'referrer_id' => $referrer->referredBy->id,
                    'referred_user_id' => $user->id,
                    'level' => 2,
                    'amount' => $level2Bonus,
                    'description' => 'Indirect referral bonus for ' . $user->username,
                ]);
                
                // Log the transaction for the indirect referrer
                $referrer->referredBy->transactions()->create([
                    'amount' => $level2Bonus,
                    'type' => 'referral',
                    'description' => 'Indirect referral bonus for ' . $user->username,
                    'status' => 'completed',
                ]);
                
                // Award indirect referral bonus (Level 3)
                if ($referrer->referredBy->referredBy) {
                    $level3Bonus = 25; // Amount in your currency
                    $referrer->referredBy->referredBy->addToWallet($level3Bonus);
                    
                    // Log the referral bonus
                    \App\Models\ReferralBonus::create([
                        'referrer_id' => $referrer->referredBy->referredBy->id,
                        'referred_user_id' => $user->id,
                        'level' => 3,
                        'amount' => $level3Bonus,
                        'description' => 'Second indirect referral bonus for ' . $user->username,
                    ]);
                    
                    // Log the transaction for the second indirect referrer
                    $referrer->referredBy->referredBy->transactions()->create([
                        'amount' => $level3Bonus,
                        'type' => 'referral',
                        'description' => 'Second indirect referral bonus for ' . $user->username,
                        'status' => 'completed',
                    ]);
                }
            }
        }
        
        // Log the user in
        Auth::login($user);
        
        return redirect()->intended('/');
    })->name('register.post');
    
    Route::post('/admin/login', function (\Illuminate\Http\Request $request) {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
        
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            if (Auth::user()->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }
            
            Auth::logout();
            return back()->withErrors([
                'email' => 'Admin access required.',
            ]);
        }
        
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    })->name('admin.login.post');
});

Route::middleware('auth')->group(function () {
    Route::get('/home', function () {
        return view('home');
    })->name('home');
    
    Route::post('/logout', function (\Illuminate\Http\Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    })->name('logout');
});

// Note: auth.php contains API routes with sanctum middleware
// require __DIR__.'/auth.php';