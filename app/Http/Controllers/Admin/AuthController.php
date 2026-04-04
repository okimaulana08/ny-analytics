<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (session()->has('admin_user')) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $user = AdminUser::where('email', $request->email)
            ->where('is_active', true)
            ->first();

        if (! $user || ! $user->checkPassword($request->password)) {
            return back()->withErrors(['email' => 'Email atau password salah.'])->withInput();
        }

        $user->update(['last_login_at' => now()]);

        session(['admin_user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar_color' => $user->avatar_color ?? 'blue',
        ]]);

        try {
            ActivityLogger::login($request, $user->id, $user->name, $user->email);
        } catch (\Throwable) {
            // Never crash on logging failure
        }

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('admin_user');

        return redirect()->route('admin.login');
    }
}
