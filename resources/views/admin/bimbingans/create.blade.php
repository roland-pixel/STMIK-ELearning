@extends('admin.layouts.app')

@section('title', 'Tambah Bimbingan - Maroon Sneat Style')
@section('page_title', 'Tambah Bimbingan')
@section('page_desc', 'Admin hanya mengisi penugasan (judul/nilai/status diisi dosen)')

{{-- Tambahkan library Tom Select di head/push --}}
@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <style>
        /* Custom styling agar Tom Select matching dengan Maroon Sneat Style */
        .ts-control {
            border-radius: 0.75rem !important;
            /* rounded-xl */
            padding: 0.5rem 0.75rem !important;
            border-color: #cbd5e1 !important;
            /* border-slate-300 */
            box-shadow: none !important;
        }

        .ts-wrapper.focus .ts-control {
            border-color: #7f1d1d !important;
            /* maroon-700 */
            box-shadow: 0 0 0 4px rgba(127, 29, 29, 0.1) !important;
        }

        .ts-dropdown {
            border-radius: 0.75rem !important;
            margin-top: 5px !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
        }
    </style>
@endpush

@section('content')
    <div class="max-w-3xl mx-auto space-y-4">

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
                        <h2 class="text-lg font-semibold text-ink-900">Form Bimbingan</h2>
                        <p class="text-sm text-ink-500 mt-1">
                            Lengkapi penugasan bimbingan untuk mahasiswa.
                        </p>
                    </div>

                    <span
                        class="inline-flex items-center rounded-full bg-maroon-600/10 px-3 py-1 text-xs font-semibold text-maroon-800 ring-1 ring-maroon-700/10">
                        MK Spesial
                    </span>
                </div>

                <form method="POST" action="{{ route('admin.bimbingans.store') }}" class="mt-6 space-y-5">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Semester (Otomatis Aktif) --}}
                        <div>
                            <label class="block text-sm font-medium text-ink-700">Semester Perkuliahan</label>
                            @if ($semesterAktif)
                                <div class="relative mt-1">
                                    <input type="text" value="{{ $semesterAktif->nama_semester }}"
                                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600 font-semibold cursor-not-allowed"
                                        readonly />
                                    <input type="hidden" name="semester_id" value="{{ $semesterAktif->id }}">
                                    <span class="absolute right-3 top-2.5">
                                        <svg class="h-4 w-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </div>
                                <p class="mt-1 text-[10px] text-emerald-600 font-medium italic">* Terdeteksi sebagai
                                    semester aktif.</p>
                            @else
                                <select name="semester_id"
                                    class="mt-1 w-full rounded-xl border border-rose-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition"
                                    required>
                                    <option value="" disabled selected>Pilih semester</option>
                                    @foreach ($semesters as $s)
                                        <option value="{{ $s->id }}"
                                            {{ old('semester_id') == $s->id ? 'selected' : '' }}>
                                            {{ $s->nama_semester }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-[10px] text-rose-600 italic">* Pilih manual.</p>
                            @endif
                        </div>

                        {{-- MK Spesial --}}
                        <div>
                            <label class="block text-sm font-medium text-ink-700">Mata Kuliah (Spesial)</label>
                            <select name="mata_kuliah_id"
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition"
                                required>
                                <option value="" disabled {{ old('mata_kuliah_id') ? '' : 'selected' }}>Pilih MK
                                    Spesial</option>
                                @foreach ($mataKuliahs as $mk)
                                    <option value="{{ $mk->id }}"
                                        {{ (string) old('mata_kuliah_id') === (string) $mk->id ? 'selected' : '' }}>
                                        {{ $mk->kode_mk }} — {{ $mk->nama_mk }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Mahasiswa (DENGAN SEARCH) --}}
                        <div>
                            <label class="block text-sm font-medium text-ink-700">Mahasiswa</label>
                            <div class="mt-1">
                                <select id="select-mahasiswa" name="mahasiswa_id" autocomplete="off" required>
                                    <option value="">Cari Nama atau NIM...</option>
                                    @foreach ($mahasiswas as $m)
                                        <option value="{{ $m->id }}"
                                            {{ (string) old('mahasiswa_id') === (string) $m->id ? 'selected' : '' }}>
                                            {{ $m->nim }} - {{ $m->user?->nama_lengkap ?? '-' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('mahasiswa_id')
                                <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Dosen Pembimbing (DENGAN SEARCH) --}}
                        <div>
                            <label class="block text-sm font-medium text-ink-700">Dosen Pembimbing</label>
                            <div class="mt-1">
                                <select id="select-dosen" name="dosen_pembimbing_id" autocomplete="off" required>
                                    <option value="">Cari Nama atau NIP...</option>
                                    @foreach ($dosens as $d)
                                        <option value="{{ $d->id }}"
                                            {{ (string) old('dosen_pembimbing_id') === (string) $d->id ? 'selected' : '' }}>
                                            {{ $d->user?->nama_lengkap ?? '-' }} ({{ $d->nip }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('dosen_pembimbing_id')
                                <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="h-px bg-slate-200/80 mt-2"></div>

                    <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2">
                        <a href="{{ route('admin.bimbingans.index') }}"
                            class="inline-flex justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-ink-800 hover:bg-slate-50 transition">
                            Batal
                        </a>

                        <button type="submit"
                            class="inline-flex justify-center rounded-xl bg-maroon-700 px-4 py-2 text-sm font-semibold text-white hover:bg-maroon-800 transition shadow-[0_10px_22px_rgba(127,29,29,0.18)]">
                            Simpan Penugasan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <p class="text-xs text-ink-400 text-center">
            Catatan: Judul penelitian, nilai, dan status akan diisi oleh dosen pembimbing melalui akun dosen.
        </p>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Inisialisasi Search Dropdown Mahasiswa
            new TomSelect("#select-mahasiswa", {
                create: false,
                sortField: {
                    field: "text",
                    order: "asc"
                },
                maxOptions: 1000, // Menampilkan hingga 1000 item dalam list search
                placeholder: "Ketik NIM atau Nama Mahasiswa...",
            });

            // Inisialisasi Search Dropdown Dosen
            new TomSelect("#select-dosen", {
                create: false,
                sortField: {
                    field: "text",
                    order: "asc"
                },
                placeholder: "Ketik NIP atau Nama Dosen...",
            });
        });
    </script>
@endpush
