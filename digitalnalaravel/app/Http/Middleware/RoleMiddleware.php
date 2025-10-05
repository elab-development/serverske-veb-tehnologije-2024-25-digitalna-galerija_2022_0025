<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $role)
    {
        if (!Auth::check()) {
            // Nije prijavljen
            return redirect('/login');
        }

        $user = Auth::user();
        if ($user->role !== $role) {
            // Nema odgovarajuću ulogu
            abort(403, 'Nemaš pristup ovoj ruti.');
        }

        return $next($request);
    }
}
