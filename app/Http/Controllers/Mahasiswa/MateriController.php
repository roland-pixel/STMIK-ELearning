<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Materi;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MateriController extends Controller
{
    private function ensureMember(Request $request, Kelas $kelas): void
    {
        $user = $request->user();

        abort_if(!$user || $user->peran !== 'mahasiswa', 403, 'Akun bukan mahasiswa.');
        abort_if(!$user->mahasiswa, 403, 'Akun mahasiswa belum terhubung ke data mahasiswa.');

        $mahasiswaId = (int) $user->mahasiswa->id;

        $isMember = $kelas->anggotaKelases()
            ->where('mahasiswa_id', $mahasiswaId)
            ->exists();

        abort_if(!$isMember, 403, 'Kamu tidak terdaftar di kelas ini.');
    }

    public function index(Request $request, Kelas $kelas)
    {
        $this->ensureMember($request, $kelas);

        $openId = $request->query('open'); // ?open=123 (optional)

        $query = Materi::query()
            ->where('kelas_id', $kelas->id)
            ->latest();

        if (!empty($openId)) {
            $query->where('id', $openId);
        }

        $materis = $query->get()->map(function ($m) {
            return [
                'id' => $m->id,
                'judul' => $m->judul,
                'deskripsi' => $m->deskripsi,
                'link_url' => $m->link_url,
                'file_path' => $m->file_path,
                'file_name' => $m->file_path ? basename($m->file_path) : null,
                'created_at' => optional($m->created_at)->toIso8601String(),
            ];
        });

        // kalau openId ada tapi materi tidak ditemukan / bukan di kelas ini
        if (!empty($openId) && $materis->count() === 0) {
            abort(404, 'Materi tidak ditemukan di kelas ini.');
        }

        return Inertia::render('Mahasiswa/Kelas/Tugas/Materi/Index', [
            'kelas' => [
                'id' => $kelas->id,
                'uuid' => $kelas->uuid,
                'nama_kelas' => $kelas->nama_kelas,
            ],
            'materis' => $materis,
            'open' => !empty($openId) ? (int) $openId : null,
        ]);
    }
}
