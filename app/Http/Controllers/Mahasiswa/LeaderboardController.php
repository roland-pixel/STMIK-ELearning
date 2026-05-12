<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class LeaderboardController extends Controller
{
    // Daftar nilai yang dianggap valid untuk syarat Cumlaude
    private const NILAI_VALID = ['A', 'A-', 'B+', 'B', 'B-'];

    public function leaderboard()
    {
        $mhs = auth()->user()->mahasiswa;

        // 1. Ambil semua mahasiswa di jurusan & angkatan yang sama
        $students = Mahasiswa::with('user')
            ->where('jurusan_id', $mhs->jurusan_id)
            ->where('angkatan', $mhs->angkatan)
            ->whereIn('jenis_program', ['reguler', 'malam'])
            ->get();

        // 2. Filter & Hitung data (Logika Cumlaude)
        $leaderboardData = $students->filter(function ($student) {

            // SYARAT 1: TIDAK BOLEH REKOS (MENGULANG)
            // Jika TRUE (artinya ketemu data mengulang), maka return FALSE (buang mahasiswa ini)
            if ($this->isMahasiswaMengulang($student->id)) return false;

            // SYARAT 2: TEPAT WAKTU (Maksimal 8 semester)
            if (!$this->cekLulusTepat($student->id)) return false;

            // SYARAT 3: NILAI MINIMAL (Tidak ada nilai di bawah B-)
            if (!$this->cekNilaiMinimal($student->id)) return false;

            return true;
        })
            ->map(function ($student) {
                // Syarat 4: Hitung IPK & Detail untuk Tiebreaker
                $metrics = $this->calculateAcademicMetrics($student->id);

                return [
                    'id'            => $student->id,
                    'nim'           => $student->nim,
                    'nama_lengkap'  => $student->user->nama_lengkap,
                    'total_sks'     => $metrics['totalSks'],
                    'ipk'           => $metrics['ipk'],
                    'total_nilai_a' => $metrics['totalNilaiA'],
                    'nilai_skripsi' => $metrics['nilaiSkripsi'],
                ];
            })
            // Filter IPK Cumlaude (Standar 3.51)
            ->filter(fn($item) => $item['ipk'] >= 3.51)
            // 3. Sorting Multi-Kolom (Tiebreaker)
            ->sort(function ($a, $b) {
                if ($b['ipk'] !== $a['ipk']) return $b['ipk'] <=> $a['ipk'];
                if ($b['nilai_skripsi'] !== $a['nilai_skripsi']) return $b['nilai_skripsi'] <=> $a['nilai_skripsi'];
                return $b['total_nilai_a'] <=> $a['total_nilai_a'];
            })
            ->values();

        // 4. Ambil Top 10 & Posisi User
        $top10     = $leaderboardData->take(10);
        $myRankIndex = $leaderboardData->search(fn($item) => $item['id'] === $mhs->id);
        $myRank    = $myRankIndex !== false ? $myRankIndex + 1 : null;
        $isInTop10 = $top10->contains('id', $mhs->id);
        $myData    = $leaderboardData->firstWhere('id', $mhs->id);

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
     * Mengecek apakah mahasiswa memiliki mata kuliah yang diambil lebih dari 1 kali.
     * Return TRUE jika mahasiswa MENGULANG (Rekos).
     */
    private function isMahasiswaMengulang(int $mahasiswaId): bool
    {
        return DB::table('anggota_kelases as ak')
            ->join('kelases as k', 'k.id', '=', 'ak.kelas_id')
            ->where('ak.mahasiswa_id', $mahasiswaId)
            ->select('k.mata_kuliah_id')
            ->groupBy('k.mata_kuliah_id')
            ->havingRaw('COUNT(*) > 1')
            ->exists();
    }

    private function cekLulusTepat(int $mahasiswaId): bool
    {
        return DB::table('anggota_kelases as ak')
            ->join('kelases as k', 'k.id', '=', 'ak.kelas_id')
            ->where('ak.mahasiswa_id', $mahasiswaId)
            ->distinct()
            ->count('k.semester_id') <= 8;
    }

    private function cekNilaiMinimal(int $mahasiswaId): bool
    {
        $nilaiRekap = DB::table('rekap_nilais')->where('mahasiswa_id', $mahasiswaId)->pluck('nilai_huruf');
        $nilaiBim = DB::table('bimbingans')->where('mahasiswa_id', $mahasiswaId)
            ->where('status', 'approved')->whereNotNull('nilai_angka')->pluck('nilai_angka')
            ->map(fn($n) => $this->konversiHuruf($n));

        $gabung = $nilaiRekap->merge($nilaiBim);
        if ($gabung->isEmpty()) return false;

        return $gabung->every(fn($n) => in_array(trim($n), self::NILAI_VALID));
    }

    private function calculateAcademicMetrics(int $mahasiswaId): array
    {
        return Cache::remember("academic_metrics_lb_{$mahasiswaId}", 300, function () use ($mahasiswaId) {
            // Ambil Nilai Reguler
            $rekapMK = DB::table('rekap_nilais as rn')
                ->join('mata_kuliahs as mk', 'mk.id', '=', 'rn.mata_kuliah_id')
                ->where('rn.mahasiswa_id', $mahasiswaId)
                ->select(['rn.nilai_indeks', 'rn.nilai_huruf', 'mk.sks'])
                ->get();

            // Ambil Nilai Bimbingan
            $rekapBimbingan = DB::table('bimbingans as b')
                ->join('mata_kuliahs as mk', 'mk.id', '=', 'b.mata_kuliah_id')
                ->where('b.mahasiswa_id', $mahasiswaId)
                ->where('b.status', 'approved')
                ->whereNotNull('b.nilai_angka')
                ->select(['b.nilai_angka', 'mk.sks', 'mk.nama_mk'])
                ->get();

            $totalBobot = 0;
            $totalSks = 0;
            $totalA = 0;

            foreach ($rekapMK as $r) {
                $totalBobot += ($r->nilai_indeks * $r->sks);
                $totalSks   += $r->sks;
                if (trim($r->nilai_huruf) === 'A') $totalA++;
            }

            foreach ($rekapBimbingan as $b) {
                $indeks = $this->konversiIndeks($b->nilai_angka);
                $totalBobot += ($indeks * $b->sks);
                $totalSks   += $b->sks;
                if ($this->konversiHuruf($b->nilai_angka) === 'A') $totalA++;
            }

            $skripsi = $rekapBimbingan->filter(
                fn($b) =>
                str_contains(strtolower($b->nama_mk), 'skripsi') &&
                    !str_contains(strtolower($b->nama_mk), 'pra')
            )->first();

            return [
                'totalSks'     => $totalSks,
                'ipk'          => $totalSks > 0 ? round($totalBobot / $totalSks, 2) : 0,
                'totalNilaiA'  => $totalA,
                'nilaiSkripsi' => $skripsi?->nilai_angka ?? 0,
            ];
        });
    }

    private function konversiHuruf($n): string
    {
        if ($n >= 90) return 'A';
        if ($n >= 86) return 'A-';
        if ($n >= 80) return 'B+';
        if ($n >= 76) return 'B';
        if ($n >= 70) return 'B-';
        return 'C';
    }

    private function konversiIndeks($n): float
    {
        if ($n >= 90) return 4.0;
        if ($n >= 86) return 3.5;
        if ($n >= 80) return 3.25;
        if ($n >= 76) return 3.0;
        if ($n >= 70) return 2.75;
        return 2.0;
    }
}
