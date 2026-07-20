@extends('admin.layouts.app')

@section('title', 'Tambah Mahasiswa - Maroon Sneat Style')
@section('page_title', 'Tambah Mahasiswa')
@section('page_desc', 'Membuat akun user + data mahasiswa')

@section('content')
    <div class="max-w-3xl mx-auto space-y-4">

        {{-- Alert error --}}
        @if ($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-800">
                <p class="font-semibold">Terjadi kesalahan:</p>
                <ul class="mt-1 list-disc pl-5 text-sm">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-2xl border border-slate-200 bg-white shadow-soft">
            <div class="p-6 sm:p-7">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-ink-900">Tambah Mahasiswa</h2>
                        <p class="text-sm text-ink-500 mt-1">Buat akun user sekaligus data mahasiswa.</p>
                    </div>

                    <span
                        class="inline-flex items-center rounded-full bg-maroon-600/10 px-3 py-1 text-xs font-semibold text-maroon-800 ring-1 ring-maroon-700/10">
                        Master Data
                    </span>
                </div>

                <form method="POST" action="{{ route('admin.mahasiswas.store') }}" class="mt-6 space-y-6">
                    @csrf

                    {{-- SECTION: USER --}}
                    <div class="rounded-2xl border border-slate-200/70 bg-slate-50/40 p-4 sm:p-5">
                        <div class="flex items-center justify-between gap-3">
                            <h3 class="text-sm font-semibold text-ink-900">Data Akun (User)</h3>
                            <span class="text-xs text-ink-500">Wajib</span>
                        </div>

                        <div class="mt-4 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-ink-700">Nama Lengkap</label>
                                <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}"
                                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm
                                           focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition"
                                    required>
                                @error('nama_lengkap')
                                    <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-ink-700">Email</label>
                                <input type="email" name="email" value="{{ old('email') }}"
                                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm
                                           focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition"
                                    required>
                                @error('email')
                                    <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-ink-700">Password</label>
                                <input type="password" name="password"
                                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm
                                           focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition"
                                    required>
                                @error('password')
                                    <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-ink-500">Minimal 8 karakter.</p>
                            </div>
                        </div>
                    </div>

                    {{-- SECTION: MAHASISWA --}}
                    <div class="rounded-2xl border border-slate-200/70 bg-slate-50/40 p-4 sm:p-5">
                        <div class="flex items-center justify-between gap-3">
                            <h3 class="text-sm font-semibold text-ink-900">Data Mahasiswa</h3>
                            <span class="text-xs text-ink-500">Wajib</span>
                        </div>

                        <div class="mt-4 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-ink-700">Jurusan</label>
                                <select name="jurusan_id"
                                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm
                                           focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition"
                                    required>
                                    <option value="" disabled {{ old('jurusan_id') ? '' : 'selected' }}>Pilih jurusan
                                    </option>
                                    @foreach ($jurusans as $j)
                                        <option value="{{ $j->id }}"
                                            {{ (string) old('jurusan_id') === (string) $j->id ? 'selected' : '' }}>
                                            {{ $j->kode_jurusan }} — {{ $j->nama_jurusan }} ({{ $j->jenjang }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('jurusan_id')
                                    <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-ink-700">NIM</label>
                                <input type="text" name="nim" value="{{ old('nim') }}"
                                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm
                                           focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition"
                                    required>
                                @error('nim')
                                    <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-ink-700">Angkatan</label>
                                    <input type="number" name="angkatan" value="{{ old('angkatan') }}"
                                        class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm
                                               focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition"
                                        min="2000" max="{{ date('Y') + 1 }}" required>
                                    @error('angkatan')
                                        <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-ink-700">Status Kelulusan</label>
                                    <select name="status" id="status_select"
                                        class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm
                                               focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition"
                                        required>
                                        <option value="aktif" {{ old('status', 'aktif') === 'aktif' ? 'selected' : '' }}>
                                            aktif</option>
                                        <option value="lulus" {{ old('status') === 'lulus' ? 'selected' : '' }}>lulus
                                        </option>
                                    </select>
                                    @error('status')
                                        <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- GRID TAMBAHAN UNTUK TANGGAL MASUK & TANGGAL LULUS --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-ink-700">Tanggal Masuk</label>
                                    <input type="date" name="tanggal_masuk" value="{{ old('tanggal_masuk') }}"
                                        class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm
                                               focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition"
                                        required>
                                    @error('tanggal_masuk')
                                        <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div id="tanggal_lulus_wrapper">
                                    <label class="block text-sm font-medium text-ink-700">Tanggal Lulus</label>
                                    <input type="date" name="tanggal_lulus" id="tanggal_lulus_input"
                                        value="{{ old('tanggal_lulus') }}"
                                        class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm
                                               focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition">
                                    @error('tanggal_lulus')
                                        <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-ink-700">Jenis Program</label>
                                    <select name="jenis_program"
                                        class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm
                                               focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition"
                                        required>
                                        <option value="" disabled {{ old('jenis_program') ? '' : 'selected' }}>Pilih
                                        </option>
                                        <option value="reguler" {{ old('jenis_program') === 'reguler' ? 'selected' : '' }}>
                                            reguler</option>
                                        <option value="malam" {{ old('jenis_program') === 'malam' ? 'selected' : '' }}>
                                            malam</option>
                                        <option value="pegawai" {{ old('jenis_program') === 'pegawai' ? 'selected' : '' }}>
                                            pegawai</option>
                                    </select>
                                    @error('jenis_program')
                                        <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-ink-700">Status Masuk</label>
                                    <select name="status_masuk"
                                        class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm
                                               focus:outline-none focus:ring-4 focus:ring-maroon-600/10 focus:border-maroon-700 transition"
                                        required>
                                        <option value="" disabled {{ old('status_masuk') ? '' : 'selected' }}>Pilih
                                        </option>
                                        <option value="normal" {{ old('status_masuk') === 'normal' ? 'selected' : '' }}>
                                            normal</option>
                                        <option value="transfer"
                                            {{ old('status_masuk') === 'transfer' ? 'selected' : '' }}>transfer</option>
                                    </select>
                                    @error('status_masuk')
                                        <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Divider --}}
                    <div class="h-px bg-slate-200/80"></div>

                    {{-- Actions --}}
                    <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2">
                        <a href="{{ route('admin.mahasiswas.index') }}"
                            class="inline-flex justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold
                                   text-ink-800 hover:bg-slate-50 transition">
                            Batal
                        </a>

                        <button type="submit"
                            class="inline-flex justify-center rounded-xl bg-maroon-700 px-4 py-2 text-sm font-semibold text-white
                                   hover:bg-maroon-800 transition shadow-[0_10px_22px_rgba(127,29,29,0.18)]">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <p class="text-xs text-ink-400 text-center">
            Data akan membuat akun user otomatis untuk mahasiswa.
        </p>
    </div>

    {{-- JAVASCRIPT VANILLA UNTUK TOGGLE INPUT TANGGAL LULUS --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const statusSelect = document.getElementById('status_select');
            const tanggalLulusWrapper = document.getElementById('tanggal_lulus_wrapper');
            const tanggalLulusInput = document.getElementById('tanggal_lulus_input');

            function toggleTanggalLulus() {
                if (statusSelect.value === 'lulus') {
                    tanggalLulusWrapper.style.display = 'block';
                    tanggalLulusInput.setAttribute('required', 'required');
                } else {
                    tanggalLulusWrapper.style.display = 'none';
                    tanggalLulusInput.removeAttribute('required');
                    tanggalLulusInput.value = ''; // Reset nilainya jika ganti ke aktif
                }
            }

            // Jalankan saat halaman pertama kali dimuat (antisipasi old value setelah failed validation)
            toggleTanggalLulus();

            // Jalankan setiap kali status select berubah
            statusSelect.addEventListener('change', toggleTanggalLulus);
        });
    </script>
@endsection
