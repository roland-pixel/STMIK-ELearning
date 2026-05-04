@extends('admin.layouts.app')

@section('title', 'Tambah Mata Kuliah - Maroon Sneat Style')
@section('page_title', 'Tambah Mata Kuliah')
@section('page_desc', 'Buat mata kuliah baru dengan kode per jurusan')

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
                </div>

                <form method="POST" action="{{ route('admin.mata_kuliahs.store') }}" class="mt-6 space-y-5">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-ink-700">Nama MK</label>
                            <input type="text" name="nama_mk" value="{{ old('nama_mk') }}"
                                placeholder="Contoh: Pemrograman Dasar"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition"
                                required>
                            @error('nama_mk')
                                <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-ink-700">SKS</label>
                            <input type="number" name="sks" value="{{ old('sks') }}" min="1" max="10"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition"
                                required>
                            @error('sks')
                                <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Mapping Kode Per Jurusan --}}
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <label class="block text-sm font-bold text-ink-800 mb-3">Kode Mata Kuliah Per Jurusan</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach ($jurusans as $jurusan)
                                <div>
                                    <label class="block text-xs font-medium text-ink-500">{{ $jurusan->nama_jurusan }}
                                        ({{ $jurusan->kode_jurusan }})
                                    </label>
                                    <input type="text" name="mapping[{{ $jurusan->id }}]"
                                        value="{{ old('mapping.' . $jurusan->id) }}"
                                        placeholder="Contoh: {{ $jurusan->kode_jurusan }}101"
                                        class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition">
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-ink-700">Jenis MK</label>
                            <select name="jenis_mk"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition"
                                required>
                                <option value="" disabled selected>Pilih jenis</option>
                                <option value="Umum" {{ old('jenis_mk') === 'Umum' ? 'selected' : '' }}>Umum</option>
                                <option value="Spesial" {{ old('jenis_mk') === 'Spesial' ? 'selected' : '' }}>Spesial
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-ink-700">Kategori MK</label>
                            <select name="kategori_mk"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition"
                                required>
                                <option value="" disabled selected>Pilih kategori</option>
                                @foreach (['KPP', 'KIT', 'KAB', 'KPB', 'KBB'] as $kat)
                                    <option value="{{ $kat }}"
                                        {{ old('kategori_mk') === $kat ? 'selected' : '' }}>{{ $kat }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-4">
                        <a href="{{ route('admin.mata_kuliahs.index') }}"
                            class="px-4 py-2 text-sm font-semibold text-ink-800 bg-slate-100 rounded-xl hover:bg-slate-200 transition">Batal</a>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-semibold text-white bg-maroon-700 rounded-xl hover:bg-maroon-800 transition">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
