<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf as PDF;  // ✅ FIX: Alias PDF
use App\Models\Mahasiswa;
use App\Models\Semester;
use Illuminate\Support\Str;

class KelolaKHSController extends Controller
{
    public function index()
    {
        $mahasiswas = Mahasiswa::with('user', 'jurusan')->get();
        $semesters = Semester::where('status_aktif', true)->get();

        return view('admin.khs.index', compact('mahasiswas', 'semesters'));
    }

    public function previewKHS(Request $request)
    {
        $request->validate([
            'mahasiswa_id' => 'required|exists:mahasiswas,id',
            'semester_id' => 'required|exists:semesters,id'
        ]);

        $mahasiswa = Mahasiswa::with('user', 'jurusan')->findOrFail($request->mahasiswa_id);
        $semester = Semester::findOrFail($request->semester_id);

        $khsData = DB::table('rekap_nilais as r')
            ->join('mahasiswas as m', 'r.mahasiswa_id', '=', 'm.id')
            ->join('users as u', 'm.user_id', '=', 'u.id')
            ->join('jurusans as j', 'm.jurusan_id', '=', 'j.id')
            ->join('semesters as s', 'r.semester_id', '=', 's.id')
            ->join('mata_kuliahs as mk', 'r.mata_kuliah_id', '=', 'mk.id')
            ->where('r.mahasiswa_id', $request->mahasiswa_id)
            ->where('r.semester_id', $request->semester_id)
            ->select(
                'u.nama_lengkap',
                'm.nim',
                'j.nama_jurusan',
                's.nama_semester',
                'mk.kode_mk',
                'mk.nama_mk',
                'mk.sks',
                'r.nilai_akhir_angka',
                'r.nilai_huruf',
                'r.nilai_indeks',
                'r.total_tugas',
                'r.total_uts',
                'r.total_uas'
            )
            ->orderBy('mk.kode_mk')
            ->get();

        // IPS Calculation (perbaiki null safety)
        $totalMutu = $khsData->sum(function ($item) {
            return ($item->nilai_indeks ?? 0) * ($item->sks ?? 0);
        });
        $totalSKS = $khsData->sum('sks') ?? 0;
        $ipk = $totalSKS > 0 ? round($totalMutu / $totalSKS, 2) : 0;

        // Total komponen nilai semester
        $totalTugas = $khsData->sum('total_tugas') ?? 0;
        $totalUTS = $khsData->sum('total_uts') ?? 0;
        $totalUAS = $khsData->sum('total_uas') ?? 0;

        return view('admin.khs.preview-khs', compact(
            'mahasiswa',
            'semester',
            'khsData',
            'ipk',
            'totalSKS',
            'totalTugas',
            'totalUTS',
            'totalUAS'
        ));
    }

    public function cetakKHS(Request $request)
    {
        $request->validate([
            'mahasiswa_id' => 'required|exists:mahasiswas,id',
            'semester_id' => 'required|exists:semesters,id'
        ]);

        $mahasiswa = Mahasiswa::with('user', 'jurusan')->findOrFail($request->mahasiswa_id);
        $semester = Semester::findOrFail($request->semester_id);

        $khsData = DB::table('rekap_nilais as r')
            ->join('mahasiswas as m', 'r.mahasiswa_id', '=', 'm.id')
            ->join('users as u', 'm.user_id', '=', 'u.id')
            ->join('jurusans as j', 'm.jurusan_id', '=', 'j.id')
            ->join('semesters as s', 'r.semester_id', '=', 's.id')
            ->join('mata_kuliahs as mk', 'r.mata_kuliah_id', '=', 'mk.id')
            ->where('r.mahasiswa_id', $request->mahasiswa_id)
            ->where('r.semester_id', $request->semester_id)
            ->select(
                'u.nama_lengkap',
                'm.nim',
                'j.nama_jurusan',
                's.nama_semester',
                'mk.kode_mk',
                'mk.nama_mk',
                'mk.sks',
                'r.nilai_akhir_angka',
                'r.nilai_huruf',
                'r.nilai_indeks',
                'r.total_tugas',
                'r.total_uts',
                'r.total_uas'
            )
            ->orderBy('mk.kode_mk')
            ->get();

        $totalMutu = $khsData->sum(function ($item) {
            return ($item->nilai_indeks ?? 0) * ($item->sks ?? 0);
        });
        $totalSKS = $khsData->sum('sks') ?? 0;
        $ipk = $totalSKS > 0 ? round($totalMutu / $totalSKS, 2) : 0;

        $totalTugas = $khsData->sum('total_tugas') ?? 0;
        $totalUTS = $khsData->sum('total_uts') ?? 0;
        $totalUAS = $khsData->sum('total_uas') ?? 0;

        $data = [
            'mahasiswa' => $mahasiswa,
            'semester' => $semester,
            'khs_data' => $khsData,
            'ipk' => $ipk,
            'total_sks' => $totalSKS,
            'total_tugas' => $totalTugas,
            'total_uts' => $totalUTS,
            'total_uas' => $totalUAS,
            'tanggal_cetak' => now()->translatedFormat('d F Y')
        ];

        $filename = "KHS-{$mahasiswa->nim}-" . Str::slug($semester->nama_semester) . "-" . now()->format('Ymd') . ".pdf";

        $pdf = PDF::loadView('admin.khs.cetak-khs', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'tempDir' => public_path(),
                'chroot'  => public_path(),
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true, // Jaga-jaga jika nanti tambah logo
            ]);

        return $pdf->stream($filename);
    }
}
