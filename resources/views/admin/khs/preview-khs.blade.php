@extends('admin.layouts.app')

@section('title', 'Preview KHS - Maroon Clean Style')
@section('page_title', 'Preview KHS')
@section('page_desc', 'Pratinjau Kartu Hasil Studi dengan identitas warna kampus')

@section('content')
    <div class="space-y-6">
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-slate-100">
            {{-- Header: Menggunakan gradien Maroon yang elegan --}}
            <div class="bg-gradient-to-r from-maroon-700 to-maroon-800 text-white p-8">
                <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-6">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-2xl font-black tracking-tight">Kartu Hasil Studi (KHS)</h3>
                            <p class="text-maroon-100 text-sm font-medium">Pratinjau dokumen resmi akademik</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <form method="POST" action="{{ route('admin.khs.cetak') }}" class="inline" target="_blank">
                            @csrf
                            <input type="hidden" name="mahasiswa_id" value="{{ $mahasiswa->id }}">
                            <input type="hidden" name="semester_id" value="{{ $semester->id }}">
                            <button type="submit"
                                class="bg-white text-maroon-700 hover:bg-maroon-50 font-bold py-3 px-6 rounded-xl transition-all duration-300 flex items-center gap-2 shadow-lg shadow-maroon-900/20">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                Cetak PDF
                            </button>
                        </form>
                        <a href="{{ route('admin.khs.index') }}"
                            class="bg-maroon-500/20 hover:bg-maroon-500/30 text-white font-bold py-3 px-6 rounded-xl transition-all duration-300 flex items-center gap-2 backdrop-blur-md">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                                </path>
                            </svg>
                            Kembali
                        </a>
                    </div>
                </div>
            </div>

            <div class="p-8 space-y-8">
                {{-- Info Mahasiswa --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 bg-slate-50 p-7 rounded-2xl border border-slate-100">
                    <div>
                        <h4 class="text-sm font-black uppercase tracking-widest text-maroon-600 mb-4">Informasi Institusi
                        </h4>
                        <div class="space-y-3">
                            <p class="text-lg font-bold text-slate-800">{{ config('app.name', 'Sistem Akademik Kampus') }}
                            </p>
                            <div class="space-y-1 text-sm text-slate-600">
                                <p><span class="inline-block w-24 font-medium">NIM</span>: <span
                                        class="font-bold text-slate-900">{{ $mahasiswa->nim }}</span></p>
                                <p><span class="inline-block w-24 font-medium">Nama</span>: <span
                                        class="font-bold text-slate-900">{{ $mahasiswa->user->nama_lengkap }}</span></p>
                                <p><span class="inline-block w-24 font-medium">Program Studi</span>: <span
                                        class="font-bold text-slate-900">{{ $mahasiswa->jurusan->nama_jurusan ?? '-' }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="md:border-l md:pl-8 border-slate-200">
                        <h4 class="text-sm font-black uppercase tracking-widest text-maroon-600 mb-4">Detail Akademik</h4>
                        <div class="space-y-1 text-sm text-slate-600">
                            <p><span class="inline-block w-24 font-medium">Semester</span>: <span
                                    class="font-bold text-slate-900">{{ $semester->nama_semester }}</span></p>
                            <p><span class="inline-block w-24 font-medium">Status MK</span>:
                                <span
                                    class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-700 text-xs font-bold uppercase">
                                    {{ count($khsData) }} Mata Kuliah
                                </span>
                            </p>
                            <p class="mt-4"><span class="inline-block w-24 font-medium">Tgl. Cetak</span>: <span
                                    class="font-bold text-slate-900">{{ $tanggal_cetak ?? now()->translatedFormat('d F Y') }}</span>
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Komponen Statistik --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-gradient-to-br from-amber-50 to-white p-6 rounded-2xl border border-amber-100 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <span class="p-2 bg-amber-500 rounded-lg text-white text-xl">📝</span>
                            <span class="text-xs font-bold text-amber-600 uppercase">Nilai Akhir Rata-rata</span>
                        </div>
                        <p class="text-3xl font-black text-slate-900">
                            {{ count($khsData) > 0 ? number_format($khsData->avg('nilai_akhir_angka'), 2) : '0.00' }}
                        </p>
                    </div>

                    <div class="bg-gradient-to-br from-blue-50 to-white p-6 rounded-2xl border border-blue-100 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <span class="p-2 bg-blue-500 rounded-lg text-white text-xl">📚</span>
                            <span class="text-xs font-bold text-blue-600 uppercase">Total SKS Semester</span>
                        </div>
                        <p class="text-3xl font-black text-slate-900">{{ $totalSKS }}</p>
                    </div>

                    <div
                        class="bg-gradient-to-br from-maroon-50 to-white p-6 rounded-2xl border border-maroon-100 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <span class="p-2 bg-maroon-600 rounded-lg text-white text-xl">🎓</span>
                            <span class="text-xs font-bold text-maroon-600 uppercase">Indeks Prestasi</span>
                        </div>
                        <p class="text-3xl font-black text-maroon-700">{{ number_format($ipk, 2) }}</p>
                    </div>
                </div>

                {{-- Tabel Nilai --}}
                <div class="overflow-hidden rounded-2xl border border-slate-200 shadow-sm">
                    <table class="w-full border-collapse">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th
                                    class="px-4 py-4 text-center text-xs font-black uppercase tracking-widest text-slate-500 w-12">
                                    No</th>
                                <th
                                    class="px-4 py-4 text-left text-xs font-black uppercase tracking-widest text-slate-500 w-28">
                                    Kode MK</th>
                                <th class="px-4 py-4 text-left text-xs font-black uppercase tracking-widest text-slate-500">
                                    Mata Kuliah</th>
                                <th
                                    class="px-4 py-4 text-center text-xs font-black uppercase tracking-widest text-slate-500 w-16">
                                    SKS</th>
                                <th
                                    class="px-4 py-4 text-center text-xs font-black uppercase tracking-widest text-slate-500 w-20">
                                    Tugas</th>
                                <th
                                    class="px-4 py-4 text-center text-xs font-black uppercase tracking-widest text-slate-500 w-20">
                                    UTS</th>
                                <th
                                    class="px-4 py-4 text-center text-xs font-black uppercase tracking-widest text-slate-500 w-20">
                                    UAS</th>
                                <th
                                    class="px-4 py-4 text-center text-xs font-black uppercase tracking-widest text-slate-500 w-24">
                                    Akhir</th>
                                <th
                                    class="px-4 py-4 text-center text-xs font-black uppercase tracking-widest text-slate-500 w-20">
                                    Huruf</th>
                                <th
                                    class="px-4 py-4 text-center text-xs font-black uppercase tracking-widest text-slate-500 w-20">
                                    Indeks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white text-slate-700">
                            @forelse($khsData as $index => $item)
                                <tr class="hover:bg-maroon-50/50 transition-colors">
                                    <td class="px-4 py-4 text-center text-sm font-bold text-slate-400">{{ $index + 1 }}
                                    </td>
                                    <td class="px-4 py-4 text-sm font-black text-maroon-600 uppercase">{{ $item->kode_mk }}
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-slate-700">{{ $item->nama_mk }}</span>
                                            @if ($item->jenis_mk === 'Spesial')
                                                <span
                                                    class="text-[10px] font-bold text-maroon-500 uppercase tracking-tight">✨
                                                    Mata Kuliah Spesial (Bimbingan)</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-center text-sm font-black text-slate-800">
                                        {{ $item->sks }}</td>

                                    {{-- Kolom Nilai Komponen --}}
                                    @if ($item->jenis_mk === 'Spesial')
                                        <td colspan="3"
                                            class="px-4 py-4 text-center text-xs italic text-slate-400 font-medium">Nilai
                                            Bimbingan Terpadu</td>
                                    @else
                                        <td class="px-4 py-4 text-center text-sm font-medium text-amber-700">
                                            {{ number_format($item->total_tugas ?? 0, 2) }}</td>
                                        <td class="px-4 py-4 text-center text-sm font-medium text-blue-700">
                                            {{ number_format($item->total_uts ?? 0, 2) }}</td>
                                        <td class="px-4 py-4 text-center text-sm font-medium text-purple-700">
                                            {{ number_format($item->total_uas ?? 0, 2) }}</td>
                                    @endif

                                    <td class="px-4 py-4 text-center">
                                        <span
                                            class="text-sm font-black {{ $item->nilai_akhir_angka >= 80 ? 'text-emerald-600' : ($item->nilai_akhir_angka >= 60 ? 'text-amber-500' : 'text-rose-600') }}">
                                            {{ number_format($item->nilai_akhir_angka ?? 0, 2) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-center text-sm font-black text-slate-800">
                                        {{ $item->nilai_huruf ?? '-' }}
                                    </td>
                                    <td class="px-4 py-4 text-center text-sm font-black text-slate-800">
                                        {{ number_format($item->nilai_indeks ?? 0, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center gap-2 text-slate-300">
                                            <svg class="w-12 h-12" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                </path>
                                            </svg>
                                            <p class="text-slate-500 font-medium">Belum ada data nilai semester ini</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        {{-- Footer Tabel --}}
                        <tfoot class="bg-maroon-700 text-white border-t-2 border-white/20">
                            <tr>
                                <th colspan="3"
                                    class="px-4 py-5 text-right text-xs font-black uppercase tracking-widest opacity-90">
                                    Total SKS Terhitung</th>
                                <th class="px-4 py-5 text-center text-xl font-black">{{ $totalSKS }}</th>
                                <th colspan="3"
                                    class="px-4 py-5 text-right text-xs font-black uppercase tracking-widest opacity-90">
                                    Indeks Prestasi Semester:</th>
                                <th class="px-4 py-5 text-center text-2xl font-black bg-maroon-800" colspan="3">
                                    {{ number_format($ipk, 2) }}
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Footer: Tanda Tangan & Keterangan --}}
                <div class="flex flex-col md:flex-row justify-between items-end gap-8 pt-6">
                    <div class="text-sm text-slate-400 font-medium">
                        <p class="flex items-center gap-2 mb-1">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            Dicetak pada: {{ now()->translatedFormat('d F Y, H:i') }} WIB
                        </p>
                        <p class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"></path>
                            </svg>
                            Oleh Admin: {{ auth()->user()->nama_lengkap }}
                        </p>
                    </div>

                    <div class="text-center md:text-right">
                        <p class="font-black text-slate-500 text-[10px] uppercase tracking-[0.2em] mb-12">Ketua Program
                            Studi / Admin Akademik</p>
                        <div class="flex flex-col items-center md:items-end">
                            <div class="h-0.5 w-40 bg-slate-200 mb-2"></div>
                            <p class="font-black text-lg text-maroon-700 tracking-tighter italic">Official Academic
                                Transcript</p>
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">Digenerate secara
                                sistem</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
