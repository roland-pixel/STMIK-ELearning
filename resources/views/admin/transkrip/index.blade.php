@extends('admin.layouts.app')

@section('title', 'Kelola Transkrip - Maroon Clean Style')
@section('page_title', 'Kelola Transkrip')
@section('page_desc', 'Kelola dan cetak Transkrip Nilai mahasiswa secara kumulatif')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container .select2-selection--single {
            height: 48px;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            background: #f8fafc;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 48px;
            padding-left: 14px;
            color: #334155;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 48px;
            right: 8px;
        }

        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #800000;
            box-shadow: 0 0 0 4px rgba(128, 0, 0, .10);
        }

        .select2-dropdown {
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            overflow: hidden;
        }

        .bg-maroon-600 {
            background-color: #800000;
        }

        .text-maroon-600 {
            color: #800000;
        }

        .bg-maroon-50 {
            background-color: #fff1f1;
        }
    </style>
@endpush

@section('content')
    <div class="space-y-6">
        {{-- Form Pencarian & Filter --}}
        <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100">
            <h3 class="text-xl font-bold text-slate-800 mb-6 flex items-center">
                <span class="w-1.5 h-6 bg-maroon-600 rounded-full mr-3"></span>
                Cetak Transkrip Mahasiswa
            </h3>

            <form method="GET" action="{{ route('admin.transkrip.index') }}"
                class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end">

                {{-- Input Cari Nama/NIM --}}
                <div class="md:col-span-4">
                    <label class="block text-xs font-bold uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Cari Mahasiswa (Nama/NIM)
                    </label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Ketik Nama atau NIM..."
                        class="w-full h-[48px] px-4 rounded-xl border border-slate-200 bg-slate-50 focus:ring-4 focus:ring-maroon-50 focus:border-maroon-600 transition-all outline-none">
                </div>

                {{-- Filter Jurusan --}}
                <div class="md:col-span-3">
                    <label class="block text-xs font-bold uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Jurusan
                    </label>
                    <select name="jurusan_id" id="jurusan_id" class="select2-transkrip w-full"
                        data-placeholder="Semua Jurusan">
                        <option value=""></option>
                        @foreach ($jurusans as $jurusan)
                            <option value="{{ $jurusan->id }}"
                                {{ request('jurusan_id') == $jurusan->id ? 'selected' : '' }}>
                                {{ $jurusan->nama_jurusan }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Angkatan --}}
                <div class="md:col-span-3">
                    <label class="block text-xs font-bold uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Angkatan
                    </label>
                    <select name="angkatan" id="angkatan" class="select2-transkrip w-full"
                        data-placeholder="Semua Angkatan">
                        <option value=""></option>
                        @foreach ($angkatans as $angkatan)
                            <option value="{{ $angkatan }}" {{ request('angkatan') == $angkatan ? 'selected' : '' }}>
                                {{ $angkatan }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Tombol Filter --}}
                <div class="md:col-span-2">
                    <button type="submit"
                        class="w-full bg-maroon-600 hover:bg-maroon-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg shadow-maroon-100 transition-all duration-300 transform hover:-translate-y-0.5 flex items-center justify-center space-x-2">
                        <i class="fas fa-filter text-sm"></i>
                        <span>Filter</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- Tabel Data --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-slate-500">NIM</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-slate-500">Nama Lengkap
                            </th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-slate-500">Program Studi
                            </th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-slate-500">Angkatan</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-slate-500 text-center">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($mahasiswas as $mhs)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 font-bold text-slate-700">{{ $mhs->nim }}</td>
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-slate-800">{{ strtoupper($mhs->user->nama_lengkap) }}
                                    </div>
                                    <div class="text-xs text-slate-400">{{ $mhs->user->email }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-lg bg-slate-100 text-slate-600 text-xs font-bold">
                                        {{ $mhs->jurusan->nama_jurusan ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-600 font-medium">{{ $mhs->angkatan }}</td>
                                <td class="px-6 py-4 text-center">
                                    <a href="{{ route('admin.transkrip.cetak', $mhs->id) }}" target="_blank"
                                        class="inline-flex items-center space-x-2 bg-white border border-maroon-600 text-maroon-600 hover:bg-maroon-600 hover:text-white font-bold py-2 px-4 rounded-xl transition-all text-sm group">
                                        <i class="fas fa-print group-hover:rotate-12 transition-transform"></i>
                                        <span>Cetak PDF</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-400 italic">
                                    Tidak ada data mahasiswa ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($mahasiswas->hasPages())
                <div class="p-6 border-t border-slate-50">
                    {{ $mahasiswas->links() }}
                </div>
            @endif
        </div>

        {{-- Statistik Bawah --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div
                class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center space-x-5 hover:border-maroon-200 transition-colors">
                <div class="p-4 bg-maroon-50 rounded-2xl text-maroon-600">
                    <i class="fas fa-user-graduate text-2xl"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-500 uppercase tracking-wide">Total Mahasiswa</p>
                    <p class="text-3xl font-black text-slate-800">{{ $mahasiswas->total() }}</p>
                </div>
            </div>

            <div
                class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center space-x-5 hover:border-blue-200 transition-colors">
                <div class="p-4 bg-blue-50 rounded-2xl text-blue-600">
                    <i class="fas fa-university text-2xl"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-500 uppercase tracking-wide">Total Jurusan</p>
                    <p class="text-3xl font-black text-slate-800">{{ $jurusans->count() }}</p>
                </div>
            </div>

            <div
                class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center space-x-5 hover:border-emerald-200 transition-colors">
                <div class="p-4 bg-emerald-50 rounded-2xl text-emerald-600">
                    <i class="fas fa-check-circle text-2xl"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-500 uppercase tracking-wide">Status Sistem</p>
                    <p class="text-3xl font-black text-slate-800">Ready</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(function() {
            $('.select2-transkrip').select2({
                width: '100%',
                placeholder: function() {
                    return $(this).data('placeholder');
                },
                allowClear: true
            });
        });
    </script>
@endpush
