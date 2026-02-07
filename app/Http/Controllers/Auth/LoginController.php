<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'peran' => ['required', Rule::in(['admin', 'dosen', 'mahasiswa'])],
        ]);

        // Cek email + password dulu
        if (!Auth::attempt([
            'email' => $validated['email'],
            'password' => $validated['password'],
        ], $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Email atau password salah.'])
                ->onlyInput('email', 'peran');
        }

        // Sudah login, sekarang validasi peran harus sesuai
        $request->session()->regenerate();

        $user = Auth::user();
        if ($user->peran !== $validated['peran']) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors(['peran' => 'Peran yang dipilih tidak sesuai dengan akun ini.'])
                ->onlyInput('email', 'peran');
        }

        // Redirect berdasarkan peran
        return match ($user->peran) {
            'admin' => redirect()->route('admin.dashboard'),
            'dosen' => redirect()->route('dosen.dashboard'),
            default => redirect()->route('mahasiswa.dashboard'),
        };
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
