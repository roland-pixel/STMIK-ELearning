<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\AnggotaKelas;
use App\Models\Penilaian;
use App\Models\Pengumpulan;
use App\Models\RekapNilai;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class NilaiBulkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ambil semua kelas yang saat ini belum memiliki nilai sama sekali
        $allKelas = Kelas::whereDoesntHave('penilaians')->get();

        if ($allKelas->isEmpty()) {
            $this->command->info('Semua kelas sudah memiliki nilai atau tidak ada kelas ditemukan.');
            return;
        }

        $kategoriList = ['tugas', 'uts', 'uas'];

        foreach ($allKelas as $kelas) {
            // Ambil semua anggota mahasiswa di kelas ini
            $anggotaKelas = AnggotaKelas::where('kelas_id', $kelas->id)->get();

            if ($anggotaKelas->isEmpty()) {
                continue; // Skip jika kelas kosong tidak ada mahasiswa
            }

            try {
                DB::beginTransaction();

                // 2. Buat Master Penilaian Manual (Tugas, UTS, UAS) untuk kelas ini
                $mapPenilaian = [];
                foreach ($kategoriList as $kat) {
                    $penilaian = Penilaian::create([
                        'uuid'           => (string) Str::uuid(),
                        'kelas_id'       => $kelas->id,
                        'judul'          => 'Nilai ' . strtoupper($kat) . ' (Input Otomatis Seeder)',
                        'instruksi'      => 'Diinput secara otomatis lewat sistem seeder massal.',
                        'kategori'       => $kat,
                        'mode_penilaian' => 'manual',
                    ]);
                    $mapPenilaian[$kat] = $penilaian->id;
                }

                // 3. Inject Nilai untuk setiap mahasiswa di dalam kelas
                foreach ($anggotaKelas as $anggota) {
                    $mahasiswaId = $anggota->mahasiswa_id;

                    // Generate nilai acak yang masuk akal (antara 75 sampai 98)
                    $nilaiTugas = rand(80, 100);
                    $nilaiUts   = rand(80, 100);
                    $nilaiUas   = rand(80, 100);

                    $nilaiMapping = [
                        'tugas' => $nilaiTugas,
                        'uts'   => $nilaiUts,
                        'uas'   => $nilaiUas
                    ];

                    // Masukkan ke tabel pengumpulan
                    foreach ($kategoriList as $kat) {
                        Pengumpulan::create([
                            'uuid'         => (string) Str::uuid(),
                            'penilaian_id' => $mapPenilaian[$kat],
                            'mahasiswa_id' => $mahasiswaId,
                            'waktu_mulai'  => now(),
                            'waktu_selesai' => now(),
                            'nilai_total'  => $nilaiMapping[$kat],
                        ]);
                    }

                    // 4. Hitung Nilai Akhir Sesuai Bobot Persentase Kelas
                    $nilaiAkhir = round(
                        ($nilaiTugas * $kelas->persentase_tugas / 100) +
                            ($nilaiUts * $kelas->persentase_uts / 100) +
                            ($nilaiUas * $kelas->persentase_uas / 100),
                        2
                    );

                    // 5. Simpan langsung ke Rekap Nilai (Aman untuk KHS / Akademik)
                    RekapNilai::updateOrCreate(
                        [
                            'mahasiswa_id' => $mahasiswaId,
                            'kelas_id'     => $kelas->id,
                        ],
                        [
                            'semester_id'       => $kelas->semester_id,
                            'mata_kuliah_id'    => $kelas->mata_kuliah_id,
                            'total_tugas'       => $nilaiTugas,
                            'total_uts'         => $nilaiUts,
                            'total_uas'         => $nilaiUas,
                            'nilai_akhir_angka' => $nilaiAkhir,
                            'nilai_huruf'       => $this->getHuruf($nilaiAkhir),
                            'nilai_indeks'      => $this->getIndeks($nilaiAkhir),
                        ]
                    );
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $this->command->error("Gagal mengisi kelas ID {$kelas->id}: " . $e->getMessage());
            }
        }

        $this->command->info('Selesai! Seluruh nilai mahasiswa berhasil di-generate secara massal.');
    }

    // Helper Konversi Huruf sesuai rule aplikasi kamu
    private function getHuruf($nilai)
    {
        if ($nilai >= 90) return 'A';
        if ($nilai >= 86) return 'A-';
        if ($nilai >= 80) return 'B+';
        if ($nilai >= 76) return 'B';
        if ($nilai >= 70) return 'B-';
        if ($nilai >= 66) return 'C+';
        if ($nilai >= 60) return 'C';
        if ($nilai >= 55) return 'C-';
        if ($nilai >= 40) return 'D';
        return 'E';
    }

    // Helper Konversi Indeks sesuai rule aplikasi kamu
    private function getIndeks($nilai)
    {
        if ($nilai >= 90) return 4.0;
        if ($nilai >= 86) return 3.5;
        if ($nilai >= 80) return 3.25;
        if ($nilai >= 76) return 3.0;
        if ($nilai >= 70) return 2.75;
        if ($nilai >= 66) return 2.5;
        if ($nilai >= 60) return 2.0;
        if ($nilai >= 55) return 1.5;
        if ($nilai >= 40) return 1.0;
        return 0.0;
    }
}
