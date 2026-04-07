@extends('admin.layouts.app')

@section('title', 'Kelola Bimbingan - Maroon Sneat Style')
@section('page_title', 'Kelola Bimbingan')
@section('page_desc', 'Manajemen penugasan dosen pembimbing dan monitoring status bimbingan.')

@section('content')
    <div class="space-y-5 max-w-full">
        @if (session('success'))
            <div
                class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 shadow-sm animate-fade-in">
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        {{-- Filter Card --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft">
            <form method="GET" action="{{ route('admin.bimbingans.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
                    {{-- Search --}}
                    <div class="md:col-span-4">
                        <label
                            class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500 ml-1">Pencarian</label>
                        <div class="relative">
                            <input type="text" name="q" value="{{ $q }}"
                                placeholder="Cari mahasiswa, NIM, atau MK..."
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm transition focus:border-maroon-700 focus:bg-white focus:outline-none focus:ring-4 focus:ring-maroon-600/5" />
                        </div>
                    </div>

                    {{-- Dosen --}}
                    <div class="md:col-span-3">
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500 ml-1">Dosen
                            Pembimbing</label>
                        <select name="dosen_id" onchange="this.form.submit()"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2.5 text-sm transition focus:border-maroon-700 focus:bg-white focus:outline-none focus:ring-4 focus:ring-maroon-600/5">
                            <option value="">Semua Dosen</option>
                            @foreach ($dosens as $d)
                                <option value="{{ $d->id }}" {{ ($filterDosen ?? '') == $d->id ? 'selected' : '' }}>
                                    {{ $d->user->nama_lengkap }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Angkatan --}}
                    <div class="md:col-span-2">
                        <label
                            class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500 ml-1">Angkatan</label>
                        <select name="angkatan" onchange="this.form.submit()"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2.5 text-sm transition focus:border-maroon-700 focus:bg-white focus:outline-none focus:ring-4 focus:ring-maroon-600/5">
                            <option value="">Semua</option>
                            @foreach ($angkatans as $a)
                                <option value="{{ $a }}" {{ ($filterAngkatan ?? '') == $a ? 'selected' : '' }}>
                                    {{ $a }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-end gap-2 md:col-span-3">
                        <button type="submit"
                            class="flex-1 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800 transition shadow-sm">
                            Terapkan
                        </button>

                        @if ($q || ($filterDosen ?? '') || ($filterAngkatan ?? ''))
                            <a href="{{ route('admin.bimbingans.index') }}"
                                class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition"
                                title="Reset Filter">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </a>
                        @endif

                        <a href="{{ route('admin.bimbingans.create') }}"
                            class="flex h-[42px] items-center justify-center gap-2 rounded-xl bg-maroon-700 px-4 py-2 text-sm font-semibold text-white hover:bg-maroon-800 transition shadow-[0_10px_20px_rgba(127,29,29,0.2)]">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5">
                                <path d="M12 5v14M5 12h14" stroke-linecap="round" />
                            </svg>
                            <span class="hidden lg:inline">Tambah</span>
                        </a>
                    </div>
                </div>
            </form>
        </div>

        {{-- Table card --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-soft overflow-hidden">
            <div class="max-w-full overflow-x-auto">
                <table class="w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50/80">
                        <tr>
                            <th class="px-5 py-4 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500">No
                            </th>
                            <th class="px-5 py-4 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500">
                                Mahasiswa</th>
                            <th class="px-5 py-4 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500">
                                Dosen Pembimbing</th>
                            <th class="px-5 py-4 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500">
                                Mata Kuliah</th>
                            <th class="px-5 py-4 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500">
                                Semester</th>
                            <th
                                class="px-5 py-4 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500 text-center">
                                Status</th>
                            <th class="px-5 py-4 text-right text-[10px] font-bold uppercase tracking-wider text-slate-500">
                                Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($bimbingans as $item)
                            <tr class="group hover:bg-slate-50/80 transition-colors">
                                <td class="px-5 py-4 text-sm text-slate-500 font-medium">
                                    {{ ($bimbingans->currentPage() - 1) * $bimbingans->perPage() + $loop->iteration }}
                                </td>

                                <td class="px-5 py-4">
                                    <div class="flex flex-col">
                                        <span
                                            class="text-sm font-bold text-slate-900 group-hover:text-maroon-800 transition-colors">
                                            {{ $item->mahasiswa?->user?->nama_lengkap ?? '-' }}
                                        </span>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span
                                                class="text-xs text-slate-500 font-mono tracking-tighter">{{ $item->mahasiswa?->nim ?? '' }}</span>
                                            <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                                            <span class="text-[10px] font-bold text-slate-400 uppercase italic">Angk.
                                                {{ $item->mahasiswa?->angkatan }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-5 py-4 text-sm text-slate-700">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="h-7 w-7 rounded-lg bg-maroon-50 text-maroon-700 flex items-center justify-center text-[10px] font-bold">
                                            {{ substr($item->dosen?->user?->nama_lengkap, 0, 1) }}
                                        </div>
                                        <span class="whitespace-nowrap font-medium text-slate-700">
                                            {{ $item->dosen?->user?->nama_lengkap ?? '-' }}
                                        </span>
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <div class="flex flex-col max-w-[200px]">
                                        <span
                                            class="text-xs font-bold text-maroon-700">{{ $item->mataKuliah?->kode_mk ?? '-' }}</span>
                                        <span class="text-xs text-slate-600 truncate"
                                            title="{{ $item->mataKuliah?->nama_mk }}">
                                            {{ $item->mataKuliah?->nama_mk ?? '-' }}
                                        </span>
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                        <svg class="h-3 w-3 text-slate-400" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        {{ $item->semester?->nama_semester ?? '-' }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-center">
                                    @if ($item->status === 'approved')
                                        <span
                                            class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-emerald-700 ring-1 ring-emerald-700/20 shadow-sm">
                                            Approved
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-amber-700 ring-1 ring-amber-700/20 shadow-sm">
                                            Pending
                                        </span>
                                    @endif
                                </td>

                                <td class="px-5 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.bimbingans.edit', $item) }}"
                                            class="h-8 w-8 rounded-lg border border-slate-200 bg-white p-1.5 text-slate-600 hover:border-maroon-200 hover:bg-maroon-50 hover:text-maroon-700 transition-all shadow-sm flex items-center justify-center"
                                            title="Edit Penugasan">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2">
                                                <path d="M4 20h4l10.5-10.5a2 2 0 0 0 0-3L16.5 4.5a2 2 0 0 0-3 0L3 15v5Z"
                                                    stroke-linejoin="round" />
                                            </svg>
                                        </a>

                                        <button type="button"
                                            onclick="document.getElementById('hapus-bimbingan-{{ $item->id }}').showModal()"
                                            class="h-8 w-8 rounded-lg border border-rose-100 bg-rose-50 p-1.5 text-rose-600 hover:bg-rose-100 transition-all shadow-sm flex items-center justify-center"
                                            title="Hapus Penugasan">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2">
                                                <path
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                    stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </button>
                                    </div>

                                    {{-- Modal Hapus Tetap Sama --}}
                                    <dialog id="hapus-bimbingan-{{ $item->id }}"
                                        class="rounded-2xl p-0 backdrop:bg-slate-900/40">
                                        <div class="w-full max-w-md bg-white p-6 shadow-2xl">
                                            <div class="text-center">
                                                <div
                                                    class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-rose-50 text-rose-600">
                                                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                    </svg>
                                                </div>
                                                <h3 class="text-lg font-bold text-slate-900">Konfirmasi Hapus</h3>
                                                <p class="mt-2 text-sm text-slate-500">
                                                    Penugasan bimbingan untuk
                                                    <b>{{ $item->mahasiswa?->user?->nama_lengkap }}</b> akan dihapus secara
                                                    permanen.
                                                </p>
                                            </div>
                                            <div class="mt-6 flex gap-3">
                                                <button type="button"
                                                    onclick="document.getElementById('hapus-bimbingan-{{ $item->id }}').close()"
                                                    class="flex-1 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                                                    Batal
                                                </button>
                                                <form method="POST"
                                                    action="{{ route('admin.bimbingans.destroy', $item) }}"
                                                    class="flex-1">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                        class="w-full rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-700 shadow-md">
                                                        Ya, Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </dialog>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-20 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="mb-3 rounded-full bg-slate-50 p-4">
                                            <svg class="h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                            </svg>
                                        </div>
                                        <p class="text-sm font-medium text-slate-500">Tidak ada data bimbingan ditemukan.
                                        </p>
                                        <p class="text-xs text-slate-400">Coba ubah filter atau tambahkan penugasan baru.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $bimbingans->links() }}
        </div>
    </div>
@endsection
