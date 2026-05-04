<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CumlaudeService
{
    // C+ dan C- sudah masuk "di bawah B-", jadi tidak valid
    private const NILAI_VALID = ['A', 'A-', 'B+', 'B', 'B-'];

    public function getDaftarCumlaude(array $filters = []): Collection
    {
        $query = DB::table('mahasiswas as m')
            ->join('users as u', 'u.id', '=', 'm.user_id')
            ->join('jurusans as j', 'j.id', '=', 'm.jurusan_id')
            ->where('m.status', 'lulus')
            ->whereIn('m.jenis_program', ['reguler', 'malam']);

        // --- FITUR FILTER BARU ---
        if (!empty($filters['jurusan_id'])) {
            $query->where('m.jurusan_id', $filters['jurusan_id']);
        }

        if (!empty($filters['angkatan'])) {
            $query->where('m.angkatan', $filters['angkatan']);
        }
        // -------------------------

        $kandidat = $query->select([
            'm.id as mahasiswa_id',
            'm.uuid',
            'm.nim',
            'm.angkatan',
            'u.nama_lengkap',
            'j.nama_jurusan',
        ])->get();

        return $kandidat
            ->filter(fn($mhs) => $this->cekLulusTepat($mhs->mahasiswa_id))
            ->filter(fn($mhs) => $this->cekTidakRekos($mhs->mahasiswa_id))
            ->filter(fn($mhs) => $this->cekNilaiMinimal($mhs->mahasiswa_id))
            ->map(fn($mhs)    => $this->enrichData($mhs))
            ->filter(fn($mhs) => $mhs->ipk >= 3.51)
            ->sortByDesc(fn($mhs) => [
                $mhs->ipk,
                $mhs->nilai_skripsi ?? 0,
                $mhs->total_nilai_a ?? 0,
            ])
            ->values();
    }

    // ---------------------------------------------------------------
    // Syarat 2: Lulus tepat waktu (≤ 8 semester)
    // ---------------------------------------------------------------
    private function cekLulusTepat(int $mahasiswaId): bool
    {
        $jumlahSemester = DB::table('anggota_kelases as ak')
            ->join('kelases as k', 'k.id', '=', 'ak.kelas_id')
            ->where('ak.mahasiswa_id', $mahasiswaId)
            ->distinct()
            ->count('k.semester_id');

        return $jumlahSemester <= 8;
    }

    // ---------------------------------------------------------------
    // Syarat 1: Tidak rekos
    // ---------------------------------------------------------------
    private function cekTidakRekos(int $mahasiswaId): bool
    {
        return ! DB::table('anggota_kelases as ak')
            ->join('kelases as k', 'k.id', '=', 'ak.kelas_id')
            ->where('ak.mahasiswa_id', $mahasiswaId)
            ->select('k.mata_kuliah_id')
            ->groupBy('k.mata_kuliah_id')
            ->havingRaw('COUNT(*) > 1')
            ->exists();
    }

    // ---------------------------------------------------------------
    // Syarat 4: Tidak ada nilai di bawah B-
    // ---------------------------------------------------------------
    private function cekNilaiMinimal(int $mahasiswaId): bool
    {
        // Sumber 1: nilai huruf langsung dari rekap_nilais
        $nilaiRekap = DB::table('rekap_nilais')
            ->where('mahasiswa_id', $mahasiswaId)
            ->pluck('nilai_huruf');

        // Sumber 2: konversi nilai_angka dari bimbingans → huruf
        $nilaiBimbingan = DB::table('bimbingans')
            ->where('mahasiswa_id', $mahasiswaId)
            ->where('status', 'approved')
            ->whereNotNull('nilai_angka')
            ->pluck('nilai_angka')
            ->map(fn($angka) => $this->konversiHuruf($angka));

        $semuaNilai = $nilaiRekap->merge($nilaiBimbingan);

        if ($semuaNilai->isEmpty()) {
            return false;
        }

        return $semuaNilai->every(
            fn($nilai) => in_array(trim($nilai), self::NILAI_VALID, true)
        );
    }

    // ---------------------------------------------------------------
    // Enrich: Hitung IPK, Total Nilai A, dan Ekstrak Skripsi
    // ---------------------------------------------------------------
    private function enrichData(object $mhs): object
    {
        // [PERBAIKAN]: Tambahkan select 'rn.nilai_huruf' untuk hitung jumlah A
        $rekapMK = DB::table('rekap_nilais as rn')
            ->join('mata_kuliahs as mk', 'mk.id', '=', 'rn.mata_kuliah_id')
            ->where('rn.mahasiswa_id', $mhs->mahasiswa_id)
            ->select(['rn.nilai_indeks', 'rn.nilai_huruf', 'mk.sks'])
            ->get();

        $rekapBimbingan = DB::table('bimbingans as b')
            ->join('mata_kuliahs as mk', 'mk.id', '=', 'b.mata_kuliah_id')
            ->where('b.mahasiswa_id', $mhs->mahasiswa_id)
            ->where('b.status', 'approved')
            ->whereNotNull('b.nilai_angka')
            ->select([
                'b.nilai_angka',
                'mk.sks',
                'mk.jenis_mk',
                'mk.nama_mk',
            ])
            ->get();

        $totalBobot = 0;
        $totalSks   = 0;
        $totalNilaiA = 0;

        // Iterasi rekap perkuliahan reguler
        foreach ($rekapMK as $r) {
            $totalBobot += $r->nilai_indeks * $r->sks;
            $totalSks   += $r->sks;

            // [PERBAIKAN]: Hitung total yang dapat nilai 'A'
            if (trim($r->nilai_huruf) === 'A') {
                $totalNilaiA++;
            }
        }

        // Iterasi rekap bimbingan (seperti skripsi/magang)
        foreach ($rekapBimbingan as $b) {
            $indeks = $this->konversiIndeks($b->nilai_angka);
            $huruf  = $this->konversiHuruf($b->nilai_angka);

            $totalBobot += $indeks * $b->sks;
            $totalSks   += $b->sks;

            // [PERBAIKAN]: Hitung total yang dapat nilai 'A'
            if ($huruf === 'A') {
                $totalNilaiA++;
            }
        }

        $mhs->ipk = $totalSks > 0 ? round($totalBobot / $totalSks, 2) : 0;
        $mhs->total_nilai_a = $totalNilaiA; // Simpan untuk Tiebreaker

        // Tiebreaker 1: cari MK Spesial dari bimbingans yang ada kata "skripsi"
        // Tiebreaker 1: cari MK Spesial dari bimbingans
        $nilaiSkripsi = $rekapBimbingan
            ->where('jenis_mk', 'Spesial')
            ->sortByDesc(fn($b) => [
                // Prioritas Utama (Nilai 2): Mengandung "skripsi" tapi BUKAN "pra" atau "proposal"
                (str_contains(strtolower($b->nama_mk), 'skripsi') &&
                    !str_contains(strtolower($b->nama_mk), 'pra') &&
                    !str_contains(strtolower($b->nama_mk), 'proposal')) ? 2 :

                    // Prioritas Kedua (Nilai 1): Mengandung kata "skripsi" (termasuk Pra Skripsi)
                    (str_contains(strtolower($b->nama_mk), 'skripsi') ? 1 : 0),

                // Terakhir baru bandingkan nilainya
                $b->nilai_angka,
            ])
            ->first();

        $mhs->nilai_skripsi   = $nilaiSkripsi?->nilai_angka;
        $mhs->nama_mk_skripsi = $nilaiSkripsi?->nama_mk;

        return $mhs;
    }

    // ---------------------------------------------------------------
    // Helper
    // ---------------------------------------------------------------
    private function konversiHuruf($n): string
    {
        if ($n >= 90) return 'A';
        if ($n >= 86) return 'A-';
        if ($n >= 80) return 'B+';
        if ($n >= 76) return 'B';
        if ($n >= 70) return 'B-';
        if ($n >= 66) return 'C+';
        if ($n >= 60) return 'C';
        if ($n >= 55) return 'C-';
        if ($n >= 40) return 'D';
        return 'E';
    }

    private function konversiIndeks($n): float
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
