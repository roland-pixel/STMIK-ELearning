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
        $total = JawabanDetail::where('pengumpulan_id', $pengumpulanId)
            ->sum('nilai_per_soal');

        Pengumpulan::where('id', $pengumpulanId)->update([
            'nilai_total' => $total,
            'waktu_selesai' => now() // tandai sudah dikoreksi
        ]);
    }

    /**
     * Regenerate rekap_nilais untuk mahasiswa di kelas
     */
    private function regenerateRekapNilai($pengumpulanId)
    {
        $pengumpulan = Pengumpulan::with(['penilaian.kelas', 'mahasiswa'])
            ->find($pengumpulanId);

        $mahasiswaId = $pengumpulan->mahasiswa_id;
        $kelasId = $pengumpulan->penilaian->kelas_id;
        $kelas = Kelas::find($kelasId);

        // Ambil SEMUA pengumpulan mahasiswa di kelas ini
        $semuaPengumpulan = Pengumpulan::whereHas('penilaian', fn($q) => $q->where('kelas_id', $kelasId))
            ->where('mahasiswa_id', $mahasiswaId)
            ->get()
            ->groupBy('penilaian.kategori');

        // Hitung total per kategori
        $totalTugas = $semuaPengumpulan->get('tugas', collect())->sum('nilai_total') ?? 0;
        $totalUts = $semuaPengumpulan->get('uts', collect())->sum('nilai_total') ?? 0;
        $totalUas = $semuaPengumpulan->get('uas', collect())->sum('nilai_total') ?? 0;

        // Hitung nilai akhir pakai persentase kelas
        $nilaiAkhir = round(
            ($totalTugas * $kelas->persentase_tugas / 100) +
                ($totalUts * $kelas->persentase_uts / 100) +
                ($totalUas * $kelas->persentase_uas / 100),
            2
        );

        // Update/insert rekap_nilais
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

    private function konversiHuruf($nilai)
    {
        if ($nilai >= 80) return 'A';
        if ($nilai >= 70) return 'B';
        if ($nilai >= 60) return 'C';
        if ($nilai >= 50) return 'D';
        return 'E';
    }

    private function konversiIndeks($nilai)
    {
        if ($nilai >= 80) return 4.0;
        if ($nilai >= 70) return 3.0;
        if ($nilai >= 60) return 2.5;
        if ($nilai >= 50) return 2.0;
        return 0.0;
    }
}
