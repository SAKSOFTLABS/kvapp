<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\ActivityLogService;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = [
            'username' => $request->username,
            'password' => $request->password,
            'status' => 'active',
        ];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            ActivityLogService::log('LOGIN', 'User logged in successfully: ' . Auth::user()->name);

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Welcome back, ' . Auth::user()->name . '!');
        }

        return back()->withErrors([
            'username' => 'Invalid credentials or account is inactive.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            ActivityLogService::log('LOGOUT', 'User logged out: ' . Auth::user()->name);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Logged out successfully.');
    }
}
