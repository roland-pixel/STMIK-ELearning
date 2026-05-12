<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\MataKuliah;
use App\Models\Semester;
use App\Models\Pengumpulan; // Pastikan Model ini sudah ada
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalMahasiswa = Mahasiswa::count();
        $totalDosen = Dosen::count();
        $totalMatkul = MataKuliah::count();
        $semesterAktif = Semester::where('status_aktif', 'active')->first();

        // --- DATA UNTUK CHART (Pengumpulan Tugas per Bulan di Tahun Ini) ---
        $tahunIni = date('Y');
        $pengumpulanData = Pengumpulan::select(
            DB::raw('MONTH(created_at) as bulan'),
            DB::raw('count(*) as total')
        )
            ->whereYear('created_at', $tahunIni)
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->pluck('total', 'bulan')
            ->all();

        // Pastikan array memiliki 12 bulan (isi 0 jika kosong)
        $chartData = [];
        for ($i = 1; $i <= 12; $i++) {
            $chartData[] = $pengumpulanData[$i] ?? 0;
        }

        // --- DATA UNTUK GAUGE (Persentase Mahasiswa Aktif) ---
        $mahasiswaAktifCount = Mahasiswa::where('status', 'aktif')->count();
        $persentaseAktif = $totalMahasiswa > 0 ? round(($mahasiswaAktifCount / $totalMahasiswa) * 100) : 0;

        return view('admin.dashboard.index', compact(
            'totalMahasiswa',
            'totalDosen',
            'totalMatkul',
            'semesterAktif',
            'chartData',
            'persentaseAktif'
        ));
    }
}
