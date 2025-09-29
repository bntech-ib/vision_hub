<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthenticationController extends Controller
{
    protected Google2FA $google2fa;

    public function __construct(Google2FA $google2fa)
    {
        $this->google2fa = $google2fa;
    }

    /**
     * Show the 2FA verification form
     */
    public function show()
    {
        return view('admin.auth.2fa');
    }

    /**
     * Verify the 2FA code
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = Auth::user();
        
        // Verify the code
        $valid = $this->google2fa->verifyKey(
            $user->two_factor_secret,
            $request->code
        );
        
        if ($valid) {
            // Mark 2FA as verified in the session
            $request->session()->put('2fa_verified', true);
            
            // Log security event
            $user->logSecurityEvent('2fa_verified', [
                'method' => 'authenticator_app'
            ]);
            
            return redirect()->intended(route('admin.dashboard'));
        }
        
        // Log failed attempt
        $user->logSecurityEvent('2fa_failed', [
            'method' => 'authenticator_app',
            'code' => $request->code
        ], false);
        
        return back()->withErrors([
            'code' => __('Invalid authentication code.'),
        ]);
    }
}