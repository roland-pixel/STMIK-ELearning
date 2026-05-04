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

        // PERBAIKAN: Hanya tampilkan semester di mana mahasiswa 
        // TERDAFTAR di setidaknya satu kelas atau memiliki rekap nilai.
        $semesters = Semester::where(function ($query) use ($mahasiswa) {
            $query->whereHas('kelases.anggotaKelases', fn($q) => $q->where('mahasiswa_id', $mahasiswa->id))
                ->orWhereRaw("id IN (SELECT semester_id FROM rekap_nilais WHERE mahasiswa_id = ?)", [$mahasiswa->id])
                ->orWhereRaw("id IN (SELECT semester_id FROM bimbingans WHERE mahasiswa_id = ? AND status = 'approved')", [$mahasiswa->id]);
        })
            ->orderBy('nama_semester', 'desc')
            ->get()
            ->map(function ($semester) use ($mahasiswa) {
                // Cek data KHS untuk tampilan UI
                $hasUmum = DB::table('rekap_nilais')
                    ->where('mahasiswa_id', $mahasiswa->id)
                    ->where('semester_id', $semester->id)
                    ->exists();

                $hasSpesial = DB::table('bimbingans')
                    ->where('mahasiswa_id', $mahasiswa->id)
                    ->where('semester_id', $semester->id)
                    ->where('status', 'approved')
                    ->exists();

                $semester->has_khs = $hasUmum || $hasSpesial;
                $semester->status_display = $semester->status_aktif === 'active' ? 'Aktif' : 'Arsip';
                return $semester;
            });

        return Inertia::render('Mahasiswa/KHS/Index', [
            'mahasiswa' => $mahasiswa->load('user', 'jurusan'),
            'semesters' => $semesters
        ]);
    }

    /**
     * Helper: Gabungkan Matkul Umum & Spesial (Logika sama dengan Admin)
     */
    private function getCombinedKHSData($mahasiswaId, $semesterId)
    {
        $mahasiswa = \App\Models\Mahasiswa::find($mahasiswaId);
        $jurusanId = $mahasiswa->jurusan_id;

        // 1. Matkul Umum (Menggunakan leftJoin ke kurikulums)
        $umum = DB::table('rekap_nilais as r')
            ->join('mata_kuliahs as mk', 'r.mata_kuliah_id', '=', 'mk.id')
            ->leftJoin('kurikulums as k', function ($join) use ($jurusanId) {
                $join->on('mk.id', '=', 'k.mata_kuliah_id')
                    ->where('k.jurusan_id', '=', $jurusanId);
            })
            ->where('r.mahasiswa_id', $mahasiswaId)
            ->where('r.semester_id', $semesterId)
            ->select(
                DB::raw('COALESCE(k.kode_mk_jurusan, "N/A") as kode_tampil'), // Menggantikan mk.kode_mk agar konsisten dengan admin
                'mk.nama_mk',
                'mk.sks',
                'mk.jenis_mk',
                'r.nilai_akhir_angka',
                'r.nilai_huruf',
                'r.nilai_indeks',
                'r.total_tugas',
                'r.total_uts',
                'r.total_uas'
            );

        // 2. Matkul Spesial (Menggunakan leftJoin ke kurikulums)
        $spesial = DB::table('bimbingans as b')
            ->join('mata_kuliahs as mk', 'b.mata_kuliah_id', '=', 'mk.id')
            ->leftJoin('kurikulums as k', function ($join) use ($jurusanId) {
                $join->on('mk.id', '=', 'k.mata_kuliah_id')
                    ->where('k.jurusan_id', '=', $jurusanId);
            })
            ->where('b.mahasiswa_id', $mahasiswaId)
            ->where('b.semester_id', $semesterId)
            ->where('b.status', 'approved')
            ->select(
                DB::raw('COALESCE(k.kode_mk_jurusan, "N/A") as kode_tampil'), // Menggantikan mk.kode_mk agar konsisten dengan admin
                'mk.nama_mk',
                'mk.sks',
                'mk.jenis_mk',
                'b.nilai_angka as nilai_akhir_angka',
                DB::raw("NULL as nilai_huruf"),
                DB::raw("NULL as nilai_indeks"),
                DB::raw("0 as total_tugas"),
                DB::raw("0 as total_uts"),
                DB::raw("0 as total_uas")
            );

        // 3. Gabungkan dan urutkan berdasarkan nama_mk
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

    public function previewKHS(Request $request)
    {
        if (!Auth::check() || Auth::user()->peran !== 'mahasiswa') abort(403);

        $request->validate(['semester_id' => 'required|exists:semesters,id']);

        $mahasiswa = Auth::user()->mahasiswa;
        $semester = Semester::findOrFail($request->semester_id);

        $khsData = $this->getCombinedKHSData($mahasiswa_id = $mahasiswa->id, $semester_id = $request->semester_id); // Disesuaikan menggunakan parameter

        if ($khsData->isEmpty()) {
            return back()->with('warning', 'Belum ada data nilai untuk semester ini.');
        }

        $totalMutu = $khsData->sum(fn($item) => ($item->nilai_indeks ?? 0) * ($item->sks ?? 0));
        $totalSKS = $khsData->sum('sks') ?? 0;
        $ipk = $totalSKS > 0 ? round($totalMutu / $totalSKS, 2) : 0;

        return Inertia::render('Mahasiswa/KHS/Index', [
            'mahasiswa' => $mahasiswa->load('user', 'jurusan'),
            'semester' => $semester,
            'khsData' => $khsData,
            'ipk' => $ipk,
            'totalSKS' => $totalSKS,
            'isPreview' => true
        ]);
    }

    public function cetakKHS(Request $request)
    {
        if (!Auth::check() || Auth::user()->peran !== 'mahasiswa') abort(403);

        $request->validate(['semester_id' => 'required|exists:semesters,id']);

        $mahasiswa = Auth::user()->mahasiswa;
        $semester = Semester::findOrFail($request->semester_id);

        $khsData = $this->getCombinedKHSData($mahasiswa->id, $request->semester_id);

        if ($khsData->isEmpty()) abort(404, 'Data KHS tidak ditemukan.');

        $totalMutu = $khsData->sum(fn($item) => ($item->nilai_indeks ?? 0) * ($item->sks ?? 0));
        $totalSKS = $khsData->sum('sks') ?? 0;
        $ipk = $totalSKS > 0 ? round($totalMutu / $totalSKS, 2) : 0;

        $data = [
            'mahasiswa' => $mahasiswa->load('user', 'jurusan'),
            'semester' => $semester,
            'khs_data' => $khsData, // Perhatikan kunci array ini konsisten dengan cetak pada admin
            'ipk' => $ipk,
            'total_sks' => $totalSKS,
            'tanggal_cetak' => now()->translatedFormat('d F Y')
        ];

        $filename = "KHS-{$mahasiswa->nim}-" . Str::slug($semester->nama_semester) . ".pdf";

        // Gunakan view yang sama agar desain konsisten dengan admin
        return PDF::loadView('admin.khs.cetak-khs', $data)
            ->setPaper('a4', 'portrait')
            ->stream($filename);
    }
}
