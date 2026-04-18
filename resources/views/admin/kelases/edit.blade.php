@extends('admin.layouts.app')

@section('title', 'Edit Kelas - Maroon Sneat Style')
@section('page_title', 'Edit Kelas')
@section('page_desc', 'Perbarui data kelas')

@section('content')
    {{-- Wrapper biar card selalu di tengah --}}
    <div class="max-w-4xl mx-auto space-y-4">

        {{-- Alert error (ringkas + jelas) --}}
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

        {{-- Card --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-soft">
            <div class="p-6 sm:p-7">
                {{-- Header card --}}
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-ink-900">Perbarui Kelas</h2>
                        <p class="text-sm text-ink-500 mt-1">Ubah relasi, identitas kelas, dan komposisi nilai.</p>
                    </div>

                    <span
                        class="inline-flex items-center rounded-full bg-maroon-600/10 px-3 py-1 text-xs font-semibold text-maroon-800 ring-1 ring-maroon-700/10">
                        Edit Mode
                    </span>
                </div>

                <form method="POST" action="{{ route('admin.kelases.update', $kelase) }}" class="mt-6 space-y-6">
                    @csrf
                    @method('PUT')

                    {{-- SECTION: Relasi --}}
                    <div>
                        <h3 class="text-sm font-semibold text-ink-900">Relasi</h3>
                        <p class="text-xs text-ink-500 mt-1">Tentukan dosen pengampu, mata kuliah, dan semester.</p>

                        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                            {{-- Dosen --}}
                            <div>
                                <label class="block text-sm font-medium text-ink-700">Dosen</label>
                                <select name="dosen_id" required
                                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm
                                           focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition">
                                    @foreach ($dosens as $d)
                                        <option value="{{ $d->id }}"
                                            {{ (string) old('dosen_id', $kelase->dosen_id) === (string) $d->id ? 'selected' : '' }}>
                                            {{ $d->user?->nama_lengkap ?? '-' }} ({{ $d->nip }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('dosen_id')
                                    <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Mata Kuliah --}}
                            <div>
                                <label class="block text-sm font-medium text-ink-700">Mata Kuliah</label>
                                <select name="mata_kuliah_id" required
                                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm
                                           focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition">
                                    @foreach ($mataKuliahs as $mk)
                                        <option value="{{ $mk->id }}"
                                            {{ (string) old('mata_kuliah_id', $kelase->mata_kuliah_id) === (string) $mk->id ? 'selected' : '' }}>
                                            {{ $mk->kode_mk }} — {{ $mk->nama_mk }} ({{ $mk->sks }} SKS)
                                        </option>
                                    @endforeach
                                </select>
                                @error('mata_kuliah_id')
                                    <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Semester --}}
                            <div>
                                <label class="block text-sm font-medium text-ink-700">Semester</label>
                                <select name="semester_id" required
                                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm
                                           focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition">
                                    @foreach ($semesters as $s)
                                        <option value="{{ $s->id }}"
                                            {{ (string) old('semester_id', $kelase->semester_id) === (string) $s->id ? 'selected' : '' }}>
                                            {{ $s->nama_semester }} ({{ $s->status_aktif }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('semester_id')
                                    <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="h-px bg-slate-200/80"></div>

                    {{-- SECTION: Identitas Kelas --}}
                    <div>
                        <h3 class="text-sm font-semibold text-ink-900">Identitas Kelas</h3>
                        <p class="text-xs text-ink-500 mt-1">Nama kelas, kode gabung, dan deskripsi.</p>

                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Nama Kelas --}}
                            <div>
                                <label class="block text-sm font-medium text-ink-700">Nama Kelas</label>
                                <input type="text" name="nama_kelas"
                                    value="{{ old('nama_kelas', $kelase->nama_kelas) }}" required
                                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm
                                           focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition">
                                @error('nama_kelas')
                                    <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Kode Gabung --}}
                            <div>
                                <label class="block text-sm font-medium text-ink-700">Kode Gabung</label>
                                <input type="text" name="kode_gabung"
                                    value="{{ old('kode_gabung', $kelase->kode_gabung) }}" required
                                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm
                                           focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition">
                                @error('kode_gabung')
                                    <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Deskripsi --}}
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-ink-700">Deskripsi (opsional)</label>
                            <textarea name="deskripsi" rows="3"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm
                                       focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition">{{ old('deskripsi', $kelase->deskripsi) }}</textarea>
                            @error('deskripsi')
                                <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="h-px bg-slate-200/80"></div>

                    {{-- SECTION: Komposisi Nilai --}}
                    <div>
                        <h3 class="text-sm font-semibold text-ink-900">Komposisi Nilai</h3>
                        <p class="text-xs text-ink-500 mt-1">Pastikan total persentase = 100.</p>

                        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-ink-700">Persentase Tugas</label>
                                <div class="relative">
                                    <input type="number" name="persentase_tugas"
                                        value="{{ old('persentase_tugas', $kelase->persentase_tugas) }}" min="0"
                                        max="100" required
                                        class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 pr-10 text-sm
                                               focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition">
                                    <span
                                        class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-sm text-ink-400">%</span>
                                </div>
                                @error('persentase_tugas')
                                    <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-ink-700">Persentase UTS</label>
                                <div class="relative">
                                    <input type="number" name="persentase_uts"
                                        value="{{ old('persentase_uts', $kelase->persentase_uts) }}" min="0"
                                        max="100" required
                                        class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 pr-10 text-sm
                                               focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition">
                                    <span
                                        class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-sm text-ink-400">%</span>
                                </div>
                                @error('persentase_uts')
                                    <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-ink-700">Persentase UAS</label>
                                <div class="relative">
                                    <input type="number" name="persentase_uas"
                                        value="{{ old('persentase_uas', $kelase->persentase_uas) }}" min="0"
                                        max="100" required
                                        class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 pr-10 text-sm
                                               focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition">
                                    <span
                                        class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-sm text-ink-400">%</span>
                                </div>
                                @error('persentase_uas')
                                    <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 pt-2">
                        <a href="{{ route('admin.kelases.index') }}"
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
            Tip: kalau ganti komposisi nilai, pastikan total tetap 100.
        </p>
    </div>
@endsection
