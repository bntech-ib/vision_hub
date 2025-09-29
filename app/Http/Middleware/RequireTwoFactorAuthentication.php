<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequireTwoFactorAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Only apply to admin routes
        if ($request->is('admin*') && Auth::check()) {
            $user = Auth::user();
            
            // Check if 2FA is enabled and confirmed for admin users
            if ($user->is_admin && $user->two_factor_enabled && $user->hasConfirmedTwoFactor()) {
                // Check if 2FA has been verified in the session
                if (!$request->session()->get('2fa_verified', false)) {
                    // Redirect to 2FA verification page if not verified
                    if (!$request->is('admin/2fa*') && !$request->is('admin/logout')) {
                        return redirect()->route('admin.2fa.show');
                    }
                }
            }
        }

        return $next($request);
    }
}