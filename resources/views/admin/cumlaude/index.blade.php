@extends('admin.layouts.app')

@section('title', 'Daftar Cumlaude')
@section('page_title', 'Daftar Cumlaude')
@section('page_desc', 'Mahasiswa yang lulus dengan predikat pujian')

@section('content')
    <div class="space-y-6 max-w-full">

        @if (session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 animate-pulse">
                {{ session('success') }}
            </div>
        @endif

        {{-- Form Filter & Toolbar --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <form action="{{ route('admin.cumlaude.index') }}" method="GET"
                class="flex flex-col md:flex-row gap-4 items-end">

                {{-- Filter Jurusan --}}
                <div class="w-full md:w-64">
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2 ml-1">Jurusan</label>
                    <select name="jurusan_id"
                        class="w-full rounded-xl border-slate-200 text-sm focus:ring-maroon-500 focus:border-maroon-500">
                        <option value="">Semua Jurusan</option>
                        @foreach ($jurusans as $jurusan)
                            <option value="{{ $jurusan->id }}"
                                {{ request('jurusan_id') == $jurusan->id ? 'selected' : '' }}>
                                {{ $jurusan->nama_jurusan }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Angkatan --}}
                <div class="w-full md:w-40">
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2 ml-1">Angkatan</label>
                    <input type="number" name="angkatan" value="{{ request('angkatan') }}" placeholder="Contoh: 2020"
                        class="w-full rounded-xl border-slate-200 text-sm focus:ring-maroon-500 focus:border-maroon-500">
                </div>

                {{-- Tombol Filter --}}
                <div class="flex gap-2 w-full md:w-auto">
                    <button type="submit"
                        class="flex-1 md:flex-none bg-slate-800 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-black transition">
                        Filter
                    </button>
                    <a href="{{ route('admin.cumlaude.index') }}"
                        class="bg-slate-100 text-slate-600 px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-slate-200 transition">
                        Reset
                    </a>
                </div>

                {{-- Spacer --}}
                <div class="flex-grow"></div>

                {{-- Export Button --}}
                <div class="w-full md:w-auto">
                    <a href="{{ request()->fullUrlWithQuery(['export' => 1]) }}"
                        class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-maroon-700 px-5 py-2.5 text-sm font-semibold text-white
                           hover:bg-maroon-800 transition shadow-lg shadow-maroon-900/20">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Export Excel
                    </a>
                </div>
            </form>
        </div>

        {{-- Ringkasan Syarat --}}
        <div class="flex flex-col md:flex-row gap-4 items-center justify-between px-2">
            <div>
                <h3 class="text-lg font-bold text-slate-800">📋 Hasil Seleksi</h3>
                <p class="text-sm text-slate-500">Ditemukan <span
                        class="font-bold text-maroon-700">{{ $total }}</span> kandidat cumlaude.</p>
            </div>
            <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-2 flex items-center gap-3">
                <span class="text-amber-600 text-lg">💡</span>
                <span class="text-xs text-amber-800 leading-tight">
                    <strong>Kriteria:</strong> IPK ≥ 3.51, Min. B-, Tepat Waktu (S1 ≤ 8 Smt, D3 ≤ 6 Smt), Tidak Rekos, &
                    Reguler/Malam.
                </span>
            </div>
        </div>

        {{-- Tabel --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500">Rank</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500">Mahasiswa</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500">Program Studi</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 text-center">Angkatan</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 text-center">IPK</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 text-center">Skripsi/Tugas Akhir
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($daftar as $mhs)
                            <tr class="hover:bg-slate-50 transition-colors">
                                {{-- Peringkat dengan badge --}}
                                <td class="px-6 py-4">
                                    @if ($mhs->peringkat <= 3)
                                        <div
                                            class="flex items-center justify-center w-8 h-8 rounded-full font-bold 
                                        {{ $mhs->peringkat == 1 ? 'bg-yellow-100 text-yellow-700 ring-2 ring-yellow-400' : '' }}
                                        {{ $mhs->peringkat == 2 ? 'bg-slate-100 text-slate-600 ring-2 ring-slate-300' : '' }}
                                        {{ $mhs->peringkat == 3 ? 'bg-orange-100 text-orange-700 ring-2 ring-orange-300' : '' }}">
                                            {{ $mhs->peringkat }}
                                        </div>
                                    @else
                                        <span class="ml-3 font-medium text-slate-400">#{{ $mhs->peringkat }}</span>
                                    @endif
                                </td>

                                {{-- Info Mahasiswa --}}
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900">{{ $mhs->nama_lengkap }}</div>
                                    <div class="text-xs font-mono text-slate-500">{{ $mhs->nim }}</div>
                                </td>

                                {{-- Jurusan --}}
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="text-xs px-2 py-0.5 bg-slate-200 text-slate-800 font-bold rounded-md font-mono">
                                            {{ $mhs->jenjang }}
                                        </span>
                                        <span class="text-sm text-slate-700 font-medium">{{ $mhs->nama_jurusan }}</span>
                                    </div>
                                </td>

                                {{-- Angkatan --}}
                                <td class="px-6 py-4 text-center">
                                    <span class="text-sm px-3 py-1 bg-slate-100 rounded-lg text-slate-600 font-medium">
                                        {{ $mhs->angkatan }}
                                    </span>
                                </td>

                                {{-- IPK Badge --}}
                                <td class="px-6 py-4 text-center">
                                    <div class="text-lg font-black text-slate-800">{{ number_format($mhs->ipk, 2) }}</div>
                                    @if ($mhs->total_nilai_a > 0)
                                        <div class="text-[10px] text-emerald-600 font-bold uppercase tracking-tighter">
                                            {{ $mhs->total_nilai_a }} Nilai A
                                        </div>
                                    @endif
                                </td>

                                {{-- Nilai Skripsi & Tiebreaker info --}}
                                <td class="px-6 py-4 text-center border-l border-slate-50">
                                    @if ($mhs->nilai_skripsi)
                                        <div class="text-sm font-bold text-maroon-700">
                                            {{ number_format($mhs->nilai_skripsi, 2) }}</div>
                                        <div class="text-[10px] text-slate-400 uppercase truncate max-w-[150px] mx-auto"
                                            title="{{ $mhs->nama_mk_skripsi }}">
                                            {{ $mhs->nama_mk_skripsi }}
                                        </div>
                                    @else
                                        <span class="text-slate-300">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <div class="max-w-xs mx-auto">
                                        <p class="text-4xl mb-4">🔍</p>
                                        <p class="font-bold text-slate-800">Tidak ada data</p>
                                        <p class="text-sm text-slate-500">Gunakan filter untuk mempersempit pencarian atau
                                            belum ada mahasiswa yang memenuhi syarat.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
