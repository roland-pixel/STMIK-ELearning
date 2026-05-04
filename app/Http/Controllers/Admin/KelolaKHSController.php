<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use App\Models\Mahasiswa;
use App\Models\Semester;
use Illuminate\Support\Str;

class KelolaKHSController extends Controller
{
    // Tambahkan method ini ke dalam class KelolaKHSController

    public function getMahasiswaByJurusan(Request $request)
    {
        $request->validate(['jurusan_id' => 'required|exists:jurusans,id']);

        $mahasiswas = Mahasiswa::with('user')
            ->where('jurusan_id', $request->jurusan_id)
            ->get()
            ->map(fn($m) => [
                'id'    => $m->id,
                'label' => $m->nim . ' - ' . $m->user->nama_lengkap,
            ]);

        return response()->json($mahasiswas);
    }

    public function getSemesterByMahasiswa(Request $request)
    {
        $request->validate(['mahasiswa_id' => 'required|exists:mahasiswas,id']);

        $semesterIdsFromRekap = DB::table('rekap_nilais')
            ->where('mahasiswa_id', $request->mahasiswa_id)
            ->pluck('semester_id');

        $semesterIdsFromBimbingan = DB::table('bimbingans')
            ->where('mahasiswa_id', $request->mahasiswa_id)
            ->where('status', 'approved')
            ->pluck('semester_id');

        $semesterIds = $semesterIdsFromRekap->merge($semesterIdsFromBimbingan)->unique();

        $semesters = Semester::whereIn('id', $semesterIds)
            ->orderBy('nama_semester', 'desc')
            ->get()
            ->map(fn($s) => [
                'id'    => $s->id,
                'label' => $s->nama_semester . ' (' . ($s->status_aktif === 'active' ? 'Aktif' : 'Arsip') . ')',
            ]);

        return response()->json($semesters);
    }

    public function index()
    {
        // Hanya jurusan yang punya mahasiswa
        $jurusans = \App\Models\Jurusan::whereHas('mahasiswas')->orderBy('nama_jurusan')->get();

        $mahasiswas = Mahasiswa::with('user', 'jurusan')->get();

        $semesters = Semester::orderBy('nama_semester', 'desc')->get()->map(function ($semester) {
            $semester->is_active_display = $semester->status_aktif === 'active';
            $semester->status_display = $semester->is_active_display ? 'Aktif' : 'Arsip';
            return $semester;
        });

        return view('admin.khs.index', compact('mahasiswas', 'semesters', 'jurusans'));
    }

    /**
     * Fungsi Helper untuk menggabungkan Matkul Umum dan Matkul Spesial
     * Menggunakan leftJoin agar data tetap muncul meski kurikulum belum diset.
     */
    private function getCombinedKHSData($mahasiswaId, $semesterId)
    {
        $mahasiswa = Mahasiswa::find($mahasiswaId);
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
                // Untuk matkul spesial, kita hitung huruf dan indeksnya di sini
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
        $request->validate([
            'mahasiswa_id' => 'required|exists:mahasiswas,id',
            'semester_id' => 'required|exists:semesters,id',
        ]);

        $mahasiswa = Mahasiswa::with('user', 'jurusan')->findOrFail($request->mahasiswa_id);
        $semester = Semester::findOrFail($request->semester_id);

        $khsData = $this->getCombinedKHSData($request->mahasiswa_id, $request->semester_id);

        $totalMutu = $khsData->sum(fn($item) => ($item->nilai_indeks ?? 0) * ($item->sks ?? 0));
        $totalSKS = $khsData->sum('sks') ?? 0;
        $ipk = $totalSKS > 0 ? round($totalMutu / $totalSKS, 2) : 0;

        return view('admin.khs.preview-khs', compact(
            'mahasiswa',
            'semester',
            'khsData',
            'ipk',
            'totalSKS'
        ));
    }

    public function cetakKHS(Request $request)
    {
        $request->validate([
            'mahasiswa_id' => 'required|exists:mahasiswas,id',
            'semester_id' => 'required|exists:semesters,id',
        ]);

        $mahasiswa = Mahasiswa::with('user', 'jurusan')->findOrFail($request->mahasiswa_id);
        $semester = Semester::findOrFail($request->semester_id);

        $khsData = $this->getCombinedKHSData($request->mahasiswa_id, $request->semester_id);

        $totalMutu = $khsData->sum(fn($item) => ($item->nilai_indeks ?? 0) * ($item->sks ?? 0));
        $totalSKS = $khsData->sum('sks') ?? 0;
        $ipk = $totalSKS > 0 ? round($totalMutu / $totalSKS, 2) : 0;

        $data = [
            'mahasiswa' => $mahasiswa,
            'semester' => $semester,
            'khs_data' => $khsData,
            'ipk' => $ipk,
            'total_sks' => $totalSKS,
            'tanggal_cetak' => now()->translatedFormat('d F Y')
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
