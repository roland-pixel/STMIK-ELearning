@extends('admin.layouts.app')

@section('title', 'Edit Jurusan - Maroon Sneat Style')
@section('page_title', 'Edit Jurusan')
@section('page_desc', 'Perbarui data jurusan')

@section('content')
    {{-- Wrapper biar card selalu di tengah --}}
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
                        <h2 class="text-lg font-semibold text-ink-900">Edit Jurusan</h2>
                        <p class="text-sm text-ink-500 mt-1">
                            Perbarui data dengan benar sebelum menyimpan.
                        </p>
                    </div>

                    <span
                        class="inline-flex items-center rounded-full bg-maroon-600/10 px-3 py-1 text-xs font-semibold text-maroon-800 ring-1 ring-maroon-700/10">
                        Master Data
                    </span>
                </div>

                <form method="POST" action="{{ route('admin.jurusans.update', $jurusan) }}" class="mt-6 space-y-5">
                    @csrf
                    @method('PUT')

                    {{-- Grid --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Kode Jurusan --}}
                        <div>
                            <label class="block text-sm font-medium text-ink-700">Kode Jurusan</label>
                            <input type="text" name="kode_jurusan"
                                value="{{ old('kode_jurusan', $jurusan->kode_jurusan) }}" placeholder="TI / SI / MI ..."
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm
                                       focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition"
                                required>
                            @error('kode_jurusan')
                                <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Jenjang --}}
                        <div>
                            <label class="block text-sm font-medium text-ink-700">Jenjang</label>
                            <select name="jenjang"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm
                                       focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition"
                                required>
                                <option value="D3" {{ old('jenjang', $jurusan->jenjang) === 'D3' ? 'selected' : '' }}>
                                    D3
                                </option>
                                <option value="S1" {{ old('jenjang', $jurusan->jenjang) === 'S1' ? 'selected' : '' }}>
                                    S1
                                </option>
                                <option value="S2" {{ old('jenjang', $jurusan->jenjang) === 'S2' ? 'selected' : '' }}>
                                    S2
                                </option>
                            </select>
                            @error('jenjang')
                                <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Nama Jurusan full width --}}
                    <div>
                        <label class="block text-sm font-medium text-ink-700">Nama Jurusan</label>
                        <input type="text" name="nama_jurusan" value="{{ old('nama_jurusan', $jurusan->nama_jurusan) }}"
                            placeholder="Teknik Informatika"
                            class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm
                                   focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition"
                            required>
                        @error('nama_jurusan')
                            <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Divider --}}
                    <div class="h-px bg-slate-200/80"></div>

                    {{-- Actions --}}
                    <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2">
                        <a href="{{ route('admin.jurusans.index') }}"
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

        {{-- hint kecil --}}
        <p class="text-xs text-ink-400 text-center">
            Pastikan perubahan sesuai standar data kampus.
        </p>
    </div>
@endsection
