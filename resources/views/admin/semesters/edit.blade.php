@extends('admin.layouts.app')

@section('title', 'Edit Semester - Maroon Sneat Style')
@section('page_title', 'Edit Semester')
@section('page_desc', 'Perbarui data semester')

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

        {{-- Card Form --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-soft">
            <div class="p-6 sm:p-7">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-ink-900">Edit Semester</h2>
                        <p class="text-sm text-ink-500 mt-1">Perbarui data semester sebelum menyimpan.</p>
                    </div>

                    <span
                        class="inline-flex items-center rounded-full bg-maroon-600/10 px-3 py-1 text-xs font-semibold text-maroon-800 ring-1 ring-maroon-700/10">
                        Master Data
                    </span>
                </div>

                <form method="POST" action="{{ route('admin.semesters.update', $semester) }}" class="mt-6 space-y-5">
                    @csrf
                    @method('PUT')

                    {{-- Nama Semester --}}
                    <div>
                        <label class="block text-sm font-medium text-ink-700">Nama Semester</label>
                        <input type="text" name="nama_semester"
                            value="{{ old('nama_semester', $semester->nama_semester) }}" placeholder="Ganjil 2025/2026"
                            class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm
                                   focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition"
                            required>
                        @error('nama_semester')
                            <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Status Aktif --}}
                    <div>
                        <label class="block text-sm font-medium text-ink-700">Status Aktif</label>
                        <select name="status_aktif"
                            class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm
                                   focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition"
                            required>
                            <option value="inactive"
                                {{ old('status_aktif', $semester->status_aktif) === 'inactive' ? 'selected' : '' }}>
                                Inactive
                            </option>
                            <option value="active"
                                {{ old('status_aktif', $semester->status_aktif) === 'active' ? 'selected' : '' }}>
                                Active
                            </option>
                        </select>
                        @error('status_aktif')
                            <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tanggal --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-ink-700">Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai"
                                value="{{ old('tanggal_mulai', $semester->tanggal_mulai) }}"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm
                                       focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition"
                                required>
                            @error('tanggal_mulai')
                                <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-ink-700">Tanggal Selesai</label>
                            <input type="date" name="tanggal_selesai"
                                value="{{ old('tanggal_selesai', $semester->tanggal_selesai) }}"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm
                                       focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition"
                                required>
                            @error('tanggal_selesai')
                                <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Divider --}}
                    <div class="h-px bg-slate-200/80"></div>

                    {{-- Actions --}}
                    <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2">
                        <a href="{{ route('admin.semesters.index') }}"
                            class="inline-flex justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold
                                   text-ink-800 hover:bg-slate-50 transition">
                            Batal
                        </a>

                        <button type="submit"
                            class="inline-flex justify-center rounded-xl bg-maroon-700 px-4 py-2 text-sm font-semibold text-white
                                   hover:bg-maroon-800 transition shadow-[0_10px_22px_rgba(127,29,29,0.18)]">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <p class="text-xs text-ink-400 text-center">
            Pastikan perubahan tidak bentrok dengan periode semester lain.
        </p>
    </div>
@endsection
