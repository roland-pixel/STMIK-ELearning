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

class KelasMalamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ambil Semua Data Master Semester yang Diperlukan untuk Kelas Malam
        $semesters = Semester::whereIn('nama_semester', [
            'Ganjil 2022/2023',
            'Genap 2022/2023',
            'Ganjil 2023/2024',
            'Genap 2023/2024',
            'Ganjil 2024/2025',
            'Genap 2024/2025',
            'Ganjil 2025/2026',
            // 'Ganjil 2023/2024', // Tinggal daftarkan di sini kalau nanti ada semester baru
        ])->get()->keyBy('nama_semester');

        // Ambil Dosen Pengajar (Siti Dosen)
        $dosenSiti = Dosen::whereHas('user', function ($query) {
            $query->where('email', 'dosen2@example.com');
        })->first();

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

        // 4. MAPPING DATA KELAS MALAM (KELAS N)
        $dataKelasMalam = [
            // --- SEMESTER GANJIL 2022/2023 ---
            [
                'semester' => 'Ganjil 2022/2023',
                'nama_mk' => 'PENDIDIKAN PANCASILA',
                'abjad' => 'N',
                'penerima' => 'SEMUA'
            ],
            [
                'semester' => 'Ganjil 2022/2023',
                'nama_mk' => 'ALJABAR LINIER',
                'abjad' => 'N',
                'penerima' => 'SEMUA'
            ],
            [
                'semester' => 'Ganjil 2022/2023',
                'nama_mk' => 'PEMROGRAMAN DASAR',
                'abjad' => 'N',
                'penerima' => 'SEMUA'
            ],
            [
                'semester' => 'Ganjil 2022/2023',
                'nama_mk' => 'BAHASA INGGRIS I',
                'abjad' => 'N',
                'penerima' => 'SEMUA'
            ],
            [
                'semester' => 'Ganjil 2022/2023',
                'nama_mk' => 'PENGANTAR TEKNOLOGI INFORMASI',
                'abjad' => 'N',
                'penerima' => 'SI'
            ],
            [
                'semester' => 'Ganjil 2022/2023',
                'nama_mk' => 'PAKET PEMROGRAMAN APLIKASI',
                'abjad' => 'N',
                'penerima' => 'SEMUA'
            ],
            [
                'semester' => 'Ganjil 2022/2023',
                'nama_mk' => 'KALKULUS',
                'abjad' => 'N',
                'penerima' => 'TI'
            ],
            [
                'semester' => 'Ganjil 2022/2023',
                'nama_mk' => 'LOGIKA INFORMATIKA',
                'abjad' => 'N',
                'penerima' => 'TI'
            ],

            // --- SEMESTER GENAP 2022/2023 ---
            [
                'semester' => 'Genap 2022/2023',
                'nama_mk' => 'PENGETAHUAN BISNIS',
                'abjad' => 'N',
                'penerima' => 'SI'
            ],
            [
                'semester' => 'Genap 2022/2023',
                'nama_mk' => 'BAHASA INGGRIS II',
                'abjad' => 'N',
                'penerima' => 'SEMUA'
            ],
            [
                'semester' => 'Genap 2022/2023',
                'nama_mk' => 'SISTEM OPERASI',
                'abjad' => 'N',
                'penerima' => 'SEMUA'
            ],
            [
                'semester' => 'Genap 2022/2023',
                'nama_mk' => 'BAHASA INDONESIA',
                'abjad' => 'N',
                'penerima' => 'SEMUA'
            ],
            [
                'semester' => 'Genap 2022/2023',
                'nama_mk' => 'KONSEP SISTEM INFORMASI',
                'abjad' => 'N',
                'penerima' => 'SI'
            ],
            [
                'semester' => 'Genap 2022/2023',
                'nama_mk' => 'JARINGAN KOMPUTER',
                'abjad' => 'N',
                'penerima' => 'SEMUA'
            ],
            [
                'semester' => 'Genap 2022/2023',
                'nama_mk' => 'LOGIKA ALGORITMA',
                'abjad' => 'N',
                'penerima' => 'SEMUA'
            ],
            [
                'semester' => 'Genap 2022/2023',
                'nama_mk' => 'DESAIN GRAFIS',
                'abjad' => 'N',
                'penerima' => 'SI'
            ],
            [
                'semester' => 'Genap 2022/2023',
                'nama_mk' => '',
                'abjad' => 'N',
                'penerima' => 'SI'
            ],
            [
                'semester' => 'Genap 2022/2023',
                'nama_mk' => '',
                'abjad' => 'N',
                'penerima' => 'SI'
            ],
            [
                'semester' => 'Genap 2022/2023',
                'nama_mk' => '',
                'abjad' => 'N',
                'penerima' => 'SI'
            ],
            [
                'semester'  => 'Genap 2022/2023',
                'nama_mk'   => 'SISTEM DIGITAL',
                'abjad'     => 'N',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Genap 2022/2023',
                'nama_mk'   => 'MATEMATIKA DISKRIT',
                'abjad'     => 'N',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Genap 2022/2023',
                'nama_mk'   => 'APLIKASI INTERNET',
                'abjad'     => 'N',
                'penerima'  => 'TI'
            ],

            // --- SEMESTER GANJIL 2023/2024 ---
            [
                'semester'  => 'Ganjil 2023/2024',
                'nama_mk'   => 'PEMROGRAMAN BERORIENTASI OBYEK',
                'abjad'     => 'N',
                'penerima'  => 'SEMUA'
            ],
            [
                'semester'  => 'Ganjil 2023/2024',
                'nama_mk'   => 'PENDIDIKAN AGAMA I',
                'abjad'     => 'N',
                'penerima'  => 'SEMUA'
            ],
            [
                'semester'  => 'Ganjil 2023/2024',
                'nama_mk'   => 'PENGANTAR MANAJEMEN',
                'abjad'     => 'N',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Ganjil 2023/2024',
                'nama_mk'   => 'STATISTIKA',
                'abjad'     => 'N',
                'penerima'  => 'SEMUA'
            ],
            [
                'semester'  => 'Ganjil 2023/2024',
                'nama_mk'   => 'MANAJEMEN SAINS',
                'abjad'     => 'N',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Ganjil 2023/2024',
                'nama_mk'   => 'SISTEM BASIS DATA',
                'abjad'     => 'N',
                'penerima'  => 'SEMUA'
            ],
            [
                'semester'  => 'Ganjil 2023/2024',
                'nama_mk'   => 'TEKNIK & INSTALASI KOMPUTER',
                'abjad'     => 'N',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Ganjil 2023/2024',
                'nama_mk'   => 'PEMROGRAMAN BASIS DATA',
                'abjad'     => 'N',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Ganjil 2023/2024',
                'nama_mk'   => 'KECERDASAN BUATAN',
                'abjad'     => 'N',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Ganjil 2023/2024',
                'nama_mk'   => 'METODE NUMERIK',
                'abjad'     => 'N',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Ganjil 2023/2024',
                'nama_mk'   => 'RISET TEKNOLOGI INFORMASI',
                'abjad'     => 'N',
                'penerima'  => 'TI'
            ],

            // --- SEMESTER GENAP 2023/2024 ---
            [
                'semester'  => 'Genap 2023/2024',
                'nama_mk'   => 'KOMPUTER & MASYARAKAT',
                'abjad'     => 'N',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Genap 2023/2024',
                'nama_mk'   => 'PENDIDIKAN ANTI KORUPSI',
                'abjad'     => 'N',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Genap 2023/2024',
                'nama_mk'   => 'ANALISA SISTEM INFORMASI',
                'abjad'     => 'N',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Genap 2023/2024',
                'nama_mk'   => 'STRUKTUR DATA',
                'abjad'     => 'N',
                'penerima'  => 'SEMUA'
            ],
            [
                'semester'  => 'Genap 2023/2024',
                'nama_mk'   => 'APLIKASI INTERNET',
                'abjad'     => 'N',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Genap 2023/2024',
                'nama_mk'   => 'ETIKA PROFESI',
                'abjad'     => 'N',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Genap 2023/2024',
                'nama_mk'   => 'PENDIDIKAN AGAMA II',
                'abjad'     => 'N',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Genap 2023/2024',
                'nama_mk'   => 'TEKNOLOGI BASIS DATA',
                'abjad'     => 'N',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Genap 2023/2024',
                'nama_mk'   => 'DATA MINING',
                'abjad'     => 'N',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Genap 2023/2024',
                'nama_mk'   => 'SISTEM INFORMASI MANAJEMEN',
                'abjad'     => 'N',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Genap 2023/2024',
                'nama_mk'   => 'DESAIN GRAFIS',
                'abjad'     => 'N',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Genap 2023/2024',
                'nama_mk'   => 'ARSITEKTUR DAN ORGANISASI KOMPUTER',
                'abjad'     => 'N',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Genap 2023/2024',
                'nama_mk'   => 'REKAYASA PERANGKAT LUNAK',
                'abjad'     => 'N',
                'penerima'  => 'TI'
            ],

            // --- SEMESTER GANJIL 2024/2025 ---
            [
                'semester'  => 'Ganjil 2024/2025',
                'nama_mk'   => 'METODOLOGI PENELITIAN',
                'abjad'     => 'N',
                'penerima'  => 'SEMUA'
            ],
            [
                'semester'  => 'Ganjil 2024/2025',
                'nama_mk'   => 'TESTING & IMPLEMENTASI SI',
                'abjad'     => 'N',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Ganjil 2024/2025',
                'nama_mk'   => 'PERANCANGAN SISTEM INFORMASI',
                'abjad'     => 'N',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Ganjil 2024/2025',
                'nama_mk'   => 'PEMROGRAMAN MOBILE I',
                'abjad'     => 'N',
                'penerima'  => 'SEMUA'
            ],
            [
                'semester'  => 'Ganjil 2024/2025',
                'nama_mk'   => 'VISUALISASI INFORMASI',
                'abjad'     => 'N',
                'penerima'  => 'SEMUA'
            ],
            [
                'semester'  => 'Ganjil 2024/2025',
                'nama_mk'   => 'KECAKAPAN ANTAR PERSONAL',
                'abjad'     => 'N',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Ganjil 2024/2025',
                'nama_mk'   => 'SISTEM TERDISTRIBUSI',
                'abjad'     => 'N',
                'penerima'  => 'SEMUA'
            ],
            [
                'semester'  => 'Ganjil 2024/2025',
                'nama_mk'   => 'TEKNIK & INSTALASI KOMPUTER',
                'abjad'     => 'N',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Ganjil 2024/2025',
                'nama_mk'   => 'MIKROKONTROLLER',
                'abjad'     => 'N',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Ganjil 2024/2025',
                'nama_mk'   => 'INTERNET UNTUK SEGALA',
                'abjad'     => 'N',
                'penerima'  => 'TI'
            ],

            // --- SEMESTER GENAP 2024/2025 ---
            [
                'semester'  => 'Genap 2024/2025',
                'nama_mk'   => 'MANAJEMEN PROYEK SI',
                'abjad'     => 'N',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Genap 2024/2025',
                'nama_mk'   => 'PEMROGRAMAN MOBILE II',
                'abjad'     => 'N',
                'penerima'  => 'SEMUA'
            ],
            [
                'semester'  => 'Genap 2024/2025',
                'nama_mk'   => 'REKAYASA PERANGKAT LUNAK',
                'abjad'     => 'N',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Genap 2024/2025',
                'nama_mk'   => 'PENDIDIKAN KEWARGANEGARAAN',
                'abjad'     => 'N',
                'penerima'  => 'SEMUA'
            ],
            [
                'semester'  => 'Genap 2024/2025',
                'nama_mk'   => 'PEMROGRAMAN WEB',
                'abjad'     => 'N',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Genap 2024/2025',
                'nama_mk'   => 'PENDIDIKAN AGAMA II',
                'abjad'     => 'N',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Genap 2024/2025',
                'nama_mk'   => 'ETIKA PROFESI',
                'abjad'     => 'N',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Genap 2024/2025',
                'nama_mk'   => 'PEMROGRAMAN GRAFIK',
                'abjad'     => 'N',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Genap 2024/2025',
                'nama_mk'   => 'INTERAKSI MANUSIA & KOMPUTER',
                'abjad'     => 'N',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Genap 2024/2025',
                'nama_mk'   => 'PENDIDIKAN ANTI KORUPSI',
                'abjad'     => 'N',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Genap 2024/2025',
                'nama_mk'   => 'SISTEM INFORMASI GEOGRAFIS',
                'abjad'     => 'N',
                'penerima'  => 'TI'
            ],

            // --- SEMESTER GANJIL 2025/2026  ---
            [
                'semester'  => 'Ganjil 2025/2026',
                'nama_mk'   => 'E-COMMERCE',
                'abjad'     => 'N',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Ganjil 2025/2026',
                'nama_mk'   => 'INTERNET UNTUK SEGALA',
                'abjad'     => 'N',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Ganjil 2025/2026',
                'nama_mk'   => 'RISET TEKNOLOGI INFORMASI',
                'abjad'     => 'N',
                'penerima'  => 'SI'
            ],
            [
                'semester'  => 'Ganjil 2025/2026',
                'nama_mk'   => 'KECAKAPAN ANTAR PERSONAL',
                'abjad'     => 'N',
                'penerima'  => 'TI'
            ],
            [
                'semester'  => 'Ganjil 2025/2026',
                'nama_mk'   => 'PENGOLAHAN CITRA DIGITAL',
                'abjad'     => 'N',
                'penerima'  => 'TI'
            ],
        ];

        // 5. Loop Proses Pembuatan Kelas & Anggota Khusus Malam
        foreach ($dataKelasMalam as $item) {
            if (!$allMatkuls->has($item['nama_mk']) || !$semesters->has($item['semester'])) {
                continue;
            }

            $mk = $allMatkuls->get($item['nama_mk']);
            $currentSemester = $semesters->get($item['semester']);

            // Buat Kelas Baru (Abjad otomatis N sesuai array)
            $kelas = Kelas::create([
                'uuid' => Str::uuid(),
                'dosen_id' => $dosenSiti->id,
                'mata_kuliah_id' => $mk->id,
                'semester_id' => $currentSemester->id,
                'nama_kelas' => $mk->nama_mk . ' - ' . $item['abjad'],
                'kode_gabung' => strtoupper($item['penerima']) . '-' . $item['abjad'] . '-' . Str::upper(Str::random(4)),
                'deskripsi' => "Kelas Malam otomatis untuk jurusan {$item['penerima']} - Semester {$item['semester']}",
                'persentase_tugas' => 30,
                'persentase_uts' => 30,
                'persentase_uas' => 40,
            ]);

            // Filter Target Jurusan
            $targetMahasiswa = collect();
            if ($item['penerima'] === 'SI') {
                $targetMahasiswa = $mhsSI;
            } elseif ($item['penerima'] === 'TI') {
                $targetMahasiswa = $mhsTI;
            } else {
                $targetMahasiswa = $mhsSI->merge($mhsTI);
            }

            // KUNCI UTAMA: Hanya mengambil mahasiswa yang string 'jenis_program'-nya ada kata 'malam'
            $targetMahasiswaMalam = $targetMahasiswa->filter(function ($mhs) {
                return Str::contains(Str::lower($mhs->jenis_program), 'malam');
            });

            // Masukkan Mahasiswa Malam ke Anggota Kelas
            foreach ($targetMahasiswaMalam as $mhs) {
                AnggotaKelas::create([
                    'kelas_id' => $kelas->id,
                    'mahasiswa_id' => $mhs->id,
                    'tanggal_gabung' => now(),
                ]);
            }
        }
    }
}
