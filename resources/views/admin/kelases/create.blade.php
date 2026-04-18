@extends('admin.layouts.app')

@section('title', 'Tambah Kelas - Maroon Sneat Style')
@section('page_title', 'Tambah Kelas')
@section('page_desc', 'Buat kelas baru dan tentukan dosen, mata kuliah, semester')

@section('content')
    {{-- Select2 CSS --}}
    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <style>
            .select2-container .select2-selection--single {
                height: 42px;
                border: 1px solid #cbd5e1;
                border-radius: 0.75rem;
                display: flex;
                align-items: center;
                padding-left: 0.25rem;
            }

            .select2-container--default .select2-selection--single .select2-selection__rendered {
                line-height: 42px;
                padding-left: 12px;
                color: #334155;
            }

            .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 42px;
                right: 8px;
            }

            .select2-container--default.select2-container--focus .select2-selection--single {
                border-color: #7f1d1d;
                box-shadow: 0 0 0 4px rgba(127, 29, 29, .1);
            }

            .select2-dropdown {
                border: 1px solid #cbd5e1;
                border-radius: 0.75rem;
                overflow: hidden;
            }

            .select2-search__field {
                outline: none !important;
            }
        </style>
    @endpush

    <div class="max-w-4xl mx-auto space-y-4">
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
                        <h2 class="text-lg font-semibold text-ink-900">Form Kelas</h2>
                        <p class="text-sm text-ink-500 mt-1">Isi data kelas, dosen, mata kuliah, dan komposisi nilai.</p>
                    </div>

                    <span
                        class="inline-flex items-center rounded-full bg-maroon-600/10 px-3 py-1 text-xs font-semibold text-maroon-800 ring-1 ring-maroon-700/10">
                        Akademik
                    </span>
                </div>

                <form method="POST" action="{{ route('admin.kelases.store') }}" class="mt-6 space-y-6">
                    @csrf

                    <div>
                        <h3 class="text-sm font-semibold text-ink-900">Relasi</h3>
                        <p class="text-xs text-ink-500 mt-1">Tentukan dosen pengampu, mata kuliah, dan semester.</p>

                        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                            {{-- Dosen --}}
                            <div>
                                <label class="block text-sm font-medium text-ink-700">Dosen</label>
                                <select name="dosen_id" id="dosen_id" required class="select2 mt-1 w-full">
                                    <option value="">Pilih dosen</option>
                                    @foreach ($dosens as $d)
                                        <option value="{{ $d->id }}"
                                            {{ (string) old('dosen_id') === (string) $d->id ? 'selected' : '' }}>
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
                                <select name="mata_kuliah_id" id="mata_kuliah_id" required class="select2 mt-1 w-full">
                                    <option value="">Pilih mata kuliah</option>
                                    @foreach ($mataKuliahs as $mk)
                                        <option value="{{ $mk->id }}"
                                            {{ (string) old('mata_kuliah_id') === (string) $mk->id ? 'selected' : '' }}>
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
                                <select name="semester_id" id="semester_id" required class="select2 mt-1 w-full">
                                    <option value="">Pilih semester</option>
                                    @foreach ($semesters as $s)
                                        <option value="{{ $s->id }}"
                                            {{ (string) old('semester_id') === (string) $s->id ? 'selected' : '' }}>
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

                    <div>
                        <h3 class="text-sm font-semibold text-ink-900">Identitas Kelas</h3>
                        <p class="text-xs text-ink-500 mt-1">Nama kelas, kode gabung, dan deskripsi.</p>

                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-ink-700">Nama Kelas</label>
                                <input type="text" name="nama_kelas" value="{{ old('nama_kelas') }}" required
                                    placeholder="Kelas A / IF-3A / dsb"
                                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition">
                                @error('nama_kelas')
                                    <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-ink-700">Kode Gabung (opsional)</label>
                                <input type="text" name="kode_gabung" value="{{ old('kode_gabung') }}"
                                    placeholder="Kosongkan untuk auto-generate"
                                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition">
                                @error('kode_gabung')
                                    <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-ink-500">Jika dikosongkan, sistem akan buat kode otomatis (8
                                    karakter).</p>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium text-ink-700">Deskripsi (opsional)</label>
                            <textarea name="deskripsi" rows="3" placeholder="Deskripsi singkat kelas..."
                                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition">{{ old('deskripsi') }}</textarea>
                            @error('deskripsi')
                                <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="h-px bg-slate-200/80"></div>

                    <div>
                        <h3 class="text-sm font-semibold text-ink-900">Komposisi Nilai</h3>
                        <p class="text-xs text-ink-500 mt-1">Pastikan total persentase = 100.</p>

                        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-ink-700">Persentase Tugas</label>
                                <div class="relative">
                                    <input type="number" name="persentase_tugas" value="{{ old('persentase_tugas', 30) }}"
                                        min="0" max="100" required
                                        class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 pr-10 text-sm focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition">
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
                                    <input type="number" name="persentase_uts" value="{{ old('persentase_uts', 30) }}"
                                        min="0" max="100" required
                                        class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 pr-10 text-sm focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition">
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
                                    <input type="number" name="persentase_uas" value="{{ old('persentase_uas', 40) }}"
                                        min="0" max="100" required
                                        class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 pr-10 text-sm focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition">
                                    <span
                                        class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-sm text-ink-400">%</span>
                                </div>
                                @error('persentase_uas')
                                    <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 pt-2">
                        <a href="{{ route('admin.kelases.index') }}"
                            class="inline-flex justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-ink-800 hover:bg-slate-50 transition">
                            Batal
                        </a>

                        <button type="submit"
                            class="inline-flex justify-center rounded-xl bg-maroon-700 px-4 py-2 text-sm font-semibold text-white hover:bg-maroon-800 transition shadow-[0_10px_22px_rgba(127,29,29,0.18)]">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <p class="text-xs text-ink-400 text-center">
            Tip: gunakan nama kelas yang konsisten (mis. IF-3A) biar mudah dicari.
        </p>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(function() {
            $('.select2').select2({
                width: '100%',
                placeholder: 'Cari / pilih data',
                allowClear: true
            });
        });
    </script>
@endpush
