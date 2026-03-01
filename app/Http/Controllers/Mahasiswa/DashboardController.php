<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    private function themeFor(string $key): string
    {
        $themes = [
            'bg-gradient-to-r from-green-600 to-emerald-500',
            'bg-gradient-to-r from-blue-600 to-indigo-500',
            'bg-gradient-to-r from-orange-500 to-rose-500',
            'bg-gradient-to-r from-slate-700 to-gray-600',
            'bg-gradient-to-r from-teal-600 to-green-500',
        ];

        $idx = crc32($key) % count($themes);
        return $themes[$idx];
    }

    public function index()
    {
        $user = auth()->user();

        // pastikan akun ini beneran mahasiswa & ada relasi mahasiswa
        abort_if($user->peran !== 'mahasiswa', 403);
        abort_if(!$user->mahasiswa, 403, 'Akun mahasiswa belum terhubung ke data mahasiswa.');

        $mahasiswaId = $user->mahasiswa->id;

        // ambil kelas yang diikuti mahasiswa (pivot: anggota_kelases)
        $classes = Kelas::query()
            ->whereHas('semester', fn($q) => $q->where('status_aktif', 'active'))
            ->whereHas('anggotaKelases', fn($q) => $q->where('mahasiswa_id', $mahasiswaId))
            ->with([
                'mataKuliah:id,nama_mk',
                'semester:id,nama_semester,status_aktif',
                'dosen:id,user_id',
                'dosen.user:id,nama_lengkap,avatar',
            ])
            ->select('id', 'uuid', 'dosen_id', 'mata_kuliah_id', 'semester_id', 'nama_kelas', 'deskripsi')
            ->latest()
            ->get()
            ->values()
            ->map(function ($k) {
                return [
                    'id' => $k->id,
                    'uuid' => $k->uuid,
                    'nama' => $k->nama_kelas,
                    'deskripsi' => $k->deskripsi,
                    'mata_kuliah' => $k->mataKuliah?->nama_mk,
                    'semester' => $k->semester?->nama_semester,
                    'dosen' => $k->dosen?->user?->nama_lengkap,
                    'dosen_avatar' => $k->dosen?->user?->avatar,
                    'theme' => $this->themeFor($k->uuid ?? (string) $k->id),
                ];
            });

        return Inertia::render('Mahasiswa/Dashboard', [
            'classes' => $classes,
            'mahasiswa' => [
                'id' => $user->mahasiswa->id,
                'uuid' => $user->mahasiswa->uuid,
                'nim' => $user->mahasiswa->nim,
                'nama' => $user->nama_lengkap,
                'avatar' => $user->avatar,
            ],
        ]);
    }
}
