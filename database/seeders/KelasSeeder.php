<?php

namespace Database\Seeders;

use App\Models\Dosen;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Semester;
use App\Models\AnggotaKelas;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class KelasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ambil Semua Data Master Semester yang Diperlukan
        $semesters = Semester::whereIn('nama_semester', [
            'Ganjil 2022/2023',
            'Genap 2022/2023',
            'Ganjil 2023/2024',
            'Genap 2023/2024',
            'Ganjil 2024/2025',
            'Genap 2024/2025',
            'Ganjil 2025/2026',
        ])->get()->keyBy('nama_semester');

        // Ambil Dosen Pengajar (Siti Dosen)
        $dosenSiti = Dosen::whereHas('user', function ($query) {
            $query->where('email', 'dosen2@example.com');
        })->first();

        // Antisipasi jika data master belum siap
        if ($semesters->isEmpty() || !$dosenSiti) {
            return;
        }

        // 2. Ambil Semua Mahasiswa Berdasarkan Jurusan
        $mhsSI = Mahasiswa::whereHas('jurusan', function ($q) {
            $q->where('kode_jurusan', 'SI');
        })->get();

        $mhsTI = Mahasiswa::whereHas('jurusan', function ($q) {
            $q->where('kode_jurusan', 'TI');
        })->get();

        // 3. Ambil Semua Master Mata Kuliah untuk Mapping ID
        $allMatkuls = MataKuliah::all()->keyBy('nama_mk');

        // 4. MAPPING DATA RIIL KELAS (DIPISAH BERDASARKAN SEMESTER TARGET)
        $dataKelasMultiSemester = [
            // --- SEMESTER GANJIL 2022/2023 ---
            [
                'semester'  => 'Ganjil 2022/2023',
                'nama_mk'   => 'PENDIDIKAN PANCASILA',
                'abjad'     => 'A',
                'penerima'  => 'SEMUA'
            ],
            [
                'semester'  => 'Ganjil 2022/2023',
                'nama_mk'   => 'BAHASA INGGRIS I',
                'abjad'     => 'A',
                'penerima'  => 'SEMUA'
            ],
            [
                'semester'  => 'Ganjil 2022/2023',
                'nama_mk'   => 'ALJABAR LINIER',
                'abjad'     => 'A',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Ganjil 2022/2023',
                'nama_mk'   => 'PEMROGRAMAN DASAR',
                'abjad'     => 'B',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Ganjil 2022/2023',
                'nama_mk'   => 'PENGANTAR TEKNOLOGI INFORMASI',
                'abjad'     => 'A',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Ganjil 2022/2023',
                'nama_mk'   => 'PAKET PEMROGRAMAN APLIKASI',
                'abjad'     => 'A',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Ganjil 2022/2023',
                'nama_mk'   => 'PAKET PEMROGRAMAN APLIKASI',
                'abjad'     => 'B',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Ganjil 2022/2023',
                'nama_mk'   => 'ALJABAR LINIER',
                'abjad'     => 'B',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Ganjil 2022/2023',
                'nama_mk'   => 'PEMROGRAMAN DASAR',
                'abjad'     => 'A',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Ganjil 2022/2023',
                'nama_mk'   => 'KALKULUS',
                'abjad'     => 'A',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Ganjil 2022/2023',
                'nama_mk'   => 'LOGIKA INFORMATIKA',
                'abjad'     => 'A',
                'penerima'  => 'TI'
            ],

            // --- SEMESTER GENAP 2022/2023 (KELAS JURUSAN SI) ---
            [
                'semester'  => 'Genap 2022/2023',
                'nama_mk'   => 'PENGETAHUAN BISNIS',
                'abjad'     => 'A',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Genap 2022/2023',
                'nama_mk'   => 'BAHASA INGGRIS II',
                'abjad'     => 'A',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Genap 2022/2023',
                'nama_mk'   => 'SISTEM OPERASI',
                'abjad'     => 'A',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Genap 2022/2023',
                'nama_mk'   => 'BAHASA INDONESIA',
                'abjad'     => 'C',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Genap 2022/2023',
                'nama_mk'   => 'KONSEP SISTEM INFORMASI',
                'abjad'     => 'A',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Genap 2022/2023',
                'nama_mk'   => 'JARINGAN KOMPUTER',
                'abjad'     => 'A',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Genap 2022/2023',
                'nama_mk'   => 'LOGIKA ALGORITMA',
                'abjad'     => 'A',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Genap 2022/2023',
                'nama_mk'   => 'DESAIN GRAFIS',
                'abjad'     => 'A',
                'penerima'  => 'SI'
            ],

            // --- SEMESTER GENAP 2022/2023 (KELAS JURUSAN TI) ---
            [
                'semester'  => 'Genap 2022/2023',
                'nama_mk'   => 'BAHASA INDONESIA',
                'abjad'     => 'A',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Genap 2022/2023',
                'nama_mk'   => 'BAHASA INGGRIS II',
                'abjad'     => 'B',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Genap 2022/2023',
                'nama_mk'   => 'JARINGAN KOMPUTER',
                'abjad'     => 'B',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Genap 2022/2023',
                'nama_mk'   => 'SISTEM OPERASI',
                'abjad'     => 'B',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Genap 2022/2023',
                'nama_mk'   => 'SISTEM DIGITAL',
                'abjad'     => 'A',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Genap 2022/2023',
                'nama_mk'   => 'MATEMATIKA DISKRIT',
                'abjad'     => 'A',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Genap 2022/2023',
                'nama_mk'   => 'LOGIKA ALGORITMA',
                'abjad'     => 'B',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Genap 2022/2023',
                'nama_mk'   => 'APLIKASI INTERNET',
                'abjad'     => 'B',
                'penerima'  => 'TI'
            ],

            // --- SEMESTER Ganjil 2023/2024  ---
            [
                'semester'  => 'Ganjil 2023/2024',
                'nama_mk'   => 'PEMROGRAMAN BERORIENTASI OBYEK',
                'abjad'     => 'A',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Ganjil 2023/2024',
                'nama_mk'   => 'PENDIDIKAN AGAMA I',
                'abjad'     => 'A',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Ganjil 2023/2024',
                'nama_mk'   => 'PENGANTAR MANAJEMEN',
                'abjad'     => 'A',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Ganjil 2023/2024',
                'nama_mk'   => 'STATISTIKA',
                'abjad'     => 'A',
                'penerima'  => 'SEMUA'
            ],
            [
                'semester'  => 'Ganjil 2023/2024',
                'nama_mk'   => 'MANAJEMEN SAINS',
                'abjad'     => 'A',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Ganjil 2023/2024',
                'nama_mk'   => 'SISTEM BASIS DATA',
                'abjad'     => 'A',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Ganjil 2023/2024',
                'nama_mk'   => 'TEKNIK & INSTALASI KOMPUTER',
                'abjad'     => 'A',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Ganjil 2023/2024',
                'nama_mk'   => 'PEMROGRAMAN BASIS DATA',
                'abjad'     => 'A',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Ganjil 2023/2024',
                'nama_mk'   => 'PENDIDIKAN AGAMA I',
                'abjad'     => 'O',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Ganjil 2023/2024',
                'nama_mk'   => 'PEMROGRAMAN BERORIENTASI OBYEK',
                'abjad'     => 'B',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Ganjil 2023/2024',
                'nama_mk'   => 'KECERDASAN BUATAN',
                'abjad'     => 'A',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Ganjil 2023/2024',
                'nama_mk'   => 'METODE NUMERIK',
                'abjad'     => 'A',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Ganjil 2023/2024',
                'nama_mk'   => 'SISTEM BASIS DATA',
                'abjad'     => 'B',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Ganjil 2023/2024',
                'nama_mk'   => 'RISET TEKNOLOGI INFORMASI',
                'abjad'     => 'B',
                'penerima'  => 'TI'
            ],

            // --- SEMESTER GENAP 2023/2024  ---
            [
                'semester'  => 'Genap 2023/2024',
                'nama_mk'   => 'KOMPUTER & MASYARAKAT',
                'abjad'     => 'A',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Genap 2023/2024',
                'nama_mk'   => 'PENDIDIKAN ANTI KORUPSI',
                'abjad'     => 'A',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Genap 2023/2024',
                'nama_mk'   => 'ANALISA SISTEM INFORMASI',
                'abjad'     => 'A',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Genap 2023/2024',
                'nama_mk'   => 'STRUKTUR DATA',
                'abjad'     => 'A',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Genap 2023/2024',
                'nama_mk'   => 'APLIKASI INTERNET',
                'abjad'     => 'A',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Genap 2023/2024',
                'nama_mk'   => 'ETIKA PROFESI',
                'abjad'     => 'B',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Genap 2023/2024',
                'nama_mk'   => 'PENDIDIKAN AGAMA II',
                'abjad'     => 'B',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Genap 2023/2024',
                'nama_mk'   => 'TEKNOLOGI BASIS DATA',
                'abjad'     => 'A',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Genap 2023/2024',
                'nama_mk'   => 'DATA MINING',
                'abjad'     => 'A',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Genap 2023/2024',
                'nama_mk'   => 'STRUKTUR DATA',
                'abjad'     => 'B',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Genap 2023/2024',
                'nama_mk'   => 'SISTEM INFORMASI MANAJEMEN',
                'abjad'     => 'A',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Genap 2023/2024',
                'nama_mk'   => 'DESAIN GRAFIS',
                'abjad'     => 'A',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Genap 2023/2024',
                'nama_mk'   => 'ARSITEKTUR DAN ORGANISASI KOMPUTER',
                'abjad'     => 'A',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Genap 2023/2024',
                'nama_mk'   => 'REKAYASA PERANGKAT LUNAK',
                'abjad'     => 'B',
                'penerima'  => 'TI'
            ],

            // --- SEMESTER Ganjil 2024/2025  ---
            [
                'semester'  => 'Ganjil 2024/2025',
                'nama_mk'   => 'METODOLOGI PENELITIAN',
                'abjad'     => 'A',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Ganjil 2024/2025',
                'nama_mk'   => 'TESTING & IMPLEMENTASI SI',
                'abjad'     => 'A',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Ganjil 2024/2025',
                'nama_mk'   => 'PERANCANGAN SISTEM INFORMASI',
                'abjad'     => 'A',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Ganjil 2024/2025',
                'nama_mk'   => 'PEMROGRAMAN MOBILE I',
                'abjad'     => 'A',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Ganjil 2024/2025',
                'nama_mk'   => 'VISUALISASI INFORMASI',
                'abjad'     => 'A',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Ganjil 2024/2025',
                'nama_mk'   => 'KECAKAPAN ANTAR PERSONAL',
                'abjad'     => 'A',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Ganjil 2024/2025',
                'nama_mk'   => 'SISTEM TERDISTRIBUSI',
                'abjad'     => 'A',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Ganjil 2024/2025',
                'nama_mk'   => 'METODOLOGI PENELITIAN',
                'abjad'     => 'B',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Ganjil 2024/2025',
                'nama_mk'   => 'TEKNIK & INSTALASI KOMPUTER',
                'abjad'     => 'B',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Ganjil 2024/2025',
                'nama_mk'   => 'VISUALISASI INFORMASI',
                'abjad'     => 'B',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Ganjil 2024/2025',
                'nama_mk'   => 'PEMROGRAMAN MOBILE I',
                'abjad'     => 'B',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Ganjil 2024/2025',
                'nama_mk'   => 'MIKROKONTROLLER',
                'abjad'     => 'B',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Ganjil 2024/2025',
                'nama_mk'   => 'INTERNET UNTUK SEGALA',
                'abjad'     => 'B',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Ganjil 2024/2025',
                'nama_mk'   => 'SISTEM TERDISTRIBUSI',
                'abjad'     => 'B',
                'penerima'  => 'TI'
            ],

            // --- SEMESTER Genap 2024/2025  ---
            [
                'semester'  => 'Genap 2024/2025',
                'nama_mk'   => 'MANAJEMEN PROYEK SI',
                'abjad'     => 'A',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Genap 2024/2025',
                'nama_mk'   => 'PEMROGRAMAN MOBILE II',
                'abjad'     => 'A',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Genap 2024/2025',
                'nama_mk'   => 'REKAYASA PERANGKAT LUNAK',
                'abjad'     => 'A',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Genap 2024/2025',
                'nama_mk'   => 'PENDIDIKAN KEWARGANEGARAAN',
                'abjad'     => 'A',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Genap 2024/2025',
                'nama_mk'   => 'PEMROGRAMAN WEB',
                'abjad'     => 'A',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Genap 2024/2025',
                'nama_mk'   => 'PENDIDIKAN AGAMA II',
                'abjad'     => 'O',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Genap 2024/2025',
                'nama_mk'   => 'ETIKA PROFESI',
                'abjad'     => 'B',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Genap 2024/2025',
                'nama_mk'   => 'PEMROGRAMAN MOBILE II',
                'abjad'     => 'B',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Genap 2024/2025',
                'nama_mk'   => 'PEMROGRAMAN GRAFIK',
                'abjad'     => 'A',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Genap 2024/2025',
                'nama_mk'   => 'INTERAKSI MANUSIA & KOMPUTER',
                'abjad'     => 'A',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Genap 2024/2025',
                'nama_mk'   => 'PENDIDIKAN KEWARGANEGARAAN',
                'abjad'     => 'B',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Genap 2024/2025',
                'nama_mk'   => 'PENDIDIKAN ANTI KORUPSI',
                'abjad'     => 'B',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Genap 2024/2025',
                'nama_mk'   => 'SISTEM INFORMASI GEOGRAFIS',
                'abjad'     => 'A',
                'penerima'  => 'TI'
            ],

            // --- SEMESTER GANJIL 2025/2026  ---
            [
                'semester'  => 'Ganjil 2025/2026',
                'nama_mk'   => 'E-COMMERCE',
                'abjad'     => 'A',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Ganjil 2025/2026',
                'nama_mk'   => 'INTERNET UNTUK SEGALA',
                'abjad'     => 'A',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Ganjil 2025/2026',
                'nama_mk'   => 'RISET TEKNOLOGI INFORMASI',
                'abjad'     => 'A',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Ganjil 2025/2026',
                'nama_mk'   => 'KECAKAPAN ANTAR PERSONAL',
                'abjad'     => 'B',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Ganjil 2025/2026',
                'nama_mk'   => 'PENGOLAHAN CITRA DIGITAL',
                'abjad'     => 'A',
                'penerima'  => 'TI'
            ],
        ];

        // 5. Loop Proses Pembuatan Kelas & Anggota
        foreach ($dataKelasMultiSemester as $item) {
            // Validasi apakah nama mata kuliah terdaftar di master data seeder
            if (!$allMatkuls->has($item['nama_mk'])) {
                continue;
            }

            // Validasi apakah semester target terdaftar di database
            if (!$semesters->has($item['semester'])) {
                continue;
            }

            $mk = $allMatkuls->get($item['nama_mk']);
            $currentSemester = $semesters->get($item['semester']);

            // Buat Kelas Riil Baru
            $kelas = Kelas::create([
                'uuid' => Str::uuid(),
                'dosen_id' => $dosenSiti->id,
                'mata_kuliah_id' => $mk->id,
                'semester_id' => $currentSemester->id,
                'nama_kelas' => $mk->nama_mk . ' - ' . $item['abjad'],
                'kode_gabung' => strtoupper($item['penerima']) . '-' . $item['abjad'] . '-' . Str::upper(Str::random(4)),
                'deskripsi' => "Kelas Pagi otomatis untuk jurusan {$item['penerima']} pada semester {$item['semester']}",
                'persentase_tugas' => 30,
                'persentase_uts' => 30,
                'persentase_uas' => 40,
            ]);

            // Filter Target Jurusan Mahasiswa
            $targetMahasiswa = collect();
            if ($item['penerima'] === 'SI') {
                $targetMahasiswa = $mhsSI;
            } elseif ($item['penerima'] === 'TI') {
                $targetMahasiswa = $mhsTI;
            } else {
                $targetMahasiswa = $mhsSI->merge($mhsTI);
            }

            // Filter Program Pagi (Mencegah mahasiswa malam ikut masuk)
            $targetMahasiswaPagi = $targetMahasiswa->filter(function ($mhs) {
                return !Str::contains(Str::lower($mhs->jenis_program), 'malam');
            });

            // Masukkan Mahasiswa Pagi yang terfilter ke Anggota Kelas
            foreach ($targetMahasiswaPagi as $mhs) {
                AnggotaKelas::create([
                    'kelas_id' => $kelas->id,
                    'mahasiswa_id' => $mhs->id,
                    'tanggal_gabung' => now(),
                ]);
            }
        }
    }
}
