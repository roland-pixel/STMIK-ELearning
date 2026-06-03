<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Laravel\Socialite\Facades\Socialite; // Tambahkan ini
use App\Models\User; // Tambahkan ini

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // --- LOGIN MANUAL (Tetap dipertahankan) ---
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'peran' => ['required', Rule::in(['admin', 'dosen', 'mahasiswa'])],
        ]);

        if (!Auth::attempt([
            'email' => $validated['email'],
            'password' => $validated['password'],
        ], $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Email atau password salah.'])
                ->onlyInput('email', 'peran');
        }

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

        return match ($user->peran) {
            'admin' => redirect()->route('admin.dashboard'),
            'dosen' => redirect()->route('dosen.dashboard'),
            default => redirect()->route('mahasiswa.dashboard'),
        };
    }

    // --- LOGIN GOOGLE ---
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            $email = $googleUser->getEmail();

            // Cek apakah email Google tersebut sudah didaftarkan di tabel users
            $user = User::where('email', $email)->first();

            if (!$user) {
                return redirect()->route('login')
                    ->withErrors(['email' => 'Akses ditolak! Email Google ini belum terdaftar di sistem. Hubungi Admin.']);
            }

            // Jika terdaftar, login-kan user tersebut
            Auth::login($user, true);
            $request->session()->regenerate();

            // Arahkan ke dashboard sesuai peran di database
            return match ($user->peran) {
                'admin' => redirect()->route('admin.dashboard'),
                'dosen' => redirect()->route('dosen.dashboard'),
                default => redirect()->route('mahasiswa.dashboard'),
            };
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Gagal terhubung ke Google. Silakan coba lagi.']);
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
