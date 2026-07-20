@extends('admin.layouts.app')

@section('title', 'Kelola Mahasiswa - Maroon Sneat Style')
@section('page_title', 'Kelola Mahasiswa')
@section('page_desc', 'Tambah, cari, ubah, dan hapus mahasiswa')

@section('content')
    <div class="space-y-4 max-w-full">
        @if (session('success'))
            <div class="mb-4 rounded-lg bg-green-100 px-4 py-3 text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 rounded-lg bg-red-100 px-4 py-3 text-red-700">
                {{ session('error') }}
            </div>
        @endif

        {{-- Toolbar & Filters --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <form method="GET" action="{{ route('admin.mahasiswas.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                    {{-- Search Global --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wider text-slate-500 ml-1">Pencarian</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </span>
                            <input type="text" name="q" value="{{ $q }}"
                                placeholder="Nama, NIM, atau Email..."
                                class="w-full rounded-xl border border-slate-300 bg-white pl-10 pr-3 py-2 text-sm focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition" />
                        </div>
                    </div>

                    {{-- Filter Jurusan --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wider text-slate-500 ml-1">Jurusan</label>
                        <select name="jurusan_id" onchange="this.form.submit()"
                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition appearance-none cursor-pointer">
                            <option value="">Semua Jurusan</option>
                            @foreach ($list_jurusan as $j)
                                <option value="{{ $j->id }}" {{ $jurusan_id == $j->id ? 'selected' : '' }}>
                                    {{ $j->nama_jurusan }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Angkatan --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wider text-slate-500 ml-1">Angkatan</label>
                        <select name="angkatan" onchange="this.form.submit()"
                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition appearance-none cursor-pointer">
                            <option value="">Semua Angkatan</option>
                            @foreach ($list_angkatan as $a)
                                <option value="{{ $a->angkatan }}" {{ $angkatan == $a->angkatan ? 'selected' : '' }}>
                                    {{ $a->angkatan }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Status --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wider text-slate-500 ml-1">Status
                            Mahasiswa</label>
                        <select name="status" onchange="this.form.submit()"
                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition appearance-none cursor-pointer">
                            <option value="">Semua Status</option>
                            <option value="aktif" {{ $status == 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="lulus" {{ $status == 'lulus' ? 'selected' : '' }}>Lulus</option>
                        </select>
                    </div>
                </div>

                <div
                    class="flex flex-col gap-3 pt-2 sm:flex-row sm:items-center sm:justify-between border-t border-slate-100 mt-2">
                    <div class="flex items-center gap-2">
                        <button type="submit"
                            class="rounded-xl bg-slate-900 px-6 py-2 text-sm font-medium text-white hover:bg-slate-800 transition">
                            Terapkan Filter
                        </button>
                        @if ($q || $jurusan_id || $angkatan || $status)
                            <a href="{{ route('admin.mahasiswas.index') }}"
                                class="text-sm text-rose-600 hover:underline font-medium">
                                Reset Filter
                            </a>
                        @endif
                    </div>

                    <a href="{{ route('admin.mahasiswas.create') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-maroon-700 px-4 py-2 text-sm font-semibold text-white
                       hover:bg-maroon-800 transition shadow-[0_10px_22px_rgba(127,29,29,0.18)]">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                            <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        </svg>
                        Tambah Mahasiswa
                    </a>
                </div>
            </form>
        </div>

        {{-- Table card --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-soft">
            <div class="max-w-full min-w-0 overflow-x-auto">
                <table class="min-w-[1050px] w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50 sticky top-0 z-10">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">#
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                                Nama</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                                Email</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">NIM
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                                Jurusan</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                                Angkatan</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                                Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-600">
                                Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-200">
                        @forelse($mahasiswas as $item)
                            <tr class="hover:bg-maroon-50/40 transition-colors">
                                <td class="px-4 py-3 text-sm text-slate-700">
                                    {{ ($mahasiswas->currentPage() - 1) * $mahasiswas->perPage() + $loop->iteration }}
                                </td>

                                <td class="px-4 py-3 text-sm font-semibold text-slate-900 whitespace-nowrap">
                                    {{ $item->user->nama_lengkap ?? '-' }}
                                </td>

                                <td class="px-4 py-3 text-sm text-slate-700 whitespace-nowrap">
                                    {{ $item->user->email ?? '-' }}
                                </td>

                                <td class="px-4 py-3 text-sm text-slate-700 whitespace-nowrap">
                                    {{ $item->nim }}
                                </td>

                                <td class="px-4 py-3 text-sm text-slate-700">
                                    <span class="font-semibold text-slate-900">{{ $item->jurusan?->kode_jurusan }}</span>
                                    <span class="text-slate-400">—</span>
                                    {{ $item->jurusan?->nama_jurusan }}
                                </td>

                                <td class="px-4 py-3 text-sm text-slate-700 whitespace-nowrap">
                                    {{ $item->angkatan }}
                                </td>

                                {{-- Kolom Status Baru --}}
                                <td class="px-4 py-3 text-sm whitespace-nowrap">
                                    @if (htmlentities($item->status) == 'aktif')
                                        <span
                                            class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 border border-emerald-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            Aktif
                                        </span>
                                    @elseif(htmlentities($item->status) == 'lulus')
                                        <span
                                            class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 border border-blue-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                                            Lulus
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 border border-slate-200">
                                            {{ $item->status ?? '-' }}
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex items-center gap-2 whitespace-nowrap">
                                        {{-- Edit --}}
                                        <a href="{{ route('admin.mahasiswas.edit', $item) }}"
                                            class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-3 py-1.5 text-sm
                                                   hover:bg-slate-50 transition">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                                <path d="M4 20h4l10.5-10.5a2 2 0 0 0 0-3L16.5 4.5a2 2 0 0 0-3 0L3 15v5Z"
                                                    stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" />
                                            </svg>
                                            Edit
                                        </a>

                                        {{-- Hapus --}}
                                        <button type="button"
                                            onclick="document.getElementById('hapus-mhs-{{ $item->id }}').showModal()"
                                            class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-3 py-1.5 text-sm
                                                   text-rose-700 hover:bg-rose-100 transition">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                                <path d="M4 7h16" stroke="currentColor" stroke-width="2"
                                                    stroke-linecap="round" />
                                                <path d="M10 11v6M14 11v6" stroke="currentColor" stroke-width="2"
                                                    stroke-linecap="round" />
                                                <path d="M6 7l1 14h10l1-14" stroke="currentColor" stroke-width="1.8"
                                                    stroke-linejoin="round" />
                                                <path d="M9 7V4h6v3" stroke="currentColor" stroke-width="1.8"
                                                    stroke-linejoin="round" />
                                            </svg>
                                            Hapus
                                        </button>
                                    </div>

                                    {{-- Modal konfirmasi --}}
                                    <dialog id="hapus-mhs-{{ $item->id }}"
                                        class="fixed inset-0 z-50 m-auto backdrop:bg-black/40 rounded-2xl p-0 open:flex open:items-center open:justify-center border-0 bg-transparent shadow-none">
                                        <div
                                            class="w-full max-w-md bg-white rounded-2xl border border-slate-200 p-5 shadow-xl transition-all">
                                            <div class="flex items-start gap-3">
                                                <div
                                                    class="h-10 w-10 rounded-2xl bg-rose-50 text-rose-700 grid place-items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                        viewBox="0 0 24 24" fill="currentColor">
                                                        <path
                                                            d="M9 3V4H4V6H5V19C5 20.1 5.9 21 7 21H17C18.1 21 19 20.1 19 19V6H20V4H15V3H9ZM7 6H17V19H7V6ZM9 8V17H11V8H9ZM13 8V17H15V8H13Z" />
                                                    </svg>
                                                </div>

                                                <div class="flex-1">
                                                    <h3 class="text-base font-semibold text-slate-900">Hapus Mahasiswa?
                                                    </h3>
                                                    <p class="mt-1 text-sm text-slate-600">
                                                        Menghapus mahasiswa ini juga akan menghapus akun user terkait.
                                                        <br>
                                                        <span class="font-medium text-slate-900">
                                                            {{ $item->user->nama_lengkap ?? '-' }}
                                                        </span>
                                                        ({{ $item->nim }})
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="mt-5 flex justify-end gap-2">
                                                <button type="button"
                                                    onclick="document.getElementById('hapus-mhs-{{ $item->id }}').close()"
                                                    class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm hover:bg-slate-50 transition">
                                                    Batal
                                                </button>

                                                <form method="POST"
                                                    action="{{ route('admin.mahasiswas.destroy', $item) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="rounded-xl bg-maroon-700 px-4 py-2 text-sm font-semibold text-white hover:bg-maroon-800 transition">
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
                                <td colspan="8" class="px-4 py-10 text-center text-sm text-slate-500">
                                    Belum ada data mahasiswa.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            {{ $mahasiswas->links() }}
        </div>
    </div>
@endsection
