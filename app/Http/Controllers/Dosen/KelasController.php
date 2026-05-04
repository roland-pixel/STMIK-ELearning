<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Pengumpulan;
use App\Models\Penilaian;
use App\Models\RekapNilai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class KelasController extends Controller
{
    public function updateSettings(Request $request, Kelas $kelas)
    {
        // Pastikan dosen cuma bisa ubah kelas yang dia ampu
        $user = $request->user();
        $dosenId = $user->dosen?->id;

        if (!$dosenId || (int) $kelas->dosen_id !== (int) $dosenId) {
            abort(403);
        }

        $validated = $request->validate([
            'nama_kelas' => ['required', 'string', 'max:255'],
            'kode_gabung' => [
                'required',
                'string',
                'max:50',
                Rule::unique('kelases', 'kode_gabung')->ignore($kelas->id),
            ],
            'deskripsi' => ['nullable', 'string', 'max:2000'],

            'persentase_tugas' => ['required', 'integer', 'min:0', 'max:100'],
            'persentase_uts'   => ['required', 'integer', 'min:0', 'max:100'],
            'persentase_uas'   => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $total = (int) $validated['persentase_tugas']
            + (int) $validated['persentase_uts']
            + (int) $validated['persentase_uas'];

        if ($total !== 100) {
            return back()->withErrors([
                'persentase_tugas' => 'Total persentase (Tugas + UTS + UAS) harus 100.',
            ]);
        }

        try {
            DB::transaction(function () use ($kelas, $validated) {
                // 1. UPDATE pengaturan kelas
                $kelas->update($validated);

                // 🔥 2. REGENERATE SEMUA REKAP NILAI di kelas ini
                $this->regenerateAllRekapNilaiKelas($kelas->id);
            });

            return back()->with('success', '✅ Pengaturan kelas & rekap nilai berhasil diperbarui!');
        } catch (\Exception $e) {
            Log::error('Update kelas gagal: ' . $e->getMessage());
            return back()->with('error', '❌ Gagal memperbarui pengaturan. Coba lagi.');
        }
    }

    /**
     * 🔥 REGENERATE SEMUA rekap_nilais untuk SELURUH mahasiswa di kelas
     */
    private function regenerateAllRekapNilaiKelas($kelasId)
    {
        $kelas = Kelas::find($kelasId);
        if (!$kelas) return;

        // Ambil SEMUA mahasiswa di kelas
        $mahasiswaIds = $kelas->anggotaKelases()->pluck('mahasiswa_id');

        foreach ($mahasiswaIds as $mahasiswaId) {
            $this->regenerateRekapNilaiForMahasiswa($kelasId, $mahasiswaId);
        }
    }

    /**
     * Regenerate rekap_nilai untuk 1 mahasiswa di kelas
     * (Logika baru: Simpan unik per kelas agar riwayat setiap semester aman)
     */
    private function regenerateRekapNilaiForMahasiswa($kelasId, $mahasiswaId)
    {
        $kelas = Kelas::find($kelasId);
        if (!$kelas) return;

        // 1. Hitung ulang semua komponen nilai di KELAS INI
        $semuaPengumpulan = Pengumpulan::whereHas('penilaian', fn($q) => $q->where('kelas_id', $kelasId))
            ->where('mahasiswa_id', $mahasiswaId)
            ->with('penilaian')
            ->get()
            ->groupBy('penilaian.kategori');

        $totalTugasDiKelas = Penilaian::where('kelas_id', $kelasId)
            ->where('kategori', 'tugas')
            ->count();

        $pengumpulanTugas = $semuaPengumpulan->get('tugas', collect());
        $sumNilaiTugas = $pengumpulanTugas->sum('nilai_total');
        $totalTugas = $totalTugasDiKelas > 0 ? round($sumNilaiTugas / $totalTugasDiKelas, 2) : 0;

        $totalUts = $this->hitungRataRataKategori($semuaPengumpulan, 'uts');
        $totalUas = $this->hitungRataRataKategori($semuaPengumpulan, 'uas');

        // 2. Hitung Nilai Akhir dengan bobot terbaru
        $nilaiAkhir = round(
            ($totalTugas * $kelas->persentase_tugas / 100) +
                ($totalUts * $kelas->persentase_uts / 100) +
                ($totalUas * $kelas->persentase_uas / 100),
            2
        );

        // 3. 🔥 SIMPAN/UPDATE UNIK PER KELAS
        // Tidak perlu lagi membandingkan dengan nilai kelas lain.
        // Dengan updateOrCreate berdasarkan mahasiswa_id dan kelas_id, 
        // nilai kelas ini akan selalu terupdate dengan bobot baru, 
        // tanpa menyentuh nilai di kelas/semester lain.
        RekapNilai::updateOrCreate(
            [
                'mahasiswa_id' => $mahasiswaId,
                'kelas_id'     => $kelasId
            ],
            [
                'semester_id'       => $kelas->semester_id,
                'mata_kuliah_id'    => $kelas->mata_kuliah_id,
                'total_tugas'       => $totalTugas,
                'total_uts'         => $totalUts,
                'total_uas'         => $totalUas,
                'nilai_akhir_angka' => $nilaiAkhir,
                'nilai_huruf'       => $this->konversiHuruf($nilaiAkhir),
                'nilai_indeks'      => $this->konversiIndeks($nilaiAkhir),
            ]
        );
    }

    /**
     * Hitung rata-rata kategori
     */
    private function hitungRataRataKategori($semuaPengumpulan, $kategori)
    {
        $pengumpulanKategori = $semuaPengumpulan->get($kategori, collect());
        return $pengumpulanKategori->isEmpty() ? 0 : round($pengumpulanKategori->avg('nilai_total'), 2);
    }

    /**
     * Konversi nilai ke huruf
     */
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

    /**
     * Konversi nilai ke indeks
     */
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
