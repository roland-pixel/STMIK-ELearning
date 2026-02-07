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
        <!-- Card -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-[0_16px_40px_rgba(2,6,23,0.08)] p-6 sm:p-7">
            <!-- Header -->
            <div class="flex items-start gap-4">
                <!-- Logo -->
                <div class="shrink-0">
                    <div
                        class="h-12 w-12 rounded-2xl bg-white flex items-center justify-center
                            ring-maroon/10">
                        <img src="{{ asset('assets/stmiklogo.png') }}" alt="Logo" class="h-20 w-20 object-contain">
                    </div>
                </div>

                <div class="min-w-0">
                    <h1 class="text-2xl font-semibold text-slate-900 leading-tight">Masuk</h1>
                    <p class="text-slate-600 text-sm mt-1">
                        Gunakan email, password, dan pilih peran.
                    </p>

                    <!-- subtle accent line -->
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
                               focus:outline-none focus:ring-4 focus:ring-red-900/10 focus:border-red-700
                               transition">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700">Password</label>
                    <input type="password" name="password" placeholder="••••••••" required
                        class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5 bg-white placeholder:text-slate-400
                               focus:outline-none focus:ring-4 focus:ring-red-900/10 focus:border-red-700
                               transition">
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Peran --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700">Peran</label>
                    <select name="peran" required
                        class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5 bg-white
                               focus:outline-none focus:ring-4 focus:ring-red-900/10 focus:border-red-700
                               transition">
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

                {{-- Remember --}}
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 text-sm text-slate-700 select-none">
                        <input type="checkbox" name="remember"
                            class="rounded border-slate-300 text-red-700 focus:ring-red-900/15">
                        Ingat saya
                    </label>
                </div>

                {{-- Button (maroon elegan) --}}
                <button type="submit"
                    class="w-full rounded-xl py-2.5 font-semibold text-white
                           bg-gradient-to-r from-red-900 via-rose-800 to-red-700
                           hover:from-red-800 hover:via-rose-700 hover:to-red-600
                           shadow-[0_10px_22px_rgba(127,29,29,0.18)]
                           focus:outline-none focus:ring-4 focus:ring-red-900/20
                           transition">
                    Login
                </button>

                <p class="text-xs text-slate-500 text-center pt-2">
                    © {{ date('Y') }} Sistem Akademik
                </p>
            </form>
        </div>
    </div>

    <!-- Tailwind "maroon" alias via inline utility fallback:
         Kalau kamu belum punya custom color 'maroon', ini tetap aman karena ring-maroon/10 tidak ada.
         Jadi ring itu akan diabaikan. Kalau mau, hapus class ring-maroon/10 di atas. -->
</body>

</html>
