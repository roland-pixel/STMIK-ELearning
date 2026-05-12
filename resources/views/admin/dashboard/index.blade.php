@extends('admin.layouts.app')

@section('title', 'Dashboard - Maroon Sneat Style')

@section('content')
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

        <!-- Welcome card (biarin sama) -->
        <section class="xl:col-span-8 bg-white border border-ink-200 rounded-3xl shadow-soft overflow-hidden">
            <div class="p-6 sm:p-7 flex flex-col sm:flex-row gap-6 items-start sm:items-center justify-between">
                <div class="max-w-xl">
                    <h2 class="text-2xl sm:text-3xl font-bold text-maroon-700">
                        Dashboard Admin <span class="text-2xl">🧾</span>
                    </h2>
                    <p class="mt-2 text-ink-600">
                        Sistem Akademik • Panel Administrator
                    </p>
                    <button
                        class="mt-5 inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-maroon-700 text-white font-semibold hover:bg-maroon-800 transition">
                        Kelola Data
                    </button>
                </div>

                <!-- Illustration placeholder (biarin sama) -->
                <div class="w-full sm:w-[320px]">
                    <div
                        class="relative h-44 sm:h-40 rounded-3xl bg-gradient-to-br from-maroon-600/10 to-ink-100 border border-ink-200">
                        <div class="absolute -bottom-3 right-5 w-24 h-24 rounded-full bg-maroon-700/10"></div>
                        <div class="absolute bottom-5 left-6 text-ink-500 text-sm">
                            <div
                                class="w-8 h-8 rounded-full bg-white border border-ink-200 flex items-center justify-center mb-2">
                                <svg class="w-4 h-4 text-maroon-700" viewBox="0 0 24 24" fill="none">
                                    <path d="M12 7v6l4 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <path d="M21 12a9 9 0 1 1-9-9" stroke="currentColor" stroke-width="2" />
                                </svg>
                            </div>
                            <p class="font-semibold">Welcome!</p>
                        </div>

                        <svg class="absolute right-6 bottom-0 w-36 h-36" viewBox="0 0 120 120" fill="none">
                            <path d="M35 52c0-14 11-25 25-25s25 11 25 25-11 25-25 25-25-11-25-25Z"
                                fill="rgba(159,18,57,.15)" />
                            <path d="M40 92c6-12 18-19 31-19s25 7 31 19" stroke="rgba(15,23,42,.25)" stroke-width="8"
                                stroke-linecap="round" />
                            <path d="M47 51c0-9 7-16 16-16s16 7 16 16" stroke="rgba(15,23,42,.25)" stroke-width="8"
                                stroke-linecap="round" />
                        </svg>
                    </div>
                </div>
            </div>
        </section>

        <!-- ✅ 4 mini card statistik akademik -->
        <section class="xl:col-span-4 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-2 gap-6">
            <!-- Total Mahasiswa -->
            <div class="bg-white border border-ink-200 rounded-3xl shadow-soft p-5">
                <div class="flex items-start justify-between">
                    <div class="w-11 h-11 rounded-2xl bg-maroon-600/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-maroon-700" viewBox="0 0 24 24" fill="none">
                            <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" stroke="currentColor" stroke-width="1.8" />
                            <path d="M4 21a8 8 0 0 1 16 0" stroke="currentColor" stroke-width="1.8" />
                        </svg>
                    </div>
                </div>
                <p class="mt-3 text-sm text-ink-500">Total Mahasiswa</p>
                <p class="mt-1 text-2xl font-bold" id="totalMahasiswa">{{ $totalMahasiswa }}</p>
                <p class="mt-2 text-sm text-ink-600">Mahasiswa terdaftar</p>
            </div>

            <!-- Total Dosen -->
            <div class="bg-white border border-ink-200 rounded-3xl shadow-soft p-5">
                <div class="flex items-start justify-between">
                    <div class="w-11 h-11 rounded-2xl bg-maroon-600/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-maroon-700" viewBox="0 0 24 24" fill="none">
                            <path d="M16 11a4 4 0 1 0-8 0" stroke="currentColor" stroke-width="1.8" />
                            <path d="M4 21a8 8 0 0 1 16 0" stroke="currentColor" stroke-width="1.8" />
                        </svg>
                    </div>
                </div>
                <p class="mt-3 text-sm text-ink-500">Total Dosen</p>
                <p class="mt-1 text-2xl font-bold" id="totalDosen">{{ $totalDosen }}</p>
                <p class="mt-2 text-sm text-ink-600">Dosen pengajar</p>
            </div>

            <!-- Total Mata Kuliah -->
            <div class="bg-white border border-ink-200 rounded-3xl shadow-soft p-5">
                <div class="flex items-start justify-between">
                    <div class="w-11 h-11 rounded-2xl bg-maroon-600/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-maroon-700" viewBox="0 0 24 24" fill="none">
                            <path d="M4 5h12a3 3 0 0 1 3 3v11H7a3 3 0 0 0-3 3V5Z" stroke="currentColor" stroke-width="1.6"
                                stroke-linejoin="round" />
                            <path d="M7 19V5" stroke="currentColor" stroke-width="1.6" />
                        </svg>
                    </div>
                </div>
                <p class="mt-3 text-sm text-ink-500">Total Mata Kuliah</p>
                <p class="mt-1 text-2xl font-bold" id="totalMatkul">{{ $totalMatkul }}</p>
                <p class="mt-2 text-sm text-ink-600">Kurikulum aktif</p>
            </div>

            <!-- Semester Aktif -->
            <div class="bg-white border border-ink-200 rounded-3xl shadow-soft p-5">
                <div class="flex items-start justify-between">
                    <div class="w-11 h-11 rounded-2xl bg-maroon-600/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-maroon-700" viewBox="0 0 24 24" fill="none">
                            <path d="M7 3h10a2 2 0 0 1 2 2v16l-4-2-4 2-4-2-4 2V5a2 2 0 0 1 2-2Z" stroke="currentColor"
                                stroke-width="1.6" stroke-linejoin="round" />
                            <path d="M8 9h8" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                            <path d="M8 13h6" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </div>
                </div>
                <p class="mt-3 text-sm text-ink-500">Semester Aktif</p>
                <p class="mt-1 text-xl font-bold" id="semesterAktif">
                    {{ $semesterAktif->nama_semester ?? 'Tidak Ada' }}
                </p>
                <p class="mt-2 text-sm text-ink-600">Saat ini berjalan</p>
            </div>
        </section>

        <!-- Total Revenue chart (biarin sama) -->
        <!-- Statistik Pengumpulan Tugas (Ganti dari Total Revenue) -->
        <section class="xl:col-span-8 bg-white border border-ink-200 rounded-3xl shadow-soft overflow-hidden">
            <div class="px-6 py-5 flex items-center justify-between border-b border-ink-200">
                <div>
                    <h3 class="text-lg font-semibold">Statistik Pengumpulan Tugas</h3>
                    <p class="text-sm text-ink-500 mt-0.5">
                        <span class="inline-flex items-center gap-2 mr-4">
                            <span class="w-2.5 h-2.5 rounded-full bg-maroon-700"></span> Tahun {{ date('Y') }}
                        </span>
                    </p>
                </div>
            </div>
            <div class="p-6">
                <div class="h-72">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </section>

        <!-- Status Mahasiswa (Ganti dari Growth) -->
        <section class="xl:col-span-4 bg-white border border-ink-200 rounded-3xl shadow-soft overflow-hidden">
            <div class="px-6 py-5 flex items-center justify-between border-b border-ink-200">
                <div class="flex items-center gap-3">
                    <h3 class="text-lg font-semibold">Status Mahasiswa</h3>
                    <span
                        class="text-xs px-2 py-1 rounded-lg bg-maroon-600/10 text-maroon-800 border border-maroon-200/50">Aktif</span>
                </div>
            </div>

            <div class="p-6">
                <div class="flex items-center justify-center">
                    <div class="relative w-52 h-52">
                        <svg viewBox="0 0 120 120" class="w-full h-full">
                            <circle cx="60" cy="60" r="46" stroke="rgba(148,163,184,.35)"
                                stroke-width="10" fill="none" stroke-linecap="round" stroke-dasharray="289"
                                stroke-dashoffset="0" />
                            <circle id="gaugeArc" cx="60" cy="60" r="46" stroke="rgba(159,18,57,.9)"
                                stroke-width="10" fill="none" stroke-linecap="round" stroke-dasharray="289"
                                stroke-dashoffset="80" transform="rotate(-90 60 60)" />
                        </svg>

                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <p id="gaugeValue" class="text-4xl font-bold text-ink-900">{{ $persentaseAktif }}%</p>
                            <p class="text-sm text-ink-500 -mt-1">Mahasiswa Aktif</p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 rounded-2xl border border-ink-200 bg-ink-50 p-4">
                    <p class="text-sm text-ink-600">
                        Saat ini terdapat <span class="font-semibold text-maroon-800">{{ $persentaseAktif }}%</span>
                        mahasiswa dengan status aktif dari total seluruh mahasiswa.
                    </p>
                </div>
            </div>
        </section>

    </div>
@endsection

@push('page-scripts')
    <script>
        // --- Data dari Laravel ---
        const dataPengumpulan = @json($chartData);
        const persentaseAktif = {{ $persentaseAktif }};

        // --- Chart (Pengumpulan Tugas) ---
        const ctx = document.getElementById('revenueChart');
        const labels = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Jumlah Pengumpulan',
                    data: dataPengumpulan, // Menggunakan data dinamis
                    borderRadius: 10,
                    backgroundColor: 'rgba(159, 18, 57, 0.85)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                // ... (options lainnya tetap sama)
            }
        });

        // --- Gauge (Persentase Mahasiswa) ---
        const gaugeArc = document.getElementById('gaugeArc');
        const gaugeValue = document.getElementById('gaugeValue');
        const CIRC = 289;

        function setGauge(percent) {
            const p = Math.max(0, Math.min(100, percent));
            const offset = CIRC * (1 - p / 100);
            gaugeArc.style.strokeDasharray = CIRC;
            gaugeArc.style.strokeDashoffset = offset;
            gaugeValue.textContent = `${p}%`;
        }

        // Panggil fungsi dengan data dari database
        setGauge(persentaseAktif);
    </script>
@endpush
