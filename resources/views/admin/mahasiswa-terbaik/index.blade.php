@extends('admin.layouts.app')

@section('title', 'Mahasiswa Terbaik')
@section('page_title', 'Mahasiswa Terbaik')
@section('page_desc', 'Penentuan Mahasiswa Terbaik murni berdasarkan pencapaian IPK tertinggi pada tahun kelulusan')

@section('content')
    <div class="space-y-6 max-w-full">

        {{-- Form Filter & Tombol Aksi --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <form action="{{ route('admin.mahasiswa-terbaik.index') }}" method="GET"
                class="flex flex-col md:flex-row gap-4 items-end">
                {{-- Filter Jurusan --}}
                <div class="w-full md:w-64">
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2 ml-1">Jurusan</label>
                    <select name="jurusan_id"
                        class="w-full rounded-xl border-slate-200 text-sm focus:ring-maroon-500 focus:border-maroon-500">
                        @foreach ($jurusans as $jurusan)
                            <option value="{{ $jurusan->id }}" {{ $selectedJurusan == $jurusan->id ? 'selected' : '' }}>
                                {{ $jurusan->nama_jurusan }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Tahun Lulus --}}
                <div class="w-full md:w-40">
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2 ml-1">Tahun Lulus</label>
                    <input type="number" name="tahun_lulus" value="{{ $selectedTahun }}"
                        class="w-full rounded-xl border-slate-200 text-sm focus:ring-maroon-500 focus:border-maroon-500">
                </div>

                {{-- Aksi --}}
                <div class="flex flex-wrap gap-2 w-full md:w-auto">
                    {{-- Tombol Cari --}}
                    <button type="submit"
                        class="bg-maroon-700 text-white px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-maroon-800 transition shadow-sm shadow-maroon-700/10">
                        Cari Mahasiswa Terbaik
                    </button>

                    @if ($terbaik)
                        {{-- Tombol Export Excel --}}
                        <a href="{{ request()->fullUrlWithQuery(['export' => true]) }}"
                            class="bg-emerald-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-emerald-700 transition flex items-center gap-1.5 shadow-sm shadow-emerald-600/10">
                            📊 Export Excel
                        </a>
                    @endif
                </div>
            </form>
        </div>

        @if ($terbaik)
            {{-- HERO CARD: Highlight Mahasiswa Terbaik Urutan No.1 --}}
            <div
                class="relative overflow-hidden bg-gradient-to-br from-amber-500 via-amber-600 to-yellow-700 rounded-3xl p-6 md:p-8 text-white shadow-xl shadow-amber-600/10 flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="absolute -right-10 -bottom-10 text-white/10 pointer-events-none">
                    <span class="text-[180px] font-black leading-none">#1</span>
                </div>

                <div class="flex items-center gap-5 text-center md:text-left flex-col md:flex-row">
                    <div
                        class="w-20 h-20 bg-white/20 rounded-2xl flex items-center justify-center text-4xl shadow-inner border border-white/20 backdrop-blur-sm">
                        🏆
                    </div>
                    <div>
                        <span
                            class="bg-yellow-400/30 text-yellow-100 text-xs px-3 py-1 rounded-full font-bold uppercase tracking-wider border border-white/10">
                            Mahasiswa Terbaik Utama
                        </span>
                        <h2 class="text-2xl md:text-3xl font-black mt-2 tracking-tight">{{ $terbaik->nama_lengkap }}</h2>
                        <p class="text-amber-100 text-sm font-mono mt-0.5">NIM. {{ $terbaik->nim }} • Angkatan
                            {{ $terbaik->angkatan }}</p>

                        <div
                            class="flex flex-wrap items-center justify-center md:justify-start gap-2 mt-4 text-xs font-medium">
                            <span class="px-2.5 py-1 bg-white/10 rounded-md border border-white/10">Prodi
                                {{ $terbaik->nama_jurusan }} ({{ $terbaik->jenjang }})</span>
                            <span class="px-2.5 py-1 bg-white/10 rounded-md border border-white/10">Lulus Tahun
                                {{ $selectedTahun }}</span>
                        </div>
                    </div>
                </div>

                {{-- Nilai IPK Besar --}}
                <div
                    class="bg-white/10 border border-white/20 backdrop-blur-md rounded-2xl px-8 py-4 text-center min-w-[140px]">
                    <div class="text-[11px] font-bold uppercase tracking-widest text-amber-200">IPK Kelulusan</div>
                    <div class="text-4xl md:text-5xl font-black tracking-tighter mt-1">
                        {{ number_format($terbaik->ipk, 2) }}</div>
                    <div class="text-[10px] text-yellow-200 font-semibold mt-1">{{ $terbaik->total_nilai_a }} Total
                        Matakuliah A</div>
                </div>
            </div>

            {{-- TABEL KOMPETITOR / PERINGKAT KANDIDAT LAINNYA --}}
            <div class="space-y-3">
                <h3 class="text-lg font-bold text-slate-800 ml-1">📊 Seluruh Peringkat Kelulusan</h3>
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-50 border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 w-24">Peringkat</th>
                                    <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500">Nama Mahasiswa</th>
                                    <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 text-center">Angkatan
                                    </th>
                                    <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 text-center">IPK</th>
                                    <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 text-center">Nilai
                                        Skripsi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($semuaKandidat as $index => $mhs)
                                    <tr
                                        class="{{ $index == 0 ? 'bg-amber-50/50 font-medium' : '' }} hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <span class="font-bold text-slate-700">#{{ $index + 1 }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-bold text-slate-900">{{ $mhs->nama_lengkap }}</div>
                                            <div class="text-xs font-mono text-slate-400">{{ $mhs->nim }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-center text-slate-600 text-sm">
                                            {{ $mhs->angkatan }}
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span
                                                class="text-base font-bold text-slate-800">{{ number_format($mhs->ipk, 2) }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-center text-sm text-slate-600">
                                            {{ $mhs->nilai_skripsi ? number_format($mhs->nilai_skripsi, 2) : '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-white rounded-2xl border border-slate-200 p-16 text-center">
                <p class="text-5xl mb-4">🔍</p>
                <h3 class="font-bold text-slate-800 text-lg">Tidak Ada Data Kelulusan</h3>
                <p class="text-sm text-slate-400 mt-1">Belum ada mahasiswa yang berstatus 'Lulus' pada Jurusan dan Tahun
                    Kelulusan yang dicari.</p>
            </div>
        @endif
    </div>
@endsection
