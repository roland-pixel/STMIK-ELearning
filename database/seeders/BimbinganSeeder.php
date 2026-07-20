<?php

namespace Database\Seeders;

use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Semester;
use App\Models\Bimbingan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BimbinganSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ambil Semester Terkait
        // Sesuaikan nama_semester di database Anda dengan rentang angkatan 2022
        $semesters = Semester::whereIn('nama_semester', [
            'Genap 2024/2025',  // Contoh Semester 6 untuk Angkatan 2022
            'Ganjil 2025/2026', // Contoh Semester 7 untuk Angkatan 2022
            'Genap 2025/2026'   // Contoh Semester 8 untuk Angkatan 2022
        ])->get()->keyBy('nama_semester');

        // Balikkan penanganan jika data semester master Anda memiliki penamaan berbeda
        $smstr6 = $semesters->firstWhere('nama_semester', 'Genap 2024/2025') ?? Semester::where('status_aktif', 'active')->first();
        $smstr7 = $semesters->firstWhere('nama_semester', 'Ganjil 2025/2026') ?? Semester::orderBy('id', 'desc')->first();
        $smstr8 = $semesters->firstWhere('nama_semester', 'Genap 2025/2026') ?? Semester::orderBy('id', 'desc')->first();

        // 2. Ambil atau Pastikan Mata Kuliah Bimbingan Ada di Tabel mata_kuliahs
        $mkPkl = MataKuliah::where('nama_mk', 'LIKE', '%PKL%')
            ->orWhere('nama_mk', 'LIKE', '%PRAKTEK KERJA LAPANGAN%')->first();
        $mkPraSkripsi = MataKuliah::where('nama_mk', 'LIKE', '%PRA SKRIPSI%')
            ->orWhere('nama_mk', 'LIKE', '%PROPOSAL%')->first();
        $mkSkripsi = MataKuliah::where('nama_mk', 'LIKE', '%SKRIPSI%')
            ->where('nama_mk', 'NOT LIKE', '%PRA%')->first();

        // Jika belum ada di master MataKuliahSeeder, kita buatkan fallback-nya di sini
        if (!$mkPkl) {
            $mkPkl = MataKuliah::create(['nama_mk' => 'PRAKTEK KERJA LAPANGAN', 'sks' => 2, 'jenis_mk' => 'Spesial']);
        }
        if (!$mkPraSkripsi) {
            $mkPraSkripsi = MataKuliah::create(['nama_mk' => 'PRA SKRIPSI', 'sks' => 2, 'jenis_mk' => 'Spesial']);
        }
        if (!$mkSkripsi) {
            $mkSkripsi = MataKuliah::create(['nama_mk' => 'SKRIPSI', 'sks' => 6, 'jenis_mk' => 'Spesial']);
        }

        // 3. Ambil Semua Mahasiswa & Semua Dosen
        $listMahasiswa = Mahasiswa::all();
        $listDosen = Dosen::all();

        if ($listMahasiswa->isEmpty() || $listDosen->isEmpty()) {
            $this->command->error('Gagal generate bimbingan. Pastikan data Mahasiswa dan Dosen sudah di-seed terlebih dahulu!');
            return;
        }

        $dosenCount = $listDosen->count();

        // 4. Lakukan Perulangan untuk Mengisi Tabel Bimbingan
        foreach ($listMahasiswa as $index => $mhs) {

            // Menggunakan teknik Round-Robin agar dosen pembimbing bervariasi bagi setiap mahasiswa
            $dosenPembimbingPkl = $listDosen[$index % $dosenCount];
            $dosenPembimbingPra  = $listDosen[($index + 1) % $dosenCount];
            $dosenPembimbingSkripsi = $listDosen[($index + 2) % $dosenCount];

            $prodi = $mhs->jurusan->kode_jurusan ?? 'IT';

            // --- SEED DATA PKL (Semester 6) ---
            Bimbingan::create([
                'uuid' => Str::uuid(),
                'semester_id' => $smstr6->id ?? 1,
                'mahasiswa_id' => $mhs->id,
                'dosen_pembimbing_id' => $dosenPembimbingPkl->id,
                'mata_kuliah_id' => $mkPkl->id,
                'judul_penelitian' => "Sistem Informasi Manajemen Instansi pada Dinas Kominfo berbasis " . ($prodi == 'SI' ? 'Web' : 'Mobile'),
                'nilai_angka' => rand(78, 95),
                'status' => 'approved',
            ]);

            // --- SEED DATA PRA SKRIPSI (Semester 7) ---
            Bimbingan::create([
                'uuid' => Str::uuid(),
                'semester_id' => $smstr7->id ?? 1,
                'mahasiswa_id' => $mhs->id,
                'dosen_pembimbing_id' => $dosenPembimbingPra->id,
                'mata_kuliah_id' => $mkPraSkripsi->id,
                'judul_penelitian' => "Analisis dan Perancangan Sistem E-Learning STMIK Menggunakan Arsitektur SPA " . $mhs->nim,
                'nilai_angka' => rand(75, 93),
                'status' => 'approved',
            ]);

            // --- SEED DATA SKRIPSI (Semester 8) ---
            // PERBAIKAN: Menghapus logika pengecualian kelipatan 5 ($index % 5 !== 0)
            // Sekarang seluruh mahasiswa disamaratakan langsung lulus dan mendapat nilai acak
            Bimbingan::create([
                'uuid' => Str::uuid(),
                'semester_id' => $smstr8->id ?? 1,
                'mahasiswa_id' => $mhs->id,
                'dosen_pembimbing_id' => $dosenPembimbingSkripsi->id,
                'mata_kuliah_id' => $mkSkripsi->id,
                'judul_penelitian' => "Implementasi Algoritma Pendukung Keputusan Penilaian Kinerja Pegawai Non-ASN Menggunakan Framework Laravel",
                'nilai_angka' => rand(80, 98), // Semua mahasiswa mendapatkan nilai antara 80 s/d 98
                'status' => 'approved',        // Semua status di-set sebagai approved (lulus)
            ]);
        }

        $this->command->info('Selesai! Data bimbingan PKL, Pra Skripsi, dan Skripsi untuk seluruh mahasiswa berhasil dibuat.');
    }
}
