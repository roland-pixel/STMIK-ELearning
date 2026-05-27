<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use App\Models\Mahasiswa;
use App\Models\Semester;
use Illuminate\Support\Str;
use Inertia\Inertia;

class MahasiswaKHSController extends Controller
{
    public function index()
    {
        if (!Auth::check() || Auth::user()->peran !== 'mahasiswa') {
            abort(403, 'Akses ditolak.');
        }

        $mahasiswa = Auth::user()->mahasiswa;
        if (!$mahasiswa) {
            return Inertia::render('Mahasiswa/KHS/Index', [
                'error' => 'Data mahasiswa tidak ditemukan.'
            ]);
        }

        // OPTIMASI: Satukan pengecekan 'has_khs' langsung di dalam satu query database
        $semesters = DB::table('semesters as s')
            ->leftJoin('kelases as k', 'k.semester_id', '=', 's.id')
            ->leftJoin('anggota_kelases as ak', function ($join) use ($mahasiswa) {
                $join->on('ak.kelas_id', '=', 'k.id')
                    ->where('ak.mahasiswa_id', '=', $mahasiswa->id);
            })
            ->where(function ($query) use ($mahasiswa) {
                $query->whereNotNull('ak.id')
                    ->orWhereRaw("s.id IN (SELECT semester_id FROM rekap_nilais WHERE mahasiswa_id = ?)", [$mahasiswa->id])
                    ->orWhereRaw("s.id IN (SELECT semester_id FROM bimbingans WHERE mahasiswa_id = ? AND status = 'approved')", [$mahasiswa->id]);
            })
            ->select([
                's.id',
                's.nama_semester',
                's.status_aktif',
                's.tanggal_mulai',
                's.tanggal_selesai',
            ])
            // Subquery cek nilai umum
            ->selectRaw("EXISTS(SELECT 1 FROM rekap_nilais WHERE mahasiswa_id = ? AND semester_id = s.id) as has_umum", [$mahasiswa->id])
            // Subquery cek nilai spesial
            ->selectRaw("EXISTS(SELECT 1 FROM bimbingans WHERE mahasiswa_id = ? AND semester_id = s.id AND status = 'approved') as has_spesial", [$mahasiswa->id])
            ->distinct()
            ->orderBy('s.nama_semester', 'desc')
            ->get()
            ->map(function ($semester) {
                // Konversi hasil subquery murni menjadi boolean / flag display
                $semester->has_khs = (bool) $semester->has_umum || (bool) $semester->has_spesial;
                $semester->status_display = $semester->status_aktif === 'active' ? 'Aktif' : 'Arsip';
                return $semester;
            });

        return Inertia::render('Mahasiswa/KHS/Index', [
            'mahasiswa' => $mahasiswa->load('user', 'jurusan'),
            'semesters' => $semesters
        ]);
    }

