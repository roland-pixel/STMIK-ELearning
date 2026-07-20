@extends('admin.layouts.app')

@section('title', 'Kelola Kelas - Maroon Sneat Style')
@section('page_title', 'Kelola Kelas')
@section('page_desc', 'Atur kelas, dosen pengampu, mata kuliah, dan semester')

@section('content')
    <div class="space-y-4 max-w-full">

        @if (session('success'))
            <div class="rounded-lg bg-green-100 px-4 py-3 text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-lg bg-red-100 px-4 py-3 text-red-700">
                {{ session('error') }}
            </div>
        @endif


        {{-- toolbar --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

            <form method="GET" class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">

                <input type="text" name="q" value="{{ $q }}" placeholder="Cari kelas..."
                    class="w-full sm:w-96 rounded-xl border border-slate-300 px-3 py-2 text-sm">

                <select name="semester_status" onchange="this.form.submit()"
                    class="rounded-xl border border-slate-300 px-3 py-2 text-sm">

                    <option value="active" {{ ($semesterStatus ?? 'active') == 'active' ? 'selected' : '' }}>
                        Semester Aktif
                    </option>

                    <option value="inactive" {{ ($semesterStatus ?? 'active') == 'inactive' ? 'selected' : '' }}>
                        Semester Tidak Aktif
                    </option>

                    <option value="all" {{ ($semesterStatus ?? 'active') == 'all' ? 'selected' : '' }}>
                        Semua
                    </option>

                </select>

            </form>


            <a href="{{ route('admin.kelases.create') }}"
                class="inline-flex items-center gap-2 rounded-xl bg-maroon-700 px-4 py-2 text-sm font-semibold text-white">

                Tambah Kelas

            </a>

        </div>


        {{-- table --}}
        <div class="rounded-2xl border border-slate-200 bg-white overflow-x-auto">

            <table class="min-w-[1200px] w-full divide-y divide-slate-200">

                <thead class="bg-slate-50">

                    <tr>

                        <th class="px-4 py-3 text-left text-xs">#</th>
                        <th class="px-4 py-3 text-left text-xs">Nama</th>
                        <th class="px-4 py-3 text-left text-xs">Dosen</th>
                        <th class="px-4 py-3 text-left text-xs">Mata Kuliah</th>
                        <th class="px-4 py-3 text-left text-xs">Semester</th>
                        <th class="px-4 py-3 text-left text-xs">Kode Gabung</th>
                        <th class="px-4 py-3 text-left text-xs">Status</th>
                        <th class="px-4 py-3 text-left text-xs">Kelas</th>
                        <th class="px-4 py-3 text-right text-xs">Aksi</th>

                    </tr>

                </thead>


                <tbody class="divide-y divide-slate-200">

                    @forelse($kelases as $item)
                        @php
                            $canDuplicate =
                                !$item->anggotaKelases()->exists() &&
                                !$item->materis()->exists() &&
                                !$item->penilaians()->exists();
                        @endphp

                        <tr class="hover:bg-slate-50">

                            <td class="px-4 py-3 text-sm">
                                {{ ($kelases->currentPage() - 1) * $kelases->perPage() + $loop->iteration }}
                            </td>


                            <td class="px-4 py-3 font-semibold">
                                {{ $item->nama_kelas }}
                            </td>


                            <td class="px-4 py-3">
                                {{ $item->dosen?->user?->nama_lengkap }}
                            </td>


                            <td class="px-4 py-3">
                                {{ $item->mataKuliah?->nama_mk }}
                            </td>


                            <td class="px-4 py-3">
                                {{ $item->semester?->nama_semester }}
                            </td>

                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex items-center rounded-full 
                                    bg-red-50 px-2.5 py-1 text-xs font-semibold 
                                    text-red-700 ring-1 ring-red-200">
                                    {{ $item->kode_gabung }}
                                </span>
                            </td>


                            <td class="px-4 py-3">

                                @if ($item->semester?->status_aktif == 'active')
                                    <span class="text-green-600 font-semibold">
                                        Aktif
                                    </span>
                                @else
                                    <span class="text-slate-500">
                                        Tidak Aktif
                                    </span>
                                @endif

                            </td>


                            <td class="px-4 py-3">

                                @if ($canDuplicate)
                                    <span class="text-xs px-2 py-1 rounded bg-green-100 text-green-700">
                                        Kosong
                                    </span>
                                @else
                                    <span class="text-xs px-2 py-1 rounded bg-slate-100 text-slate-700">
                                        Terisi
                                    </span>
                                @endif

                            </td>



                            <td class="px-4 py-3 text-right">

                                <div class="inline-flex gap-2">


                                    {{-- edit --}}
                                    <a href="{{ route('admin.kelases.edit', $item) }}"
                                        class="rounded-xl border px-3 py-1.5 text-sm">

                                        Edit

                                    </a>



                                    {{-- copy --}}
                                    <form method="POST" action="{{ route('admin.kelases.duplicate', $item) }}">

                                        @csrf

                                        <button type="submit" {{ !$canDuplicate ? 'disabled' : '' }}
                                            class="rounded-xl px-3 py-1.5 text-sm
                                            
                                            {{ $canDuplicate ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-500 cursor-not-allowed' }}">

                                            Copy

                                        </button>

                                    </form>



                                    {{-- delete --}}
                                    <button type="button"
                                        onclick="document.getElementById('hapus-kelas-{{ $item->id }}').showModal()"
                                        class="rounded-xl bg-rose-50 text-rose-700 px-3 py-1.5 text-sm">

                                        Hapus

                                    </button>

                                </div>

                            </td>

                        </tr>



                        {{-- modal delete --}}
                        <dialog id="hapus-kelas-{{ $item->id }}"
                            class="fixed inset-0 z-50 m-auto backdrop:bg-black/40 rounded-2xl p-0 open:flex open:items-center open:justify-center border-0 bg-transparent shadow-none">

                            <div
                                class="w-full max-w-md bg-white rounded-2xl border border-slate-200 p-5 shadow-xl transition-all">

                                <h3 class="font-semibold text-lg text-slate-900">
                                    Hapus kelas?
                                </h3>

                                <p class="mt-2 text-sm text-slate-600">
                                    Kamu akan menghapus kelas: <span
                                        class="font-medium text-slate-900">{{ $item->nama_kelas }}</span>
                                </p>

                                <div class="mt-5 flex justify-end gap-2">

                                    <button type="button"
                                        onclick="document.getElementById('hapus-kelas-{{ $item->id }}').close()"
                                        class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition">
                                        Batal
                                    </button>

                                    <form method="POST" action="{{ route('admin.kelases.destroy', $item) }}">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                            class="rounded-xl bg-maroon-700 px-4 py-2 text-sm font-semibold text-white hover:bg-maroon-800 transition shadow-sm">
                                            Hapus
                                        </button>
                                    </form>

                                </div>

                            </div>
                        </dialog>

                    @empty

                        <tr>

                            <td colspan="8" class="text-center py-10 text-slate-500">

                                Belum ada data kelas.

                            </td>

                        </tr>
                    @endforelse

                </tbody>

            </table>

        </div>


        <div>

            {{ $kelases->links() }}

        </div>

    </div>
@endsection
