<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckMembershipExpiry
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->role === 'miembro' && $user->member) {

            $membershipExpiry = $user->member->expiration_date;

            if ($membershipExpiry && now()->isAfter($membershipExpiry->addYear())) {
                Auth::logout();

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()
                    ->route('login')
                    ->withErrors([
                        'membership' => 'Tu año de gracia ha expirado. Renueva tu membresía para recuperar el acceso.']);
            }
        }

        return $next($request);
    }
}
