<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class LeaderboardController extends Controller
{
    public function leaderboard()
    {
        $mhs = auth()->user()->mahasiswa;

        // 1. Ambil semua mahasiswa di jurusan & angkatan yang sama
        $students = Mahasiswa::with('user')
            ->where('jurusan_id', $mhs->jurusan_id)
            ->where('angkatan', $mhs->angkatan)
            ->get();

        // 2. Hitung data akademik tiap mahasiswa
        $leaderboardData = $students->map(function ($student) {
            $transcript = $this->calculateAcademicMetrics($student->id);

            return [
                'id'           => $student->id,
                'nim'          => $student->nim,
                'nama_lengkap' => $student->user->nama_lengkap,
                'total_sks'    => $transcript['totalSks'],
                'total_kredit' => $transcript['totalKredit'],
                'ipk'          => $transcript['ipk'],
            ];
        })
            // 3. Sort multi-kolom
            ->sort(function ($a, $b) {
                if ($b['ipk'] !== $a['ipk']) {
                    return $b['ipk'] <=> $a['ipk'];
                }
                if ($b['total_kredit'] !== $a['total_kredit']) {
                    return $b['total_kredit'] <=> $a['total_kredit'];
                }
                return $b['total_sks'] <=> $a['total_sks'];
            })
            ->values();

        // 4. Ambil Top 10 & posisi user saat ini
        $top10     = $leaderboardData->take(10);
        $myRank    = $leaderboardData->search(fn($item) => $item['id'] === $mhs->id) + 1;
        $isInTop10 = $top10->contains('id', $mhs->id);
        $myData    = $leaderboardData->firstWhere('id', $mhs->id);

        // 5. Return Inertia Render
        return Inertia::render('Mahasiswa/Leaderboard/Index', [
            'leaderboard' => [
                'top10'     => $top10,
                'myRank'    => $myRank,
                'isInTop10' => $isInTop10,
                'myData'    => $myData,
            ],
            'auth_mhs' => [
                'angkatan' => $mhs->angkatan,
                'jurusan'  => $mhs->jurusan->nama_jurusan
            ]
        ]);
    }

    /**
     * Hitung IPK, Total SKS, dan Total Kredit seorang mahasiswa.
     *
     * LOGIKA BIMBINGAN:
     * - PKL / Pra Skripsi / Skripsi yang nilai_angka masih NULL → SKIP
     * (belum selesai, tidak boleh merusak IPK dan tidak dihitung SKS-nya)
     * - Hanya yang status='approved' DAN nilai_angka IS NOT NULL yang masuk hitungan
     *
     * Di-cache 5 menit agar tidak N+1 query saat load leaderboard
     */
    private function calculateAcademicMetrics(int $mahasiswaId): array
    {
        return Cache::remember("academic_metrics_{$mahasiswaId}", 300, function () use ($mahasiswaId) {

            // Ambil data jurusan mahasiswa untuk mengambil kode kurikulum
            $mahasiswa = Mahasiswa::find($mahasiswaId);
            $jurusanId = $mahasiswa->jurusan_id;

            // --- Sumber 1: Nilai dari kelas reguler (rekap_nilais) ---
            $regularQuery = DB::table('rekap_nilais as r')
                ->join('mata_kuliahs as mk', 'r.mata_kuliah_id', '=', 'mk.id')
                ->join('kurikulums as k', function ($join) use ($jurusanId) {
                    $join->on('k.mata_kuliah_id', '=', 'mk.id')
                        ->where('k.jurusan_id', $jurusanId);
                })
                ->where('r.mahasiswa_id', $mahasiswaId)
                ->select(
                    'k.kode_mk_jurusan as kode_mk', // Mengambil kode dari tabel kurikulum
                    'mk.sks',
                    'r.nilai_akhir_angka'
                );

            // --- Sumber 2: Nilai bimbingan (PKL, Pra Skripsi, Skripsi) ---
            $bimbinganQuery = DB::table('bimbingans as b')
                ->join('mata_kuliahs as mk', 'b.mata_kuliah_id', '=', 'mk.id')
                ->join('kurikulums as k', function ($join) use ($jurusanId) {
                    $join->on('k.mata_kuliah_id', '=', 'mk.id')
                        ->where('k.jurusan_id', $jurusanId);
                })
                ->where('b.mahasiswa_id', $mahasiswaId)
                ->where('b.status', 'approved')
                ->whereNotNull('b.nilai_angka')
                ->select(
                    'k.kode_mk_jurusan as kode_mk', // Mengambil kode dari tabel kurikulum
                    'mk.sks',
                    'b.nilai_angka as nilai_akhir_angka'
                );

            // --- Gabungkan keduanya dengan UNION ---
            $unionQuery = $regularQuery->union($bimbinganQuery);

            // --- Ambil nilai terbaik per mata kuliah (handle kasus retake/mengulang) ---
            $data = DB::table(DB::raw("({$unionQuery->toSql()}) as combined"))
                ->mergeBindings($unionQuery)
                ->select(
                    'kode_mk',
                    'sks',
                    DB::raw('MAX(nilai_akhir_angka) as nilai_angka')
                )
                ->groupBy('kode_mk', 'sks')
                ->get();

            // --- Hitung total SKS dan total kredit ---
            $totalSks    = 0;
            $totalKredit = 0.0;

            foreach ($data as $item) {
                if (is_null($item->nilai_angka)) {
                    continue;
                }

                $indeks       = $this->konversiIndeks((float) $item->nilai_angka);
                $totalSks    += $item->sks;
                $totalKredit += ($item->sks * $indeks);
            }

            return [
                'totalSks'    => $totalSks,
                'totalKredit' => $totalKredit,
                'ipk'         => $totalSks > 0
                    ? round($totalKredit / $totalSks, 2)
                    : 0.0,
            ];
        });
    }

    /**
     * Konversi nilai angka (0-100) ke indeks mutu (0.0 - 4.0)
     * Sesuaikan range ini dengan kebijakan kampus
     */
    private function konversiIndeks(float $n): float
    {
        if ($n >= 90) return 4.0;
        if ($n >= 86) return 3.5;
        if ($n >= 80) return 3.25;
        if ($n >= 76) return 3.0;
        if ($n >= 70) return 2.75;
        if ($n >= 66) return 2.5;
        if ($n >= 60) return 2.0;
        if ($n >= 55) return 1.5;
        if ($n >= 40) return 1.0;
        return 0.0;
    }
}
