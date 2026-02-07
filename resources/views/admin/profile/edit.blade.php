@extends('admin.layouts.app')

@section('title', 'Profile - Admin')
@section('page_title', 'Profile')
@section('page_desc', 'Ubah data akun dan foto profil')

@section('content')
    <div class="max-w-3xl mx-auto">
        @if (session('success'))
            <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="rounded-2xl border border-slate-200 bg-white p-6">
            <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="flex items-center gap-4">
                    {{-- Avatar preview --}}
                    @if (auth()->user()->avatar)
                        <img src="{{ asset('storage/' . auth()->user()->avatar) }}"
                            class="h-16 w-16 rounded-2xl object-cover border border-slate-200" alt="Avatar">
                    @else
                        <div class="h-16 w-16 rounded-2xl bg-maroon-600/10 grid place-items-center border border-slate-200">
                            {{-- SVG default person --}}
                            <svg class="h-8 w-8 text-maroon-800" viewBox="0 0 24 24" fill="none">
                                <path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Z" stroke="currentColor" stroke-width="1.8" />
                                <path d="M20 21a8 8 0 0 0-16 0" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" />
                            </svg>
                        </div>
                    @endif

                    <div class="flex-1">
                        <label class="block text-sm font-medium text-slate-700">Foto Profil</label>
                        <input type="file" name="avatar"
                            class="mt-1 block w-full text-sm file:mr-3 file:rounded-xl file:border-0 file:bg-maroon-700 file:px-4 file:py-2 file:text-white hover:file:bg-maroon-800">
                        @error('avatar')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-slate-500">JPG/PNG/WEBP max 2MB.</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap', $user->nama_lengkap) }}"
                        class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm
                           focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition"
                        required>
                    @error('nama_lengkap')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                        class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm
                           focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition"
                        required>
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Password Baru (opsional)</label>
                    <input type="password" name="password"
                        class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm
                           focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition">
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-slate-500">Kosongkan jika tidak ingin mengubah password.</p>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <a href="{{ route('admin.dashboard') }}"
                        class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm hover:bg-slate-50 transition">
                        Batal
                    </a>
                    <button type="submit"
                        class="rounded-xl bg-maroon-700 px-4 py-2 text-sm font-semibold text-white hover:bg-maroon-800 transition">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
