<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    @vite('resources/css/app.css')
</head>

<body class="min-h-screen bg-gradient-to-b from-slate-50 via-slate-50 to-slate-100 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-[0_16px_40px_rgba(2,6,23,0.08)] p-6 sm:p-7">

            <div class="flex items-start gap-4">
                <div class="shrink-0">
                    <div
                        class="h-12 w-12 rounded-2xl bg-white flex items-center justify-center border border-slate-100 shadow-sm">
                        <img src="{{ asset('assets/stmiklogo.png') }}" alt="Logo" class="h-10 w-10 object-contain">
                    </div>
                </div>

                <div class="min-w-0">
                    <h1 class="text-2xl font-semibold text-slate-900 leading-tight">Masuk</h1>
                    <p class="text-slate-600 text-sm mt-1">
                        Gunakan email, password, dan pilih peran.
                    </p>
                    <div class="mt-3 h-1 w-20 rounded-full bg-gradient-to-r from-rose-700 to-red-500 opacity-80"></div>
                </div>
            </div>

            <form method="POST" action="{{ route('login.post') }}" class="mt-6 space-y-4">
                @csrf

                {{-- Email --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="nama@kampus.ac.id"
                        required
                        class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5 bg-white placeholder:text-slate-400
                               focus:outline-none focus:ring-4 focus:ring-red-900/10 focus:border-red-700 transition">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700">Password</label>
                    <input type="password" name="password" placeholder="••••••••" required
                        class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5 bg-white placeholder:text-slate-400
                               focus:outline-none focus:ring-4 focus:ring-red-900/10 focus:border-red-700 transition">
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Peran --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700">Peran</label>
                    <select name="peran" required
                        class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5 bg-white text-slate-900
                               focus:outline-none focus:ring-4 focus:ring-red-900/10 focus:border-red-700 transition">
                        <option value="" disabled {{ old('peran') ? '' : 'selected' }}>Pilih peran</option>
                        <option value="admin" {{ old('peran') === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="dosen" {{ old('peran') === 'dosen' ? 'selected' : '' }}>Dosen</option>
                        <option value="mahasiswa" {{ old('peran') === 'mahasiswa' ? 'selected' : '' }}>Mahasiswa
                        </option>
                    </select>
                    @error('peran')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember Me --}}
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 text-sm text-slate-700 select-none cursor-pointer">
                        <input type="checkbox" name="remember"
                            class="rounded border-slate-300 text-red-700 focus:ring-red-900/15 h-4 w-4 transition">
                        Ingat saya
                    </label>
                </div>

                {{-- Button Login Utama (maroon elegan) --}}
                <button type="submit"
                    class="w-full rounded-xl py-2.5 font-semibold text-white
                           bg-gradient-to-r from-red-900 via-rose-800 to-red-700
                           hover:from-red-800 hover:via-rose-700 hover:to-red-600
                           shadow-[0_10px_22px_rgba(127,29,29,0.18)]
                           focus:outline-none focus:ring-4 focus:ring-red-900/20 transition">
                    Login
                </button>

                {{-- Divider / Pembatas --}}
                <div class="relative flex py-2 items-center">
                    <div class="flex-grow border-t border-slate-200"></div>
                    <span
                        class="flex-shrink mx-4 text-slate-400 text-xs font-medium uppercase tracking-wider">atau</span>
                    <div class="flex-grow border-t border-slate-200"></div>
                </div>

                {{-- Tombol Login dengan Google yang Diperbagus --}}
                <a href="{{ route('google.login') }}"
                    class="flex items-center justify-center gap-3 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 
                          text-sm font-semibold text-slate-700 hover:bg-slate-50 active:bg-slate-100
                          focus:outline-none focus:ring-4 focus:ring-slate-100 transition shadow-sm">
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                            fill="#4285F4" />
                        <path
                            d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                            fill="#34A853" />
                        <path
                            d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"
                            fill="#FBBC05" />
                        <path
                            d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                            fill="#EA4335" />
                    </svg>
                    <span>Masuk menggunakan Google</span>
                </a>

                {{-- Footer --}}
                <p class="text-xs text-slate-500 text-center pt-2">
                    &copy; {{ date('Y') }} Sistem Akademik
                </p>
            </form>
        </div>
    </div>
</body>

</html>
