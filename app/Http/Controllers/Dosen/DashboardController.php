<?php

namespace App\Http\Controllers\Dosen;

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

        // pastikan akun ini beneran dosen & ada relasi dosen
        abort_if($user->peran !== 'dosen', 403);
        abort_if(!$user->dosen, 403, 'Akun dosen belum terhubung ke data dosen.');

        $dosenId = $user->dosen->id;

        // ambil kelas yang dia ampu
        $classes = Kelas::query()
            ->where('dosen_id', $dosenId)
            ->whereHas('semester', fn($q) => $q->where('status_aktif', 'active'))
            ->with([
                'mataKuliah:id,nama_mk',
                'semester:id,nama_semester,status_aktif',
            ])
            ->select('id', 'uuid', 'dosen_id', 'mata_kuliah_id', 'semester_id', 'nama_kelas', 'deskripsi')
            ->latest()
            ->get()
            ->values()
            ->map(function ($k) use ($user) {
                return [
                    'id' => $k->id,
                    'uuid' => $k->uuid,
                    'nama' => $k->nama_kelas,
                    'deskripsi' => $k->deskripsi,
                    'mata_kuliah' => $k->mataKuliah?->nama_mk,
                    'semester' => $k->semester?->nama_semester,
                    'dosen' => $user->nama_lengkap,
                    'dosen_avatar' => $user->avatar,
                    'theme' => $this->themeFor($k->uuid ?? (string) $k->id),
                ];
            });



        return Inertia::render('Dosen/Dashboard', [
            'classes' => $classes,
        ]);
    }
}
