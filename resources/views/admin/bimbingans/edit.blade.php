@extends('admin.layouts.app')

@section('title', 'Edit Bimbingan - Maroon Sneat Style')
@section('page_title', 'Edit Bimbingan')
@section('page_desc', 'Admin hanya mengubah penugasan (judul/nilai/status diisi dosen)')

@section('content')
    {{-- Wrapper biar card selalu di tengah --}}
    <div class="max-w-3xl mx-auto space-y-4">

        {{-- Alert error (optional) --}}
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
                        <h2 class="text-lg font-semibold text-ink-900">Edit Penugasan Bimbingan</h2>
                        <p class="text-sm text-ink-500 mt-1">
                            Ubah semester/MK spesial/mahasiswa/dosen pembimbing bila diperlukan.
                        </p>
                    </div>

                    <span
                        class="inline-flex items-center rounded-full bg-maroon-600/10 px-3 py-1 text-xs font-semibold text-maroon-800 ring-1 ring-maroon-700/10">
                        MK Spesial
                    </span>
                </div>

                <form method="POST" action="{{ route('admin.bimbingans.update', $bimbingan) }}" class="mt-6 space-y-5">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Semester --}}
                        <div>
                            <label class="block text-sm font-medium text-ink-700">Semester</label>
                            <select name="semester_id"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm
                                       focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition"
                                required>
                                @foreach ($semesters as $s)
                                    <option value="{{ $s->id }}"
                                        {{ (string) old('semester_id', $bimbingan->semester_id) === (string) $s->id ? 'selected' : '' }}>
                                        {{ $s->nama_semester }} ({{ $s->status_aktif }})
                                    </option>
                                @endforeach
                            </select>
                            @error('semester_id')
                                <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- MK Spesial --}}
                        <div>
                            <label class="block text-sm font-medium text-ink-700">Mata Kuliah (Spesial)</label>
                            <select name="mata_kuliah_id"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm
                                       focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition"
                                required>
                                @foreach ($mataKuliahs as $mk)
                                    <option value="{{ $mk->id }}"
                                        {{ (string) old('mata_kuliah_id', $bimbingan->mata_kuliah_id) === (string) $mk->id ? 'selected' : '' }}>
                                        {{ $mk->kode_mk }} — {{ $mk->nama_mk }}
                                    </option>
                                @endforeach
                            </select>
                            @error('mata_kuliah_id')
                                <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-ink-400">Hanya mata kuliah berjenis <span
                                    class="font-semibold">Spesial</span>.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Mahasiswa --}}
                        <div>
                            <label class="block text-sm font-medium text-ink-700">Mahasiswa</label>
                            <select name="mahasiswa_id"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm
                                       focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition"
                                required>
                                @foreach ($mahasiswas as $m)
                                    <option value="{{ $m->id }}"
                                        {{ (string) old('mahasiswa_id', $bimbingan->mahasiswa_id) === (string) $m->id ? 'selected' : '' }}>
                                        {{ $m->user?->nama_lengkap ?? '-' }} ({{ $m->nim }})
                                    </option>
                                @endforeach
                            </select>
                            @error('mahasiswa_id')
                                <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Dosen Pembimbing --}}
                        <div>
                            <label class="block text-sm font-medium text-ink-700">Dosen Pembimbing</label>
                            <select name="dosen_pembimbing_id"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm
                                       focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition"
                                required>
                                @foreach ($dosens as $d)
                                    <option value="{{ $d->id }}"
                                        {{ (string) old('dosen_pembimbing_id', $bimbingan->dosen_pembimbing_id) === (string) $d->id ? 'selected' : '' }}>
                                        {{ $d->user?->nama_lengkap ?? '-' }} ({{ $d->nip }})
                                    </option>
                                @endforeach
                            </select>
                            @error('dosen_pembimbing_id')
                                <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Info dari Dosen (read-only) --}}
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-sm font-semibold text-slate-900">Info dari Dosen</div>
                                <div class="text-xs text-slate-500 mt-0.5">Bagian ini read-only (diisi dosen).</div>
                            </div>

                            {{-- Badge status --}}
                            @if ($bimbingan->status === 'approved')
                                <span
                                    class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-700/10">
                                    approved
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-300/60">
                                    pending
                                </span>
                            @endif
                        </div>

                        <div class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm text-slate-700">
                            <div class="rounded-xl bg-white border border-slate-200 p-3">
                                <div class="text-xs text-slate-500">Status</div>
                                <div class="font-semibold text-slate-900">{{ $bimbingan->status }}</div>
                            </div>

                            <div class="rounded-xl bg-white border border-slate-200 p-3 sm:col-span-2">
                                <div class="text-xs text-slate-500">Judul Penelitian</div>
                                <div class="font-semibold text-slate-900">
                                    {{ $bimbingan->judul_penelitian ?? '-' }}
                                </div>
                            </div>

                            <div class="rounded-xl bg-white border border-slate-200 p-3">
                                <div class="text-xs text-slate-500">Nilai Angka</div>
                                <div class="font-semibold text-slate-900">{{ $bimbingan->nilai_angka ?? '-' }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Divider --}}
                    <div class="h-px bg-slate-200/80"></div>

                    {{-- Actions --}}
                    <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2">
                        <a href="{{ route('admin.bimbingans.index') }}"
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
            Admin hanya mengatur penugasan. Dosen mengisi judul, nilai, dan status.
        </p>
    </div>
@endsection
