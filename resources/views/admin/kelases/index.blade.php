@extends('admin.layouts.app')

@section('title', 'Kelola Kelas - Maroon Sneat Style')
@section('page_title', 'Kelola Kelas')
@section('page_desc', 'Atur kelas, dosen pengampu, mata kuliah, dan semester')

@section('content')
    <div class="space-y-4 max-w-full">
        @if (session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        {{-- Toolbar --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <form method="GET" class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                <input type="text" name="q" value="{{ $q }}"
                    placeholder="Cari kelas/kode gabung/dosen/mk/semester..."
                    class="w-full sm:w-96 rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm
               focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition" />

                <select name="semester_status" onchange="this.form.submit()"
                    class="w-full sm:w-52 rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm
               focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition">
                    <option value="active" {{ ($semesterStatus ?? 'active') === 'active' ? 'selected' : '' }}>Semester Aktif
                    </option>
                    <option value="inactive" {{ ($semesterStatus ?? 'active') === 'inactive' ? 'selected' : '' }}>Semester
                        Tidak Aktif</option>
                    <option value="all" {{ ($semesterStatus ?? 'active') === 'all' ? 'selected' : '' }}>Semua</option>
                </select>


            </form>


            <a href="{{ route('admin.kelases.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-maroon-700 px-4 py-2 text-sm font-semibold text-white
                       hover:bg-maroon-800 transition shadow-[0_10px_22px_rgba(127,29,29,0.18)]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                    <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                </svg>
                Tambah Kelas
            </a>
        </div>

        {{-- Table card --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-soft">
            <div class="max-w-full min-w-0 overflow-x-auto">
                <table class="min-w-[1200px] w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50 sticky top-0 z-10">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">#
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                                Nama Kelas</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                                Dosen</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                                Mata Kuliah</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                                Semester</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                                Status
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                                Kode Gabung</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-600">
                                Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-200">
                        @forelse($kelases as $item)
                            <tr class="hover:bg-maroon-50/40 transition-colors">
                                <td class="px-4 py-3 text-sm text-slate-700">
                                    {{ ($kelases->currentPage() - 1) * $kelases->perPage() + $loop->iteration }}
                                </td>

                                <td class="px-4 py-3 text-sm font-semibold text-slate-900 whitespace-nowrap">
                                    {{ $item->nama_kelas }}
                                </td>

                                <td class="px-4 py-3 text-sm text-slate-700 whitespace-nowrap">
                                    {{ $item->dosen?->user?->nama_lengkap ?? '-' }}
                                </td>

                                <td class="px-4 py-3 text-sm text-slate-700">
                                    <span class="font-semibold text-slate-900 whitespace-nowrap">
                                        {{ $item->mataKuliah?->kode_mk ?? '-' }}
                                    </span>
                                    <span class="text-slate-400">—</span>
                                    {{ $item->mataKuliah?->nama_mk ?? '-' }}
                                </td>

                                <td class="px-4 py-3 text-sm text-slate-700 whitespace-nowrap">
                                    {{ $item->semester?->nama_semester ?? '-' }}
                                </td>

                                <td class="px-4 py-3 text-sm whitespace-nowrap">
                                    @php
                                        $status = $item->semester?->status_aktif;
                                        $isActive = $status === 'active';
                                    @endphp

                                    @if ($status)
                                        <span
                                            class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 whitespace-nowrap
                                            {{ $isActive
                                                ? 'bg-emerald-600/10 text-emerald-800 ring-emerald-700/10'
                                                : 'bg-slate-600/10 text-slate-700 ring-slate-700/10' }}">
                                            {{ $isActive ? 'Aktif' : 'Tidak Aktif' }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 text-sm">-</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex items-center rounded-full bg-maroon-600/10 px-2.5 py-1 text-xs font-semibold text-maroon-800
                                                ring-1 ring-maroon-700/10 whitespace-nowrap">
                                        {{ $item->kode_gabung }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex items-center gap-2 whitespace-nowrap">
                                        {{-- Edit --}}
                                        <a href="{{ route('admin.kelases.edit', $item) }}"
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
                                            onclick="document.getElementById('hapus-kelas-{{ $item->id }}').showModal()"
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
                                    <dialog id="hapus-kelas-{{ $item->id }}"
                                        class="rounded-2xl p-0 backdrop:bg-black/30">
                                        <div class="w-full max-w-md bg-white rounded-2xl border border-slate-200 p-5">
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
                                                    <h3 class="text-base font-semibold text-slate-900">Hapus Kelas?</h3>
                                                    <p class="mt-1 text-sm text-slate-600">
                                                        Kamu akan menghapus kelas:
                                                        <span
                                                            class="font-medium text-slate-900">{{ $item->nama_kelas }}</span>.
                                                        Tindakan ini tidak bisa dibatalkan.
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="mt-5 flex justify-end gap-2">
                                                <button type="button"
                                                    onclick="document.getElementById('hapus-kelas-{{ $item->id }}').close()"
                                                    class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm hover:bg-slate-50 transition">
                                                    Batal
                                                </button>

                                                <form method="POST" action="{{ route('admin.kelases.destroy', $item) }}">
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
                                    Belum ada data kelas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            {{ $kelases->links() }}
        </div>
    </div>
@endsection
