@extends('admin.layouts.app')

@section('title', 'Input Nilai Massal - ' . $kelas->nama_kelas)
@section('page_title', 'Input Nilai')
@section('page_desc', $kelas->mataKuliah->nama_mk . ' (' . $kelas->nama_kelas . ')')

@section('content')
    <div class="max-w-4xl mx-auto space-y-4">
        <a href="{{ route('admin.input_nilai_manual.index') }}"
            class="inline-flex items-center text-sm font-semibold text-maroon-700 hover:text-maroon-800">
            <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Kembali ke Daftar Kelas
        </a>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-soft overflow-hidden">
            <div class="p-6 bg-maroon-700 text-white">
                <h2 class="text-lg font-bold">Entry Nilai Kolektif</h2>
                <p class="text-maroon-100 text-sm">Pastikan nilai yang dimasukkan sudah sesuai dengan form nilai manual dari
                    dosen.</p>
            </div>

            <form action="{{ route('admin.input_nilai_manual.store', $kelas->id) }}" method="POST">
                @csrf
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead
                            class="bg-slate-50  sticky top-0 z-10 text-ink-600 uppercase text-[11px] font-bold tracking-wider">
                            <tr>
                                <th class="px-6 py-4">Mahasiswa</th>
                                <th class="px-2 py-4 text-center" width="120">Tugas</th>
                                <th class="px-2 py-4 text-center" width="120">UTS</th>
                                <th class="px-2 py-4 text-center" width="120">UAS</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            {{-- Mengambil data melalui relasi anggotaKelases yang sudah kita load di Controller --}}
                            @foreach ($kelas->anggotaKelases as $anggota)
                                @php $mhs = $anggota->mahasiswa; @endphp
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-ink-900">{{ $mhs->user->nama_lengkap }}</div>
                                        <div class="text-xs text-ink-500">{{ $mhs->nim }}</div>
                                    </td>
                                    {{-- Input Tugas --}}
                                    <td class="px-2 py-4 text-center">
                                        <input type="number" name="data_nilai[{{ $mhs->id }}][tugas]"
                                            class="w-20 mx-auto block rounded-lg border border-slate-200 px-2 py-2 text-center focus:border-maroon-700 transition"
                                            placeholder="0" step="0.01" min="0" max="100" required>
                                    </td>
                                    {{-- Input UTS --}}
                                    <td class="px-2 py-4 text-center">
                                        <input type="number" name="data_nilai[{{ $mhs->id }}][uts]"
                                            class="w-20 mx-auto block rounded-lg border border-slate-200 px-2 py-2 text-center focus:border-maroon-700 transition"
                                            placeholder="0" step="0.01" min="0" max="100" required>
                                    </td>
                                    {{-- Input UAS --}}
                                    <td class="px-2 py-4 text-center">
                                        <input type="number" name="data_nilai[{{ $mhs->id }}][uas]"
                                            class="w-20 mx-auto block rounded-lg border border-slate-200 px-2 py-2 text-center focus:border-maroon-700 transition"
                                            placeholder="0" step="0.01" min="0" max="100" required>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-6 bg-slate-50 border-t border-slate-100 flex justify-end items-center gap-4">
                    <button type="submit"
                        class="inline-flex justify-center rounded-xl bg-maroon-700 px-6 py-2.5 text-sm font-bold text-white hover:bg-maroon-800 transition shadow-lg">
                        Simpan & Kalkulasi
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
