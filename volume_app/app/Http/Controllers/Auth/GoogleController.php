<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\LoginLog;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors(['google' => 'Erro ao autenticar com Google.']);
        }

        $googleEmail = strtolower((string) $googleUser->getEmail());

        $user = User::where('role', 'fiscal')
            ->where(function ($query) use ($googleUser, $googleEmail) {
                $query->where('google_id', $googleUser->getId())
                    ->orWhere('email', $googleEmail);
            })
            ->first();

        if (!$user) {
            return redirect()->route('login')
                ->withErrors(['google' => 'Acesso negado. Conta Google não autorizada.']);
        }

        if (!$user->active) {
            return redirect()->route('login')->withErrors(['google' => 'Conta desativada.']);
        }

        if (!$user->google_id) {
            $user->update([
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
            ]);
        }

        Auth::login($user, true);

        LoginLog::create([
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'login',
            'details' => ['method' => 'google'],
            'ip_address' => $request->ip(),
        ]);

        session(['last_activity' => now()->timestamp]);

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
