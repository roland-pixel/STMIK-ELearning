@extends('admin.layouts.app')

@section('title', 'Input Nilai Manual - Admin')
@section('page_title', 'Input Nilai Manual')
@section('page_desc', 'Daftar kelas yang belum memiliki data nilai sama sekali.')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <style>
        .ts-control {
            border-radius: 0.75rem !important;
            padding: 0.45rem 0.75rem !important;
            border-color: #e2e8f0 !important;
        }

        .ts-wrapper.focus .ts-control {
            border-color: #7f1d1d !important;
            box-shadow: 0 0 0 4px rgba(127, 29, 29, 0.1) !important;
        }
    </style>
@endpush

@section('content')
    <div class="space-y-4">
        {{-- Flash Message --}}
        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        {{-- Section Filter --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft">
            <form action="{{ route('admin.input_nilai_manual.index') }}" method="GET"
                class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div>
                    <label class="block text-xs font-bold text-ink-600 uppercase mb-2">Filter Mata Kuliah</label>
                    <select name="mata_kuliah_id" class="select-filter">
                        <option value="">Semua Mata Kuliah</option>
                        @foreach ($listMataKuliah as $mk)
                            <option value="{{ $mk->id }}"
                                {{ request('mata_kuliah_id') == $mk->id ? 'selected' : '' }}>
                                {{ $mk->kode_mk }} - {{ $mk->nama_mk }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-ink-600 uppercase mb-2">Filter Dosen</label>
                    <select name="dosen_id" class="select-filter">
                        <option value="">Semua Dosen</option>
                        @foreach ($listDosen as $d)
                            <option value="{{ $d->id }}" {{ request('dosen_id') == $d->id ? 'selected' : '' }}>
                                {{ $d->user->nama_lengkap }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit"
                        class="flex-1 bg-maroon-700 text-white rounded-xl px-4 py-2 text-sm font-bold hover:bg-maroon-800 transition">
                        Terapkan Filter
                    </button>
                    @if (request('mata_kuliah_id') || request('dosen_id'))
                        <a href="{{ route('admin.input_nilai_manual.index') }}"
                            class="bg-slate-100 text-slate-600 rounded-xl px-4 py-2 text-sm font-bold hover:bg-slate-200 transition text-center">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Tabel Data --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-soft overflow-hidden">
            <div class="p-6 border-b border-slate-100">
                <h2 class="text-lg font-semibold text-ink-900">Pilih Kelas Kuliah</h2>
                <p class="text-sm text-ink-500">Menampilkan {{ $kelasKosong->count() }} kelas yang siap diinput nilai.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-ink-600 uppercase text-[11px] font-bold tracking-wider">
                        <tr>
                            <th class="px-6 py-4">Mata Kuliah</th>
                            <th class="px-6 py-4">Kelas</th>
                            <th class="px-6 py-4">Dosen Pengampu</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($kelasKosong as $kelas)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-ink-900">{{ $kelas->mataKuliah->nama_mk }}</div>
                                    <div class="text-xs text-ink-500">{{ $kelas->mataKuliah->kode_mk }}</div>
                                </td>
                                <td class="px-6 py-4 text-ink-700">
                                    <span
                                        class="inline-flex items-center rounded-md bg-slate-100 px-2 py-1 text-xs font-medium text-slate-600">
                                        {{ $kelas->nama_kelas }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-ink-700">
                                    {{ $kelas->dosen->user->nama_lengkap ?? 'Tanpa Dosen' }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('admin.input_nilai_manual.create', $kelas->id) }}"
                                        class="inline-flex items-center justify-center rounded-xl bg-maroon-700 px-4 py-2 text-xs font-bold text-white hover:bg-maroon-800 transition">
                                        Input Nilai
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-ink-400 italic">
                                    Tidak ditemukan kelas yang cocok dengan filter atau semua nilai sudah terisi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.querySelectorAll('.select-filter').forEach((el) => {
            new TomSelect(el, {
                create: false,
                sortField: {
                    field: "text",
                    order: "asc"
                }
            });
        });
    </script>
@endpush
