<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InactivityTimeout
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $lastActivity = session('last_activity', now()->timestamp);
            $timeout = 300; // 5 minutes (RNF12)

            if (now()->timestamp - $lastActivity > $timeout) {
                auth()->logout();
                session()->flush();
                return redirect()->route('login')->with('message', 'Sessão expirada por inatividade.');
            }

            session(['last_activity' => now()->timestamp]);
        }

        return $next($request);
    }
}
