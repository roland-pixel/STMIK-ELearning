<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Jurusan;
use App\Models\MataKuliah;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\Kelas;
use App\Models\Semester;

class LandingPageController extends Controller
{
    /**
     * Menampilkan halaman landing page publik.
     *
     * Mengambil statistik umum aplikasi untuk ditampilkan
     * kepada pengunjung (calon mahasiswa, tamu, dsb).
     */
    public function index()
    {
        // --- Statistik Umum ---
        $stats = [
            'total_mahasiswa' => Mahasiswa::where('status', 'aktif')->count(),
            'total_dosen'     => Dosen::count(),
            'total_kelas'     => Kelas::count(),
            'total_mk'        => MataKuliah::count(),
        ];

        // --- Semester Aktif ---
        $semesterAktif = Semester::where('status_aktif', 'active')->first();

        // --- Daftar Jurusan ---
        $jurusans = Jurusan::withCount([
            // Jumlah mahasiswa aktif per jurusan
            'mahasiswas as total_mahasiswa' => fn($q) =>
            $q->where('status', 'aktif'),
        ])
            ->get();

        // --- Mata Kuliah Unggulan (sample, bisa dikustomisasi) ---
        // Mengambil beberapa mata kuliah untuk ditampilkan di landing
        $mataKuliahs = MataKuliah::select('id', 'nama_mk', 'sks', 'jenis_mk', 'kategori_mk')
            ->limit(6)
            ->get();

        return view('landing', compact(
            'stats',
            'semesterAktif',
            'jurusans',
            'mataKuliahs',
        ));
    }
}
