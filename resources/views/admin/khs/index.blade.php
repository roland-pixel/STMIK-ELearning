@extends('admin.layouts.app')

@section('title', 'Kelola KHS - Maroon Clean Style')
@section('page_title', 'Kelola KHS')
@section('page_desc', 'Kelola dan cetak Kartu Hasil Studi mahasiswa dengan nuansa identitas kampus')

@section('content')
    <div class="space-y-6">
        <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100">
            <h3 class="text-xl font-bold text-slate-800 mb-6 flex items-center">
                <span class="w-1.5 h-6 bg-maroon-600 rounded-full mr-3"></span>
                Cetak KHS Mahasiswa
            </h3>

            <form method="GET" action="{{ route('admin.khs.preview') }}"
                class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end">

                {{-- Jurusan dropdown untuk filter --}}
                <div class="md:col-span-4">
                    <label class="block text-xs font-bold uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Jurusan
                    </label>
                    <div class="relative">
                        <select name="jurusan_id"
                            class="w-full pl-4 pr-10 py-3 border border-slate-200 rounded-xl bg-slate-50 text-slate-700 focus:ring-4 focus:ring-maroon-500/10 focus:border-maroon-500 focus:bg-white transition-all appearance-none cursor-pointer">
                            <option value="">Semua Jurusan</option>
                            @foreach ($mahasiswas->pluck('jurusan')->unique()->values() as $jurusan)
                                <option value="{{ $jurusan->id }}">{{ $jurusan->nama_jurusan }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-4">
                    <label class="block text-xs font-bold uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Pilih Mahasiswa
                    </label>
                    <div class="relative">
                        <select name="mahasiswa_id"
                            class="w-full pl-4 pr-10 py-3 border border-slate-200 rounded-xl bg-slate-50 text-slate-700 focus:ring-4 focus:ring-maroon-500/10 focus:border-maroon-500 focus:bg-white transition-all appearance-none cursor-pointer"
                            required>
                            <option value="">-- Pilih Mahasiswa --</option>
                            @foreach ($mahasiswas as $mahasiswa)
                                <option value="{{ $mahasiswa->id }}">
                                    {{ $mahasiswa->nim }} - {{ $mahasiswa->user->nama_lengkap }}
                                    @if ($mahasiswa->jurusan)
                                        ({{ $mahasiswa->jurusan->nama_jurusan }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-4">
                    <label class="block text-xs font-bold uppercase tracking-widest text-slate-400 mb-2 ml-1">
                        Pilih Semester
                    </label>
                    <div class="relative">
                        <select name="semester_id"
                            class="w-full pl-4 pr-10 py-3 border border-slate-200 rounded-xl bg-slate-50 text-slate-700 focus:ring-4 focus:ring-maroon-500/10 focus:border-maroon-500 focus:bg-white transition-all appearance-none cursor-pointer"
                            required>
                            <option value="">-- Pilih Semester --</option>
                            @foreach ($semesters as $semester)
                                <option value="{{ $semester->id }}">
                                    {{ $semester->nama_semester }} ({{ $semester->periode }})
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-12 md:col-start-1">
                    <button type="submit"
                        class="w-full md:w-auto bg-maroon-600 hover:bg-maroon-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg shadow-maroon-200 transition-all duration-300 transform hover:-translate-y-0.5 flex items-center justify-center space-x-2 group">
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

        {{-- Stats Cards - DYNAMIC DATA --}}
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
                    <p class="text-sm font-medium text-slate-500 uppercase tracking-wide">Semester Aktif</p>
                    <p class="text-3xl font-black text-slate-800">{{ $semesters->count() }}</p>
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
                </div>
            </div>
        </div>
    </div>
@endsection
