@extends('admin.layouts.app')

@section('title', 'Tambah Mata Kuliah - Maroon Sneat Style')
@section('page_title', 'Tambah Mata Kuliah')
@section('page_desc', 'Buat mata kuliah baru')

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
                        <h2 class="text-lg font-semibold text-ink-900">Form Mata Kuliah</h2>
                        <p class="text-sm text-ink-500 mt-1">Isi data dengan benar sebelum menyimpan.</p>
                    </div>

                    <span
                        class="inline-flex items-center rounded-full bg-maroon-600/10 px-3 py-1 text-xs font-semibold text-maroon-800 ring-1 ring-maroon-700/10">
                        Master Data
                    </span>
                </div>

                <form method="POST" action="{{ route('admin.mata_kuliahs.store') }}" class="mt-6 space-y-5">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-ink-700">Kode MK</label>
                            <input type="text" name="kode_mk" value="{{ old('kode_mk') }}" placeholder="IF101"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm
                                       focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition"
                                required>
                            @error('kode_mk')
                                <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-ink-700">SKS</label>
                            <input type="number" name="sks" value="{{ old('sks') }}" min="1" max="10"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm
                                       focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition"
                                required>
                            @error('sks')
                                <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-ink-700">Nama MK</label>
                        <input type="text" name="nama_mk" value="{{ old('nama_mk') }}" placeholder="Pemrograman Dasar"
                            class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm
                                   focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition"
                            required>
                        @error('nama_mk')
                            <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-ink-700">Jenis MK</label>
                        <select name="jenis_mk"
                            class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm
                                   focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition"
                            required>
                            <option value="" disabled {{ old('jenis_mk') ? '' : 'selected' }}>Pilih jenis</option>
                            <option value="Umum" {{ old('jenis_mk') === 'Umum' ? 'selected' : '' }}>Umum</option>
                            <option value="Spesial" {{ old('jenis_mk') === 'Spesial' ? 'selected' : '' }}>Spesial</option>
                        </select>
                        @error('jenis_mk')
                            <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="h-px bg-slate-200/80"></div>

                    <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2">
                        <a href="{{ route('admin.mata_kuliahs.index') }}"
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
            Pastikan kode MK unik dan SKS sesuai kurikulum.
        </p>
    </div>
@endsection
