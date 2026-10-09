<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class LoginController extends Controller
{
    /**
     * Tampilkan formulir login.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Proses autentikasi pengguna.
     */
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Percobaan login
        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi yang Anda masukkan salah.',
            ]);
        }

        // Regenerasi session ID untuk mencegah serangan Session Fixation
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Keluarkan pengguna dari sistem (Logout).
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}