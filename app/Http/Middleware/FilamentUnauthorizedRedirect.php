<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class FilamentUnauthorizedRedirect
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the current panel
        $panel = Filament::getCurrentPanel();

        // Ensure we have a valid panel
        if (!$panel) {
            return $next($request);
        }

        // Check if the user is authenticated but unauthorized for this panel
        if (Auth::check() && !$panel->auth()->check()) {
            // Logout the user
            Auth::guard('web')->logout();
            session()->invalidate();
            session()->regenerateToken();

            // Redirect to the login page of the panel they are trying to access
            return redirect()->route("filament.{$panel->getId()}.auth.login");
        }

        return $next($request);
    }
}