    /**
     * Helper: Gabungkan Matkul Umum & Spesial (Gunakan Query Builder - Sudah Sangat Tepat)
     */
    private function getCombinedKHSData($mahasiswaId, $semesterId)
    {
        $mahasiswa = DB::table('mahasiswas')->where('id', $mahasiswaId)->first(['jurusan_id']);
        if (!$mahasiswa) return collect();

        $jurusanId = $mahasiswa->jurusan_id;

        // 1. Matkul Umum
        $umum = DB::table('rekap_nilais as r')
            ->join('mata_kuliahs as mk', 'r.mata_kuliah_id', '=', 'mk.id')
            ->leftJoin('kurikulums as k', function ($join) use ($jurusanId) {
                $join->on('mk.id', '=', 'k.mata_kuliah_id')
                    ->where('k.jurusan_id', '=', $jurusanId);
            })
            ->where('r.mahasiswa_id', $mahasiswaId)
            ->where('r.semester_id', $semesterId)
            ->select(
                DB::raw('COALESCE(k.kode_mk_jurusan, "N/A") as kode_tampil'),
                'mk.nama_mk',
                'mk.sks',
                'mk.jenis_mk',
                'r.total_tugas',
                'r.total_uts',
                'r.total_uas',
                'r.nilai_akhir_angka',
                'r.nilai_huruf',
                'r.nilai_indeks'
            );

        // 2. Matkul Spesial
        $spesial = DB::table('bimbingans as b')
            ->join('mata_kuliahs as mk', 'b.mata_kuliah_id', '=', 'mk.id')
            ->leftJoin('kurikulums as k', function ($join) use ($jurusanId) {
                $join->on('mk.id', '=', 'k.mata_kuliah_id')
                    ->where('k.jurusan_id', $jurusanId);
            })
            ->select([
                DB::raw('COALESCE(k.kode_mk_jurusan, "N/A") as kode_tampil'),
                'mk.nama_mk',
                'mk.sks',
                'mk.jenis_mk',
                DB::raw('NULL as total_tugas'),
                DB::raw('NULL as total_uts'),
                DB::raw('NULL as total_uas'),
                'b.nilai_angka as nilai_akhir_angka',
                DB::raw('NULL as nilai_huruf'),
                DB::raw('NULL as nilai_indeks'),
            ])
            ->where('b.mahasiswa_id', $mahasiswaId)
            ->where('b.semester_id', $semesterId)
            ->where('b.status', 'approved');

        // 3. Gabungkan dan urutkan
        return $umum->union($spesial)
            ->orderBy('nama_mk', 'asc')
            ->get()
            ->map(function ($item) {
                if ($item->jenis_mk === 'Spesial') {
                    $item->nilai_huruf = $this->konversiHuruf($item->nilai_akhir_angka);
                    $item->nilai_indeks = $this->konversiIndeks($item->nilai_akhir_angka);
                }
                return $item;
            });
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

    private function getKetuaJurusanDetail($namaJurusan)
    {
        $daftarKajur = [
            'Sistem Informasi' => [
                'nama' => 'Dr. Jane Smith, S.Kom, M.Kom',
                'nip'  => '19820301 201001 1 002'
            ],
            'Teknik Informatika' => [
                'nama' => 'Ahmad Riad, M.Kom',
                'nip'  => '19850412 201302 1 005'
            ],
            'Komputerisasi Akuntansi' => [
                'nama' => 'Dewi Lestari, S.E., M.M.',
                'nip'  => '19881123 201504 2 001'
            ],
            'Manajemen Informatika' => [
                'nama' => 'Budi Setiawan, M.T.',
                'nip'  => '19790105 200801 1 003'
            ],
        ];

        return $daftarKajur[$namaJurusan] ?? ['nama' => 'Belum Ditentukan', 'nip' => '-'];
    }

    public function previewKHS(Request $request)
    {
        if (!Auth::check() || Auth::user()->peran !== 'mahasiswa') abort(403);

        $request->validate(['semester_id' => 'required|exists:semesters,id']);

        $mahasiswa = Auth::user()->mahasiswa->load('user', 'jurusan');
        $semester = Semester::findOrFail($request->semester_id);

        $khsData = $this->getCombinedKHSData($mahasiswa->id, $request->semester_id);

        if ($khsData->isEmpty()) {
            return back()->with('warning', 'Belum ada data nilai untuk semester ini.');
        }

        $totalMutu = $khsData->sum(fn($item) => ($item->nilai_indeks ?? 0) * ($item->sks ?? 0));
        $totalSKS = $khsData->sum('sks') ?? 0;
        $ipk = $totalSKS > 0 ? round($totalMutu / $totalSKS, 2) : 0;

        $namaJurusan = $mahasiswa->jurusan->nama_jurusan ?? '-';
        $ketua_jurusan = $this->getKetuaJurusanDetail($namaJurusan);

        return Inertia::render('Mahasiswa/KHS/Index', [
            'mahasiswa' => $mahasiswa,
            'semester' => $semester,
            'khsData' => $khsData,
            'ipk' => $ipk,
            'totalSKS' => $totalSKS,
            'ketua_jurusan' => $ketua_jurusan,
            'isPreview' => true
        ]);
    }

    public function cetakKHS(Request $request)
    {
        if (!Auth::check() || Auth::user()->peran !== 'mahasiswa') abort(403);

        $request->validate(['semester_id' => 'required|exists:semesters,id']);

        $mahasiswa = Auth::user()->mahasiswa->load('user', 'jurusan');
        $semester = Semester::findOrFail($request->semester_id);

        $khsData = $this->getCombinedKHSData($mahasiswa->id, $request->semester_id);

        if ($khsData->isEmpty()) abort(404, 'Data KHS tidak ditemukan.');

        $totalMutu = $khsData->sum(fn($item) => ($item->nilai_indeks ?? 0) * ($item->sks ?? 0));
        $totalSKS = $khsData->sum('sks') ?? 0;
        $ipk = $totalSKS > 0 ? round($totalMutu / $totalSKS, 2) : 0;

        $namaJurusan = $mahasiswa->jurusan->nama_jurusan ?? '-';
        $ketua_jurusan = $this->getKetuaJurusanDetail($namaJurusan);

        $data = [
            'mahasiswa' => $mahasiswa,
            'semester' => $semester,
            'khs_data' => $khsData,
            'ipk' => $ipk,
            'total_sks' => $totalSKS,
            'tanggal_cetak' => now()->translatedFormat('d F Y'),
            'ketua_jurusan' => $ketua_jurusan
        ];

        $filename = "KHS-{$mahasiswa->nim}-" . Str::slug($semester->nama_semester) . "-" . now()->format('Ymd') . ".pdf";

        $pdf = PDF::loadView('admin.khs.cetak-khs', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'tempDir' => storage_path('app/public'),
                'chroot' => base_path(),
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
            ]);

        return $pdf->stream($filename);
    }
}
