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
                foreach ($jawabanData as $jawaban) {
                    JawabanDetail::where('id', $jawaban['id'])
                        ->where('pengumpulan_id', $pengumpulanId)
                        ->update([
                            'nilai_per_soal' => $jawaban['nilai_per_soal']
                        ]);
                }

                $this->updatePengumpulanTotal($pengumpulanId);
                $this->regenerateRekapNilai($pengumpulanId);
            });

            return back()->with('success', '✅ Skor berhasil disimpan & rekap nilai terupdate!');
        } catch (\Exception $e) {
            Log::error('Koreksi gagal: ' . $e->getMessage());
            return back()->with('error', '❌ Gagal menyimpan skor. Coba lagi.');
        }
    }

    private function updatePengumpulanTotal($pengumpulanId)
    {
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

    private function regenerateRekapNilai($pengumpulanId)
    {
        $pengumpulan = Pengumpulan::with(['penilaian.kelas'])->find($pengumpulanId);
        if (!$pengumpulan) return;

        $kelas = $pengumpulan->penilaian->kelas;
        $kelasId = $kelas->id;

        $totalTugasDiKelas = Penilaian::where('kelas_id', $kelasId)->where('kategori', 'tugas')->count();
        $totalUtsDiKelas   = Penilaian::where('kelas_id', $kelasId)->where('kategori', 'uts')->count();
        $totalUasDiKelas   = Penilaian::where('kelas_id', $kelasId)->where('kategori', 'uas')->count();

        $allPengumpulan = Pengumpulan::whereHas('penilaian', fn($q) => $q->where('kelas_id', $kelasId))
            ->with('penilaian')
            ->get()
            ->groupBy('mahasiswa_id');

        $daftarMahasiswaId = DB::table('anggota_kelases')
            ->where('kelas_id', $kelasId)
            ->pluck('mahasiswa_id');

        foreach ($daftarMahasiswaId as $mId) {
            $pengumpulanMhs = $allPengumpulan->get($mId, collect())->groupBy('penilaian.kategori');

            $sumNilaiTugas = $pengumpulanMhs->get('tugas', collect())->sum('nilai_total');
            $totalTugas = $totalTugasDiKelas > 0 ? round($sumNilaiTugas / $totalTugasDiKelas, 2) : 0;

            $sumNilaiUts = $pengumpulanMhs->get('uts', collect())->sum('nilai_total');
            $totalUts = $totalUtsDiKelas > 0 ? round($sumNilaiUts / $totalUtsDiKelas, 2) : 0;

            $sumNilaiUas = $pengumpulanMhs->get('uas', collect())->sum('nilai_total');
            $totalUas = $totalUasDiKelas > 0 ? round($sumNilaiUas / $totalUasDiKelas, 2) : 0;

            $nilaiAkhir = round(
                ($totalTugas * $kelas->persentase_tugas / 100) +
                ($totalUts * $kelas->persentase_uts / 100) +
                ($totalUas * $kelas->persentase_uas / 100),
                2
            );

            $payload = [
                'semester_id'       => $kelas->semester_id,
                'mata_kuliah_id'    => $kelas->mata_kuliah_id,
                'total_tugas'       => $totalTugas,
                'total_uts'         => $totalUts,
                'total_uas'         => $totalUas,
                'nilai_akhir_angka' => $nilaiAkhir,
                'nilai_huruf'       => $this->konversiHuruf($nilaiAkhir),
                'nilai_indeks'      => $this->konversiIndeks($nilaiAkhir),
            ];

            RekapNilai::updateOrCreate(
                [
                    'mahasiswa_id' => $mId,
                    'kelas_id'     => $kelasId
                ],
                $payload
            );
        }
    }

    private function hitungRataRataKategori($semuaPengumpulan, $kategori)
    {
        $pengumpulanKategori = $semuaPengumpulan->get($kategori, collect());
        return $pengumpulanKategori->isEmpty() ? 0 : round($pengumpulanKategori->avg('nilai_total'), 2);
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