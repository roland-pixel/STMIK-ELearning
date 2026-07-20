<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Penilaian;
use App\Models\Kelas;
use App\Models\Pengumpulan;
use App\Models\RekapNilai;
use Carbon\Carbon;

class ClosePenilaianDanRekap extends Command
{
    // Jalankan via: php artisan penilaian:close-expired
    protected $signature = 'penilaian:close-expired';
    protected $description = 'Otomatis hitung rekap nilai mahasiswa alpa setelah tenggat waktu penilaian ONLINE berakhir';

    public function handle()
    {
        $now = Carbon::now('Asia/Jakarta');

        // Otomatis cari tugas online yang sudah lewat deadline DAN belum pernah diproses
        $expiredPenilaians = Penilaian::where('mode_penilaian', 'online')
            ->whereNotNull('tenggat_waktu')
            ->where('tenggat_waktu', '<=', $now)
            ->where('is_processed', false) // Kuncinya di sini!
            ->get();

        if ($expiredPenilaians->isEmpty()) {
            return 0; // Kalau gak ada yang perlu diproses, sistem santai (tidak membebani server)
        }

        foreach ($expiredPenilaians as $penilaian) {
            $kelas = $penilaian->kelas;
            if (!$kelas) continue;

            $mahasiswaIds = \DB::table('anggota_kelases')
                ->where('kelas_id', $kelas->id)
                ->pluck('mahasiswa_id')
                ->toArray();

            foreach ($mahasiswaIds as $mahasiswaId) {
                $this->forceRegenerateRekap($kelas, $mahasiswaId);
            }

            // TANDAI SUDAH DIPROSES: 
            // Biar scheduler menit berikutnya tidak memproses data yang sama lagi
            $penilaian->update(['is_processed' => true]);

            $this->info("Nilai untuk tugas '{$penilaian->judul}' berhasil di-closing otomatis.");
        }

        return 0;
    }

    /**
     * CORE ENGINE CALCULATOR (ADIL MUTLAK)
     */
    private function forceRegenerateRekap(Kelas $kelas, $mahasiswaId)
    {
        // 1. Ambil skema seluruh item penilaian (online & manual) yang diterbitkan di kelas ini
        $allPenilaians = Penilaian::where('kelas_id', $kelas->id)->get();

        // 2. Ambil data pengumpulan riil milik mahasiswa terkait di kelas ini
        $riwayatPengumpulan = Pengumpulan::whereIn('penilaian_id', $allPenilaians->pluck('id'))
            ->where('mahasiswa_id', $mahasiswaId)
            ->get()
            ->keyBy('penilaian_id');

        $komponenNilai = ['tugas' => 0, 'uts' => 0, 'uas' => 0];

        foreach (array_keys($komponenNilai) as $kategori) {
            $daftarPenilaianKategori = $allPenilaians->where('kategori', $kategori);
            $totalMataPenilaian = $daftarPenilaianKategori->count();

            if ($totalMataPenilaian > 0) {
                $accumulatedNilai = 0;

                foreach ($daftarPenilaianKategori as $p) {
                    $matchRecord = $riwayatPengumpulan->get($p->id);

                    if ($matchRecord) {
                        $accumulatedNilai += $matchRecord->nilai_total;
                    } else {
                        // Jika tidak ada data pengumpulan, mutlak dianggap 0 (Karena tenggat online sudah berakhir)
                        $accumulatedNilai += 0;
                    }
                }
                // Pembagi wajib menggunakan total instrumen evaluasi kelas resmi ($totalMataPenilaian)
                $komponenNilai[$kategori] = round($accumulatedNilai / $totalMataPenilaian, 2);
            }
        }

        // 3. Kalkulasi Nilai Akhir Angka berdasarkan pembobotan persentase kelas
        $nilaiAkhir = round(
            ($komponenNilai['tugas'] * $kelas->persentase_tugas / 100) +
                ($komponenNilai['uts'] * $kelas->persentase_uts / 100) +
                ($komponenNilai['uas'] * $kelas->persentase_uas / 100),
            2
        );

        // 4. Update or Create data rekap unik per kelas
        RekapNilai::updateOrCreate(
            [
                'mahasiswa_id' => $mahasiswaId,
                'kelas_id'     => $kelas->id
            ],
            [
                'semester_id'       => $kelas->semester_id,
                'mata_kuliah_id'    => $kelas->mata_kuliah_id,
                'total_tugas'       => $komponenNilai['tugas'],
                'total_uts'         => $komponenNilai['uts'],
                'total_uas'         => $komponenNilai['uas'],
                'nilai_akhir_angka' => $nilaiAkhir,
                'nilai_huruf'       => $this->konversiHuruf($nilaiAkhir),
                'nilai_indeks'      => $this->konversiIndeks($nilaiAkhir),
            ]
        );
    }

    private function konversiHuruf($nilai)
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

    private function konversiIndeks($nilai)
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
