<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\AnggotaKelas;
use App\Models\RekapNilai;
use App\Models\Pengumpulan;
use Illuminate\Http\Request;

class AnggotaKelasController extends Controller
{
    /**
     * Fitur bagi mahasiswa untuk keluar dari kelas (Unenroll)
     * Syarat: Belum ada pengumpulan tugas/ujian dan belum ada rekap nilai.
     */
    public function destroy($id)
    {
        // 1. Cari data anggota kelas, pastikan milik mahasiswa yang sedang login
        $mahasiswaId = auth()->user()->mahasiswa->id;
        $anggota = AnggotaKelas::where('id', $id)
            ->where('mahasiswa_id', $mahasiswaId)
            ->firstOrFail();

        $kelasId = $anggota->kelas_id;

        // 2. Cek apakah sudah ada rekap nilai di kelas ini
        $hasGrades = RekapNilai::where('mahasiswa_id', $mahasiswaId)
            ->where('kelas_id', $kelasId)
            ->exists();

        // 3. Cek apakah sudah pernah mengumpulkan tugas/ujian di kelas ini
        // Kita cek tabel pengumpulans yang penilaian_id-nya milik kelas tersebut
        $hasSubmissions = Pengumpulan::where('mahasiswa_id', $mahasiswaId)
            ->whereHas('penilaian', function ($query) use ($kelasId) {
                $query->where('kelas_id', $kelasId);
            })
            ->exists();

        // 4. Validasi: Jika ada nilai atau tugas, gagalkan proses
        if ($hasGrades || $hasSubmissions) {
            return redirect()->back()->with('error', 'Gagal: Kamu tidak bisa keluar dari kelas karena sudah memiliki riwayat tugas atau nilai.');
        }

        // 5. Jika bersih, hapus keanggotaan
        $anggota->delete();

        return redirect()->route('mahasiswa.dashboard') // Sesuaikan route dashboard/index kelasmu
            ->with('success', 'Kamu telah berhasil keluar dari kelas.');
    }
}
