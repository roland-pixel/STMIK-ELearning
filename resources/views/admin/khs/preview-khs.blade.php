@extends('admin.layouts.app')

@section('title', 'Preview KHS')
@section('page_title', 'Preview KHS')
@section('page_desc', 'Pratinjau Kartu Hasil Studi')

@section('content')
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">

        {{-- Top bar --}}
        <div
            class="flex flex-col sm:flex-row items-start sm:items-center justify-between px-4 sm:px-6 py-4 border-b border-slate-100 gap-3">
            <div>
                <h2 class="text-lg sm:text-xl font-serif font-normal text-slate-900 tracking-tight">
                    Kartu Hasil Studi
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Pratinjau dokumen resmi akademik</p>
            </div>

            <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                <form method="POST" action="{{ route('admin.khs.cetak') }}" target="_blank" class="flex-1 sm:flex-none">
                    @csrf
                    <input type="hidden" name="mahasiswa_id" value="{{ $mahasiswa->id }}">
                    <input type="hidden" name="semester_id" value="{{ $semester->id }}">
                    <button type="submit"
                        class="inline-flex items-center justify-center gap-1.5 text-sm font-medium px-4 py-2 rounded-lg bg-maroon-700 text-white hover:bg-maroon-800 transition-colors w-full sm:w-auto">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Cetak PDF</span>
                    </button>
                </form>
                <a href="{{ route('admin.khs.index') }}"
                    class="inline-flex items-center justify-center gap-1.5 text-sm font-medium px-4 py-2 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors w-full sm:w-auto">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span>Kembali</span>
                </a>
            </div>
        </div>

        <div class="p-4 sm:p-6 space-y-4 sm:space-y-5">

            {{-- Info mahasiswa --}}
            <div class="grid grid-cols-1 md:grid-cols-2 border border-slate-200 rounded-lg overflow-hidden">
                <div class="p-4 sm:p-5">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-maroon-700 mb-3">Informasi Institusi
                    </p>
                    <p class="text-base sm:text-[15px] font-medium text-slate-900 mb-2.5">
                        {{ config('app.name', 'Sistem Akademik Kampus') }}</p>
                    <div class="space-y-1.5 text-sm">
                        <div class="flex gap-2 text-slate-500">
                            <span class="w-20 sm:w-24 shrink-0 font-medium">NIM</span>
                            <span class="font-medium text-slate-800 break-all">{{ $mahasiswa->nim }}</span>
                        </div>
                        <div class="flex gap-2 text-slate-500">
                            <span class="w-20 sm:w-24 shrink-0 font-medium">Nama</span>
                            <span class="font-medium text-slate-800">{{ $mahasiswa->user->nama_lengkap }}</span>
                        </div>
                        <div class="flex gap-2 text-slate-500">
                            <span class="w-20 sm:w-24 shrink-0 font-medium">Prodi</span>
                            <span class="font-medium text-slate-800">{{ $mahasiswa->jurusan->nama_jurusan ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                <div class="p-4 sm:p-5 border-t md:border-t-0 md:border-l border-slate-200">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-maroon-700 mb-3">Detail Akademik</p>
                    <div class="space-y-1.5 text-sm">
                        <div class="flex gap-2 text-slate-500">
                            <span class="w-20 sm:w-24 shrink-0 font-medium">Semester</span>
                            <span class="font-medium text-slate-800">{{ $semester->nama_semester }}</span>
                        </div>
                        <div class="flex gap-2 text-slate-500 items-center">
                            <span class="w-20 sm:w-24 shrink-0 font-medium">Jumlah MK</span>
                            <span
                                class="text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 px-2 py-0.5 rounded-full shrink-0">
                                {{ count($khsData) }} MK
                            </span>
                        </div>
                        <div class="flex gap-2 text-slate-500 pt-1">
                            <span class="w-20 sm:w-24 shrink-0 font-medium">Tgl. Cetak</span>
                            <span
                                class="font-medium text-slate-800">{{ $tanggal_cetak ?? now()->translatedFormat('d F Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Statistik --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="bg-slate-50 rounded-lg p-4">
                    <p class="text-[11px] font-medium text-slate-500 mb-1.5">Rata-rata nilai akhir</p>
                    <p class="text-2xl font-semibold text-slate-900 tabular-nums">
                        {{ count($khsData) > 0 ? number_format($khsData->avg('nilai_akhir_angka'), 2) : '0.00' }}
                    </p>
                </div>
                <div class="bg-slate-50 rounded-lg p-4">
                    <p class="text-[11px] font-medium text-slate-500 mb-1.5">Total SKS semester</p>
                    <p class="text-2xl font-semibold text-slate-900 tabular-nums">{{ $totalSKS }}</p>
                </div>
                <div class="bg-slate-50 rounded-lg p-4">
                    <p class="text-[11px] font-medium text-slate-500 mb-1.5">Indeks prestasi</p>
                    <p class="text-2xl font-semibold text-maroon-700 tabular-nums">{{ number_format($ipk, 2) }}</p>
                </div>
            </div>

            {{-- Tabel nilai (responsive dengan scroll) --}}
            <div class="border border-slate-200 rounded-lg overflow-hidden">
                <div class="overflow-x-auto -mx-4 sm:mx-0">
                    <div class="inline-block min-w-full align-middle sm:px-6">
                        <table class="w-full text-sm border-collapse min-w-[600px]">
                            <thead class="bg-slate-50 border-b border-slate-200">
                                <tr>
                                    <th
                                        class="px-3 py-3 text-center text-[10px] font-semibold uppercase tracking-widest text-slate-500 w-10">
                                        No</th>
                                    <th
                                        class="px-3 py-3 text-left text-[10px] font-semibold uppercase tracking-widest text-slate-500 w-24">
                                        Kode</th>
                                    <th
                                        class="px-3 py-3 text-left text-[10px] font-semibold uppercase tracking-widest text-slate-500 min-w-[200px]">
                                        Mata Kuliah</th>
                                    <th
                                        class="px-3 py-3 text-center text-[10px] font-semibold uppercase tracking-widest text-slate-500 w-14">
                                        SKS</th>
                                    <th
                                        class="px-3 py-3 text-center text-[10px] font-semibold uppercase tracking-widest text-slate-500 w-16 hidden md:table-cell">
                                        Tugas</th>
                                    <th
                                        class="px-3 py-3 text-center text-[10px] font-semibold uppercase tracking-widest text-slate-500 w-16 hidden md:table-cell">
                                        UTS</th>
                                    <th
                                        class="px-3 py-3 text-center text-[10px] font-semibold uppercase tracking-widest text-slate-500 w-16 hidden md:table-cell">
                                        UAS</th>
                                    <th
                                        class="px-3 py-3 text-center text-[10px] font-semibold uppercase tracking-widest text-slate-500 w-16">
                                        Akhir</th>
                                    <th
                                        class="px-3 py-3 text-center text-[10px] font-semibold uppercase tracking-widest text-slate-500 w-16">
                                        Huruf</th>
                                    <th
                                        class="px-3 py-3 text-center text-[10px] font-semibold uppercase tracking-widest text-slate-500 w-16">
                                        Indx</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white text-slate-700">
                                @forelse($khsData as $index => $item)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-3 py-3 text-center text-slate-400 text-xs">{{ $index + 1 }}</td>
                                        <td
                                            class="px-3 py-3 text-left text-xs font-semibold text-maroon-700 uppercase whitespace-nowrap">
                                            {{ $item->kode_tampil }}
                                        </td>
                                        <td class="px-3 py-3 text-left">
                                            <span class="font-medium text-slate-800 block">{{ $item->nama_mk }}</span>
                                            @if ($item->jenis_mk === 'Spesial')
                                                <span class="block text-[10px] text-slate-400 italic mt-0.5">Bimbingan
                                                    terpadu</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-3 text-center font-medium">{{ $item->sks }}</td>

                                        @if ($item->jenis_mk === 'Spesial')
                                            <td colspan="3"
                                                class="px-3 py-3 text-center text-xs text-slate-400 italic hidden md:table-cell">
                                                Nilai Bimbingan
                                            </td>
                                        @else
                                            <td class="px-3 py-3 text-center text-amber-700 hidden md:table-cell">
                                                {{ number_format($item->total_tugas ?? 0, 2) }}</td>
                                            <td class="px-3 py-3 text-center text-blue-700 hidden md:table-cell">
                                                {{ number_format($item->total_uts ?? 0, 2) }}</td>
                                            <td class="px-3 py-3 text-center text-slate-600 hidden md:table-cell">
                                                {{ number_format($item->total_uas ?? 0, 2) }}</td>
                                        @endif

                                        <td
                                            class="px-3 py-3 text-center font-semibold tabular-nums whitespace-nowrap
                                        {{ $item->nilai_akhir_angka >= 80 ? 'text-emerald-600' : ($item->nilai_akhir_angka >= 60 ? 'text-amber-600' : 'text-rose-600') }}">
                                            {{ number_format($item->nilai_akhir_angka ?? 0, 2) }}
                                        </td>
                                        <td class="px-3 py-3 text-center font-medium whitespace-nowrap">
                                            {{ $item->nilai_huruf ?? '-' }}</td>
                                        <td class="px-3 py-3 text-center font-medium tabular-nums whitespace-nowrap">
                                            {{ number_format($item->nilai_indeks ?? 0, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="px-6 py-14 text-center text-slate-400">
                                            <svg class="w-10 h-10 mx-auto mb-2 text-slate-300" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <p class="text-sm">Belum ada data nilai semester ini.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-maroon-700 text-white">
                                <tr>
                                    <td colspan="3"
                                        class="px-3 py-3.5 text-right text-[10px] font-semibold uppercase tracking-widest opacity-75">
                                        Total SKS
                                    </td>
                                    <td class="px-3 py-3.5 text-center text-lg font-semibold tabular-nums">
                                        {{ $totalSKS }}
                                    </td>
                                    <td colspan="3"
                                        class="px-3 py-3.5 text-right text-[10px] font-semibold uppercase tracking-widest opacity-75 hidden md:table-cell">
                                        IPS
                                    </td>
                                    <td colspan="3"
                                        class="px-3 py-3.5 text-center text-xl font-semibold tabular-nums bg-maroon-800">
                                        {{ number_format($ipk, 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4 pt-2">
                <div class="text-xs text-slate-400 space-y-1 w-full sm:w-auto">
                    <p class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>Dicetak {{ now()->translatedFormat('d F Y, H:i') }} WIB</span>
                    </p>
                    <p class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" />
                        </svg>
                        <span>Oleh Admin: {{ auth()->user()->nama_lengkap }}</span>
                    </p>
                </div>

                <div class="text-right w-full sm:w-auto">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-8">
                        Ketua Prodi / Admin Akademik
                    </p>
                    <div class="w-36 h-px bg-slate-200 ml-auto mb-1"></div>
                    <p class="font-serif italic text-maroon-700 text-sm">Official Academic Transcript</p>
                    <p class="text-[10px] text-slate-400 mt-0.5">Digenerate secara sistem</p>
                </div>
            </div>

        </div>
    </div>
@endsection
