<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\JawabanDetail;
use App\Models\Pengumpulan;
use App\Models\RekapNilai;
use App\Models\Penilaian;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KoreksiPenilaianController extends Controller
{
    public function save(Request $request, Kelas $kelas, Penilaian $penilaian)
    {
        $request->validate([
            'pengumpulan_id' => 'required|exists:pengumpulans,id',
            'jawaban' => 'required|array|min:1',
            'jawaban.*.id' => 'required|exists:jawaban_details,id',
            'jawaban.*.nilai_per_soal' => 'required|numeric|min:0|max:100',
        ]);

        $pengumpulanId = $request->pengumpulan_id;
        $jawabanData = collect($request->jawaban);

        try {
            DB::transaction(function () use ($pengumpulanId, $jawabanData) {
                // 1. UPDATE jawaban_details.nilai_per_soal
                foreach ($jawabanData as $jawaban) {
                    JawabanDetail::where('id', $jawaban['id'])
                        ->where('pengumpulan_id', $pengumpulanId)
                        ->update(['nilai_per_soal' => $jawaban['nilai_per_soal']]);
                }

                // 2. HITUNG ULANG pengumpulans.nilai_total
                $this->updatePengumpulanTotal($pengumpulanId);

                // 3. REGENERATE rekap_nilais
                $this->regenerateRekapNilai($pengumpulanId);
            });

            return back()->with('success', '✅ Skor berhasil disimpan & rekap nilai terupdate!');
        } catch (\Exception $e) {
            Log::error('Koreksi gagal: ' . $e->getMessage());
            return back()->with('error', '❌ Gagal menyimpan skor. Coba lagi.');
        }
    }

    /**
     * Hitung ulang nilai_total pengumpulan dari jawaban_details
     */
    private function updatePengumpulanTotal($pengumpulanId)
    {
        $pengumpulan = Pengumpulan::find($pengumpulanId);

        $data = JawabanDetail::select('jawaban_details.nilai_per_soal', 'pertanyaans.bobot_soal')
            ->join('pertanyaans', 'jawaban_details.pertanyaan_id', '=', 'pertanyaans.id')
            ->where('jawaban_details.pengumpulan_id', $pengumpulanId)
            ->get();

        if ($data->isEmpty()) {
            $nilaiTotal = 0;
        } else {
            $sumNilaiPerSoal = $data->sum('nilai_per_soal');
            $sumBobotSoal = $data->sum('bobot_soal');

            $nilaiTotal = $sumBobotSoal > 0
                ? round(($sumNilaiPerSoal / $sumBobotSoal) * 100, 2)
                : 0;
        }

        Pengumpulan::where('id', $pengumpulanId)->update([
            'nilai_total' => $nilaiTotal,
            'waktu_selesai' => now()
        ]);
    }

    /**
     * Regenerate rekap_nilais untuk mahasiswa di kelas
     * (SUDAH RATA‑RATA BERDASARKAN JUMLAH TUGAS DI KELAS)
     */
    private function regenerateRekapNilai($pengumpulanId)
    {
        $pengumpulan = Pengumpulan::with(['penilaian.kelas', 'mahasiswa'])->find($pengumpulanId);
        $mahasiswaId = $pengumpulan->mahasiswa_id;
        $kelasId = $pengumpulan->penilaian->kelas_id;
        $kelas = Kelas::find($kelasId);

        // 1. Semua pengumpulan mahasiswa di kelas ini
        $semuaPengumpulan = Pengumpulan::whereHas('penilaian', fn($q) => $q->where('kelas_id', $kelasId))
            ->where('mahasiswa_id', $mahasiswaId)
            ->get()
            ->groupBy('penilaian.kategori');

        // 2. Hitung total tugas di kelas (semua tugas, bukan yang sudah dikoreksi)
        $totalTugasDiKelas = Penilaian::where('kelas_id', $kelasId)
            ->where('kategori', 'tugas')
            ->count();

        // 3. Hitung total nilai tugas yang sudah ada
        $pengumpulanTugas = $semuaPengumpulan->get('tugas', collect());
        $sumNilaiTugas = $pengumpulanTugas->sum('nilai_total'); // misal 100

        // 4. total_tugas = (nilai tugas) / total jumlah tugas kelas
        $totalTugas = $totalTugasDiKelas > 0
            ? $sumNilaiTugas / $totalTugasDiKelas   // 100 / 3 → 33.33
            : 0;

        // 5. UTS & UAS tetap 1 item (0 kalau tidak ada)
        $totalUts = $this->hitungRataRataKategori($semuaPengumpulan, 'uts');
        $totalUas = $this->hitungRataRataKategori($semuaPengumpulan, 'uas');

        // 6. Hitung nilai akhir
        $nilaiAkhir = round(
            ($totalTugas * $kelas->persentase_tugas / 100) +
                ($totalUts * $kelas->persentase_uts / 100) +
                ($totalUas * $kelas->persentase_uas / 100),
            2
        );

        RekapNilai::updateOrCreate(
            [
                'mahasiswa_id' => $mahasiswaId,
                'kelas_id' => $kelasId
            ],
            [
                'semester_id' => $kelas->semester_id,
                'mata_kuliah_id' => $kelas->mata_kuliah_id,
                'total_tugas' => $totalTugas,
                'total_uts' => $totalUts,
                'total_uas' => $totalUas,
                'nilai_akhir_angka' => $nilaiAkhir,
                'nilai_huruf' => $this->konversiHuruf($nilaiAkhir),
                'nilai_indeks' => $this->konversiIndeks($nilaiAkhir),
            ]
        );
    }

    /**
     * Hitung nilai kategori: TUGAS=rata-rata, UTS/UAS=langsung nilai (1 item)
     */
    private function hitungRataRataKategori($semuaPengumpulan, $kategori)
    {
        $pengumpulanKategori = $semuaPengumpulan->get($kategori, collect());

        if ($pengumpulanKategori->isEmpty()) {
            return 0;
        }

        // TUGAS: rata-rata nilai_total (multiple)
        // UTS/UAS: langsung nilai_total (1 item)
        return round($pengumpulanKategori->avg('nilai_total'), 2);
    }

    private function konversiHuruf($nilai)
    {
        if ($nilai >= 90 && $nilai <= 100) return 'A';
        if ($nilai >= 86 && $nilai <= 89) return 'A-';
        if ($nilai >= 80 && $nilai <= 85) return 'B+';
        if ($nilai >= 76 && $nilai <= 79) return 'B';
        if ($nilai >= 70 && $nilai <= 75) return 'B-';
        if ($nilai >= 66 && $nilai <= 69) return 'C+';
        if ($nilai >= 60 && $nilai <= 65) return 'C';
        if ($nilai >= 56 && $nilai <= 59) return 'C-';
        if ($nilai >= 40 && $nilai <= 55) return 'D';
        return 'E';
    }

    private function konversiIndeks($nilai)
    {
        if ($nilai >= 90 && $nilai <= 100) return 4.0;
        if ($nilai >= 86 && $nilai <= 89) return 3.5;
        if ($nilai >= 80 && $nilai <= 85) return 3.25;
        if ($nilai >= 76 && $nilai <= 79) return 3.0;
        if ($nilai >= 70 && $nilai <= 75) return 2.75;
        if ($nilai >= 66 && $nilai <= 69) return 2.5;
        if ($nilai >= 60 && $nilai <= 65) return 2.0;
        if ($nilai >= 56 && $nilai <= 59) return 1.5;
        if ($nilai >= 40 && $nilai <= 55) return 1.0;
        return 0.0;
    }
}
