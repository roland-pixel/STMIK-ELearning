<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class KelasDetailController extends Controller
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

    private function dosenId(Request $request): int
    {
        return (int) DB::table('dosens')
            ->where('user_id', $request->user()->id)
            ->value('id');
    }

    public function show(Request $request, Kelas $kelas)
    {
        $dosenId = $this->dosenId($request);

        // Pastikan dosen hanya bisa akses kelas yang dia ampu
        abort_if((int) $kelas->dosen_id !== $dosenId, 403);

        // theme harus konsisten dengan dashboard
        $theme = $this->themeFor($kelas->uuid ?? (string) $kelas->id);

        // Total anggota
        $totalAnggota = $kelas->anggotaKelases()->count();

        // Eager load
        $kelas->load([
            'dosen.user:id,nama_lengkap,email',
            'mataKuliah:id,kode_mk,nama_mk,sks,jenis_mk',
            'semester:id,nama_semester,status_aktif,tanggal_mulai,tanggal_selesai',
        ]);

        // Materi
        $materis = $kelas->materis()
            ->latest()
            ->get()
            ->map(fn($m) => [
                'id' => $m->id,
                'judul' => $m->judul,
                'deskripsi' => $m->deskripsi,
                'file_path' => $m->file_path,
                'link_url' => $m->link_url,
                'created_at' => optional($m->created_at)->toDateTimeString(),
            ]);

        // Penilaian
        $penilaiansRaw = $kelas->penilaians()
            ->latest()
            ->get(['id', 'uuid', 'judul', 'instruksi', 'kategori', 'mode_penilaian', 'tenggat_waktu', 'created_at']);

        $pengumpulanCounts = DB::table('pengumpulans')
            ->select('penilaian_id', DB::raw('COUNT(*) as total'))
            ->whereIn('penilaian_id', $penilaiansRaw->pluck('id'))
            ->groupBy('penilaian_id')
            ->pluck('total', 'penilaian_id');

        $penilaians = $penilaiansRaw->map(function ($p) use ($totalAnggota, $pengumpulanCounts) {
            $sudah = (int) ($pengumpulanCounts[$p->id] ?? 0);

            return [
                'id' => $p->id,
                'uuid' => $p->uuid,
                'judul' => $p->judul,
                'instruksi' => $p->instruksi,
                'kategori' => $p->kategori,
                'mode_penilaian' => $p->mode_penilaian,
                'tenggat_waktu' => optional($p->tenggat_waktu)->toDateTimeString(),
                'created_at' => optional($p->created_at)->toDateTimeString(),
                'stat' => [
                    'total_anggota' => $totalAnggota,
                    'sudah_mengumpulkan' => $sudah,
                    'belum_mengumpulkan' => max($totalAnggota - $sudah, 0),
                ],
            ];
        });

        // Anggota
        $anggota = $kelas->anggotaKelases()
            ->with([
                'mahasiswa:id,uuid,nim,user_id',
                'mahasiswa.user:id,nama_lengkap,email',
            ])
            ->latest('tanggal_gabung')
            ->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'tanggal_gabung' => optional($a->tanggal_gabung)->toDateTimeString(),
                'mahasiswa' => [
                    'uuid' => $a->mahasiswa?->uuid,
                    'nim' => $a->mahasiswa?->nim,
                    'nama_lengkap' => $a->mahasiswa?->user?->nama_lengkap,
                    'email' => $a->mahasiswa?->user?->email,
                ],
            ]);

        return Inertia::render('Dosen/Kelas/Show', [
            'kelas' => [
                'id' => $kelas->id,
                'uuid' => $kelas->uuid,
                'nama_kelas' => $kelas->nama_kelas,
                'deskripsi' => $kelas->deskripsi,
                'kode_gabung' => $kelas->kode_gabung,

                // ✅ INI KUNCI BIAR SAMA DENGAN DASHBOARD
                'theme' => $theme,

                // (opsional) kalau suatu saat kamu punya cover/pattern dari DB
                // 'cover' => $kelas->cover ?? null,
                // 'pattern' => $kelas->pattern ?? null,

                'persentase_tugas' => $kelas->persentase_tugas,
                'persentase_uts' => $kelas->persentase_uts,
                'persentase_uas' => $kelas->persentase_uas,

                'dosen' => [
                    'nama_lengkap' => $kelas->dosen?->user?->nama_lengkap,
                    'email' => $kelas->dosen?->user?->email,
                ],
                'mata_kuliah' => [
                    'kode_mk' => $kelas->mataKuliah?->kode_mk,
                    'nama_mk' => $kelas->mataKuliah?->nama_mk,
                    'sks' => $kelas->mataKuliah?->sks,
                    'jenis_mk' => $kelas->mataKuliah?->jenis_mk,
                ],
                'semester' => [
                    'nama_semester' => $kelas->semester?->nama_semester,
                    'status_aktif' => $kelas->semester?->status_aktif,
                    'tanggal_mulai' => optional($kelas->semester?->tanggal_mulai)->toDateString(),
                    'tanggal_selesai' => optional($kelas->semester?->tanggal_selesai)->toDateString(),
                ],
                'counts' => [
                    'materi' => $materis->count(),
                    'penilaian' => $penilaians->count(),
                    'anggota' => $anggota->count(),
                ],
            ],
            'materis' => $materis,
            'penilaians' => $penilaians,
            'anggota' => $anggota,
        ]);
    }
}
