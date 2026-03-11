<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            if (!$user->active) {
                Auth::logout();
                return back()->withErrors(['email' => 'Conta desativada.']);
            }

            LoginLog::create([
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'login',
                'details' => ['method' => 'password'],
                'ip_address' => $request->ip(),
            ]);

            session(['last_activity' => now()->timestamp]);

            return $this->redirectByRole($user);
        }

        return back()->withErrors(['email' => 'Credenciais inválidas.']);
    }

    public function logout(Request $request)
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'logout',
            'ip_address' => $request->ip(),
        ]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    protected function redirectByRole($user)
    {
        return match ($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'operator' => redirect()->route('operator.terminal'),
            'company' => redirect()->route('company.dashboard'),
            'fiscal' => redirect()->route('fiscal.dashboard'),
            'management' => redirect()->route('management.dashboard'),
            default => redirect()->route('login'),
        };
    }
}
