@extends('admin.layouts.app')

@section('title', 'Tambah Dosen - Maroon Sneat Style')
@section('page_title', 'Tambah Dosen')
@section('page_desc', 'Membuat akun user + data dosen')

@section('content')
    <div class="max-w-3xl mx-auto space-y-4">

        {{-- Alert error --}}
        @if ($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-800">
                <p class="font-semibold">Terjadi kesalahan:</p>
                <ul class="mt-1 list-disc pl-5 text-sm">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-2xl border border-slate-200 bg-white shadow-soft">
            <div class="p-6 sm:p-7">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-ink-900">Tambah Dosen</h2>
                        <p class="text-sm text-ink-500 mt-1">Buat akun user sekaligus data dosen.</p>
                    </div>

                    <span
                        class="inline-flex items-center rounded-full bg-maroon-600/10 px-3 py-1 text-xs font-semibold text-maroon-800 ring-1 ring-maroon-700/10">
                        Master Data
                    </span>
                </div>

                <form method="POST" action="{{ route('admin.dosens.store') }}" class="mt-6 space-y-6">
                    @csrf

                    {{-- SECTION: USER --}}
                    <div class="rounded-2xl border border-slate-200/70 bg-slate-50/40 p-4 sm:p-5">
                        <div class="flex items-center justify-between gap-3">
                            <h3 class="text-sm font-semibold text-ink-900">Data Akun (User)</h3>
                            <span class="text-xs text-ink-500">Wajib</span>
                        </div>

                        <div class="mt-4 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-ink-700">Nama Lengkap</label>
                                <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}"
                                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm
                                           focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition"
                                    required>
                                @error('nama_lengkap')
                                    <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-ink-700">Email</label>
                                <input type="email" name="email" value="{{ old('email') }}"
                                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm
                                           focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition"
                                    required>
                                @error('email')
                                    <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-ink-700">Password</label>
                                <input type="password" name="password"
                                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm
                                           focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition"
                                    required>
                                @error('password')
                                    <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-ink-500">Minimal 8 karakter.</p>
                            </div>
                        </div>
                    </div>

                    {{-- SECTION: DOSEN --}}
                    <div class="rounded-2xl border border-slate-200/70 bg-slate-50/40 p-4 sm:p-5">
                        <div class="flex items-center justify-between gap-3">
                            <h3 class="text-sm font-semibold text-ink-900">Data Dosen</h3>
                            <span class="text-xs text-ink-500">Wajib</span>
                        </div>

                        <div class="mt-4 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-ink-700">NIP</label>
                                <input type="text" name="nip" value="{{ old('nip') }}"
                                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm
                                           focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition"
                                    required>
                                @error('nip')
                                    <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Divider --}}
                    <div class="h-px bg-slate-200/80"></div>

                    {{-- Actions --}}
                    <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2">
                        <a href="{{ route('admin.dosens.index') }}"
                            class="inline-flex justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold
                                   text-ink-800 hover:bg-slate-50 transition">
                            Batal
                        </a>

                        <button type="submit"
                            class="inline-flex justify-center rounded-xl bg-maroon-700 px-4 py-2 text-sm font-semibold text-white
                                   hover:bg-maroon-800 transition shadow-[0_10px_22px_rgba(127,29,29,0.18)]">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <p class="text-xs text-ink-400 text-center">
            Sistem akan membuat akun user otomatis untuk dosen.
        </p>
    </div>
@endsection
