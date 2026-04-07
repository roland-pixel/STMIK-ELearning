@extends('admin.layouts.app')

@section('title', 'Kelola Mata Kuliah - Maroon Sneat Style')
@section('page_title', 'Kelola Mata Kuliah')
@section('page_desc', 'Tambah, cari, ubah, dan hapus mata kuliah')

@section('content')
    <div class="space-y-4 max-w-full">
        @if (session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        {{-- Toolbar & Filters --}}
        <div class="flex flex-col gap-4">
            <form method="GET" action="{{ route('admin.mata_kuliahs.index') }}"
                class="grid grid-cols-1 gap-3 sm:flex sm:items-end">

                {{-- Search Input --}}
                <div class="flex flex-col gap-1.5 flex-1">
                    <label class="text-xs font-medium text-slate-500 ml-1">Pencarian</label>
                    <input type="text" name="q" value="{{ $q }}" placeholder="Cari kode atau nama..."
                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm
                               focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition" />
                </div>

                {{-- Filter Jenis MK --}}
                <div class="flex flex-col gap-1.5 w-full sm:w-40">
                    <label class="text-xs font-medium text-slate-500 ml-1">Jenis</label>
                    <select name="jenis" onchange="this.form.submit()"
                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm
                               focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition">
                        <option value="">Semua Jenis</option>
                        <option value="Umum" {{ request('jenis') == 'Umum' ? 'selected' : '' }}>Umum</option>
                        <option value="Spesial" {{ request('jenis') == 'Spesial' ? 'selected' : '' }}>Spesial</option>
                    </select>
                </div>

                {{-- Filter Kategori MK --}}
                <div class="flex flex-col gap-1.5 w-full sm:w-56">
                    <label class="text-xs font-medium text-slate-500 ml-1">Kategori</label>
                    <select name="kategori" onchange="this.form.submit()"
                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm
                               focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition">
                        <option value="">Semua Kategori</option>
                        @foreach (['KPP', 'KIT', 'KAB', 'KPB', 'KBB'] as $kat)
                            <option value="{{ $kat }}" {{ request('kategori') == $kat ? 'selected' : '' }}>
                                {{ $kat }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Buttons --}}
                <div class="flex gap-2">
                    <button type="submit"
                        class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 transition h-[38px]">
                        Filter
                    </button>
                    @if (request('q') || request('jenis') || request('kategori'))
                        <a href="{{ route('admin.mata_kuliahs.index') }}"
                            class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-200 transition h-[38px] grid place-items-center">
                            Reset
                        </a>
                    @endif
                </div>

                <div class="sm:ml-auto">
                    <a href="{{ route('admin.mata_kuliahs.create') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-maroon-700 px-4 py-2 text-sm font-semibold text-white
                               hover:bg-maroon-800 transition shadow-[0_10px_22px_rgba(127,29,29,0.18)] whitespace-nowrap h-[38px]">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                            <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        </svg>
                        Tambah MK
                    </a>
                </div>
            </form>
        </div>

        {{-- Table card --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-soft overflow-hidden">
            <div class="max-w-full overflow-x-auto">
                <table class="min-w-[850px] w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 w-16">
                                #</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                                Kode</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                                Nama</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 w-20">
                                SKS</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                                Jenis</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                                Kategori</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-600">
                                Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-200">
                        @forelse($mataKuliahs as $item)
                            <tr class="hover:bg-maroon-50/40 transition-colors text-slate-700">
                                <td class="px-4 py-3 text-sm">
                                    {{ ($mataKuliahs->currentPage() - 1) * $mataKuliahs->perPage() + $loop->iteration }}
                                </td>
                                <td class="px-4 py-3 text-sm font-semibold text-slate-900 whitespace-nowrap">
                                    {{ $item->kode_mk }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ $item->nama_mk }}
                                </td>
                                <td class="px-4 py-3 text-sm whitespace-nowrap">
                                    {{ $item->sks }}
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-bold text-slate-600 ring-1 ring-slate-200 uppercase">
                                        {{ $item->jenis_mk }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex items-center rounded-full bg-maroon-600/10 px-2.5 py-0.5 text-[11px] font-bold text-maroon-800 ring-1 ring-maroon-700/10">
                                        {{ $item->kategori_mk ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('admin.mata_kuliahs.edit', $item) }}"
                                            class="p-1.5 rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-maroon-700 transition">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>

                                        <button type="button"
                                            onclick="document.getElementById('hapus-mk-{{ $item->id }}').showModal()"
                                            class="p-1.5 rounded-lg border border-rose-100 text-rose-500 hover:bg-rose-50 transition">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>

                                    {{-- Modal Hapus (Tetap sama seperti sebelumnya) --}}
                                    <dialog id="hapus-mk-{{ $item->id }}"
                                        class="rounded-2xl p-0 backdrop:bg-black/40 overflow-hidden shadow-2xl">
                                        <div class="w-full max-w-sm bg-white p-6 text-left whitespace-normal">
                                            <h3 class="text-lg font-bold text-slate-900">Konfirmasi Hapus</h3>
                                            <p class="mt-2 text-sm text-slate-600">Yakin ingin menghapus MK
                                                <strong>{{ $item->nama_mk }}</strong>? Data yang dihapus tidak bisa
                                                dikembalikan.</p>
                                            <div class="mt-6 flex justify-end gap-3">
                                                <button type="button"
                                                    onclick="document.getElementById('hapus-mk-{{ $item->id }}').close()"
                                                    class="px-4 py-2 text-sm font-medium text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition">Batal</button>
                                                <form method="POST"
                                                    action="{{ route('admin.mata_kuliahs.destroy', $item) }}">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                        class="px-4 py-2 text-sm font-bold text-white bg-rose-600 rounded-xl hover:bg-rose-700 transition">Hapus
                                                        Data</button>
                                                </form>
                                            </div>
                                        </div>
                                    </dialog>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center">
                                    <div class="flex flex-col items-center gap-2 text-slate-400">
                                        <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M9.172 9.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span class="text-sm">Tidak menemukan mata kuliah yang dicari.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $mataKuliahs->appends(request()->query())->links() }}
        </div>
    </div>
@endsection
    