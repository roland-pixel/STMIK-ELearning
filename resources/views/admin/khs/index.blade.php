@extends('admin.layouts.app')

@section('title', 'Kelola KHS - Maroon Clean Style')
@section('page_title', 'Kelola KHS')
@section('page_desc', 'Kelola dan cetak Kartu Hasil Studi mahasiswa dengan nuansa identitas kampus')

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
            border-color: #b91c1c;
            box-shadow: 0 0 0 4px rgba(185, 28, 28, .10);
        }

        .select2-dropdown {
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            overflow: hidden;
        }

        .select2-search__field {
            outline: none !important;
        }
    </style>
@endpush

@section('content')
    <div class="space-y-6">
        <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100">
            <h3 class="text-xl font-bold text-slate-800 mb-6 flex items-center">
                <span class="w-1.5 h-6 bg-maroon-600 rounded-full mr-3"></span>
                Cetak KHS Mahasiswa
            </h3>

            <form method="GET" action="{{ route('admin.khs.preview') }}"
                class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end">

                <div class="md:col-span-3">
                    <label class="block text-xs font-bold uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Jurusan
                    </label>
                    <select name="jurusan_id" id="jurusan_id" class="select2-khs w-full" data-placeholder="Semua Jurusan">
                        <option value=""></option>
                        @foreach ($mahasiswas->pluck('jurusan')->filter()->unique('id')->values() as $jurusan)
                            <option value="{{ $jurusan->id }}">{{ $jurusan->nama_jurusan }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-4">
                    <label class="block text-xs font-bold uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Pilih Mahasiswa
                    </label>
                    <select name="mahasiswa_id" id="mahasiswa_id" class="select2-khs w-full"
                        data-placeholder="-- Pilih Mahasiswa --" required>
                        <option value=""></option>
                        @foreach ($mahasiswas as $mahasiswa)
                            <option value="{{ $mahasiswa->id }}">
                                {{ $mahasiswa->nim }} - {{ $mahasiswa->user->nama_lengkap }}
                                @if ($mahasiswa->jurusan)
                                    ({{ $mahasiswa->jurusan->nama_jurusan }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-3">
                    <label class="block text-xs font-bold uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Pilih Semester
                    </label>
                    <select name="semester_id" id="semester_id" class="select2-khs w-full"
                        data-placeholder="-- Pilih Semester --" required>
                        <option value=""></option>
                        @foreach ($semesters as $semester)
                            <option value="{{ $semester->id }}">
                                {{ $semester->nama_semester }} ({{ $semester->status_display }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <button type="submit"
                        class="w-full bg-maroon-600 hover:bg-maroon-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg shadow-maroon-200 transition-all duration-300 transform hover:-translate-y-0.5 flex items-center justify-center space-x-2 group">
                        <svg class="w-5 h-5 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <span>Preview KHS</span>
                    </button>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div
                class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center space-x-5 hover:border-maroon-200 transition-colors">
                <div class="p-4 bg-maroon-50 rounded-2xl text-maroon-600">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-500 uppercase tracking-wide">Total Mahasiswa</p>
                    <p class="text-3xl font-black text-slate-800">{{ $mahasiswas->count() }}</p>
                </div>
            </div>

            <div
                class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center space-x-5 hover:border-emerald-200 transition-colors">
                <div class="p-4 bg-emerald-50 rounded-2xl text-emerald-600">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-500 uppercase tracking-wide">Total Semester</p>
                    <p class="text-3xl font-black text-slate-800">{{ $semesters->count() }}</p>
                    <p class="text-xs text-emerald-600 font-medium mt-1">
                        {{ $semesters->where('is_active_display', true)->count() }} Aktif •
                        {{ $semesters->where('is_active_display', false)->count() }} Arsip
                    </p>
                </div>
            </div>

            <div
                class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center space-x-5 hover:border-orange-200 transition-colors">
                <div class="p-4 bg-orange-50 rounded-2xl text-orange-600">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-500 uppercase tracking-wide">Siap Cetak</p>
                    <p class="text-3xl font-black text-slate-800">100%</p>
                    <p class="text-xs text-orange-600 font-medium mt-1">Semua semester tersedia</p>
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
            $('.select2-khs').select2({
                width: '100%',
                placeholder: function() {
                    return $(this).data('placeholder');
                },
                allowClear: true
            });
        });
    </script>
@endpush
