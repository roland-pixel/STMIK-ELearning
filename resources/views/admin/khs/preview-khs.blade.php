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
                {{-- Info Mahasiswa: Box Terang dengan Aksen Maroon --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 bg-slate-50 p-7 rounded-2xl border border-slate-100">
                    <div>
                        <h4 class="text-sm font-black uppercase tracking-widest text-maroon-600 mb-4">Informasi Institusi
                        </h4>
                        <div class="space-y-3">
                            <p class="text-lg font-bold text-slate-800">{{ config('app.name', 'Universitas') }}</p>
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
                            <p><span class="inline-block w-24 font-medium">Periode</span>: <span
                                    class="font-bold text-slate-900">{{ $semester->periode }}</span></p>
                            <p class="mt-4"><span class="inline-block w-24 font-medium">Tgl. Cetak</span>: <span
                                    class="font-bold text-slate-900">{{ $tanggal_cetak ?? now()->format('d F Y') }}</span>
                            </p>
                        </div>
                    </div>
                </div>

                {{-- 🔥 TOTAL KOMPONEN NILAI --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div
                        class="bg-gradient-to-r from-amber-500/5 to-amber-600/5 p-8 rounded-2xl border border-amber-200/50 text-center hover:border-amber-300 transition-all">
                        <div class="text-4xl mb-3">📝</div>
                        <p class="text-sm font-medium text-amber-700 uppercase tracking-wide mb-2">Total Nilai Tugas</p>
                        <p class="text-3xl font-black text-slate-900">{{ number_format($totalTugas ?? 0, 2) }}</p>
                    </div>

                    <div
                        class="bg-gradient-to-r from-blue-500/5 to-blue-600/5 p-8 rounded-2xl border border-blue-200/50 text-center hover:border-blue-300 transition-all">
                        <div class="text-4xl mb-3">📚</div>
                        <p class="text-sm font-medium text-blue-700 uppercase tracking-wide mb-2">Total Nilai UTS</p>
                        <p class="text-3xl font-black text-slate-900">{{ number_format($totalUTS ?? 0, 2) }}</p>
                    </div>

                    <div
                        class="bg-gradient-to-r from-purple-500/5 to-purple-600/5 p-8 rounded-2xl border border-purple-200/50 text-center hover:border-purple-300 transition-all">
                        <div class="text-4xl mb-3">🎓</div>
                        <p class="text-sm font-medium text-purple-700 uppercase tracking-wide mb-2">Total Nilai UAS</p>
                        <p class="text-3xl font-black text-slate-900">{{ number_format($totalUAS ?? 0, 2) }}</p>
                    </div>
                </div>

                {{-- Tabel Nilai: TAMBAH KOLOM TUGAS/UTS/UAS --}}
                <div class="overflow-hidden rounded-2xl border border-slate-200 shadow-sm">
                    <table class="w-full border-collapse">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th
                                    class="px-4 py-4 text-left text-xs font-black uppercase tracking-widest text-slate-500 w-12">
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
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($khsData as $index => $item)
                                <tr class="hover:bg-maroon-50/50 transition-colors">
                                    <td class="px-4 py-4 text-center text-sm font-bold text-slate-400">{{ $index + 1 }}
                                    </td>
                                    <td class="px-4 py-4 text-sm font-black text-maroon-600">{{ $item->kode_mk }}</td>
                                    <td class="px-4 py-4 text-sm font-bold text-slate-700">{{ $item->nama_mk }}</td>
                                    <td class="px-4 py-4 text-center text-sm font-black text-slate-800">{{ $item->sks }}
                                    </td>

                                    {{-- 🔥 KOLOM NILAI KOMPONEN --}}
                                    <td class="px-4 py-4 text-center text-sm font-medium text-amber-700">
                                        {{ number_format($item->total_tugas ?? 0, 2) }}</td>
                                    <td class="px-4 py-4 text-center text-sm font-medium text-blue-700">
                                        {{ number_format($item->total_uts ?? 0, 2) }}</td>
                                    <td class="px-4 py-4 text-center text-sm font-medium text-purple-700">
                                        {{ number_format($item->total_uas ?? 0, 2) }}</td>

                                    <td class="px-4 py-4 text-center">
                                        <span
                                            class="text-sm font-black {{ $item->nilai_akhir_angka >= 80 ? 'text-emerald-600' : ($item->nilai_akhir_angka >= 60 ? 'text-amber-500' : 'text-rose-600') }}">
                                            {{ number_format($item->nilai_akhir_angka ?? 0, 2) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-center text-sm font-black text-slate-800">
                                        {{ $item->nilai_huruf ?? '-' }}</td>
                                    <td class="px-4 py-4 text-center text-sm font-black text-slate-800">
                                        {{ number_format($item->nilai_indeks ?? 0, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center gap-2">
                                            <span class="text-4xl text-slate-300">📄</span>
                                            <p class="text-slate-500 font-medium">Belum ada data nilai untuk semester ini
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        {{-- Footer Tabel: Maroon Solid --}}
                        <tfoot class="bg-maroon-700 text-white">
                            <tr>
                                <th colspan="3"
                                    class="px-4 py-5 text-right text-sm font-bold uppercase tracking-widest opacity-90">
                                    Total SKS Terhitung</th>
                                <th class="px-4 py-5 text-center text-xl font-black">{{ $totalSKS }}</th>
                                <th colspan="2"
                                    class="px-4 py-5 text-right text-sm font-bold uppercase tracking-widest opacity-90">IPK
                                    Semester:</th>
                                <th class="px-4 py-5 text-center text-2xl font-black bg-white/10" colspan="4">
                                    {{ number_format($ipk, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Footer: Tanda Tangan & Keterangan --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center pt-4">
                    <div class="text-sm text-slate-500 italic">
                        <p><strong>Dicetak oleh:</strong> {{ auth()->user()->nama_lengkap }}</p>
                        <p>{{ now()->format('d F Y H:i') }}</p>
                    </div>
                    <div class="text-right flex flex-col items-end">
                        <p class="font-bold text-slate-500 text-sm uppercase tracking-widest mb-10">Hormat Kami, Admin
                            Akademik</p>
                        <div class="h-0.5 w-48 bg-slate-100 mb-2"></div>
                        <p class="font-black text-xl text-maroon-700 tracking-tighter italic">Official Academic Transcript
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
