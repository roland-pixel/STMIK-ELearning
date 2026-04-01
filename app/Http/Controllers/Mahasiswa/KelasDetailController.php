<?php

namespace App\Http\Controllers\Mahasiswa;

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

    private function mahasiswaId(Request $request): int
    {
        return (int) DB::table('mahasiswas')
            ->where('user_id', $request->user()->id)
            ->value('id');
    }

    private function ensureMember(Request $request, Kelas $kelas): int
    {
        $mahasiswaId = $this->mahasiswaId($request);
        abort_if(!$mahasiswaId, 403, 'Akun mahasiswa belum terhubung ke data mahasiswa.');

        $isMember = DB::table('anggota_kelases')
            ->where('kelas_id', $kelas->id)
            ->where('mahasiswa_id', $mahasiswaId)
            ->exists();

        abort_if(!$isMember, 403, 'Kamu tidak terdaftar di kelas ini.');

        return $mahasiswaId;
    }

    public function show(Request $request, Kelas $kelas)
    {
        abort_if($request->user()?->peran !== 'mahasiswa', 403);

        $mahasiswaId = $this->ensureMember($request, $kelas);

        $theme = $this->themeFor($kelas->uuid ?? (string) $kelas->id);

        $totalAnggota = $kelas->anggotaKelases()->count();

        $kelas->load([
            'dosen.user:id,nama_lengkap,email,avatar',
            'mataKuliah:id,kode_mk,nama_mk,sks,jenis_mk',
            'semester:id,nama_semester,status_aktif,tanggal_mulai,tanggal_selesai',
        ]);

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

        $penilaiansRaw = $kelas->penilaians()
            ->where('mode_penilaian', 'online') // <--- Hanya ambil yang online
            ->latest()
            ->get(['id', 'uuid', 'judul', 'instruksi', 'kategori', 'mode_penilaian', 'tenggat_waktu', 'created_at']);

        $pengumpulanByPenilaian = DB::table('pengumpulans')
            ->where('mahasiswa_id', $mahasiswaId)
            ->whereIn('penilaian_id', $penilaiansRaw->pluck('id'))
            ->get(['penilaian_id', 'waktu_mulai', 'waktu_selesai', 'nilai_total'])
            ->keyBy('penilaian_id');

        $penilaians = $penilaiansRaw->map(function ($p) use ($pengumpulanByPenilaian) {
            $peng = $pengumpulanByPenilaian->get($p->id);

            return [
                'id' => $p->id,
                'uuid' => $p->uuid,
                'judul' => $p->judul,
                'instruksi' => $p->instruksi,
                'kategori' => $p->kategori,
                'mode_penilaian' => $p->mode_penilaian,
                'tenggat_waktu' => optional($p->tenggat_waktu)->toDateTimeString(),
                'created_at' => optional($p->created_at)->toDateTimeString(),
                'my' => [
                    'started_at' => $peng?->waktu_mulai ? (string) $peng->waktu_mulai : null,
                    'submitted_at' => $peng?->waktu_selesai ? (string) $peng->waktu_selesai : null,
                    'nilai_total' => $peng?->nilai_total !== null ? (float) $peng->nilai_total : null,
                    'status' => $peng
                        ? ($peng->waktu_selesai ? 'submitted' : 'in_progress')
                        : 'not_started',
                ],
            ];
        });

        // ✅ Anggota + avatar HARUS ikut di user
        $anggota = $kelas->anggotaKelases()
            ->with([
                'mahasiswa:id,uuid,nim,user_id',
                'mahasiswa.user:id,nama_lengkap,email,avatar',
            ])
            ->latest('tanggal_gabung')
            ->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'tanggal_gabung' => optional($a->tanggal_gabung)->toDateTimeString(),
                'mahasiswa' => [
                    'uuid' => $a->mahasiswa?->uuid,
                    'nim' => $a->mahasiswa?->nim,
                    'user_id' => $a->mahasiswa?->user_id,
                    'nama_lengkap' => $a->mahasiswa?->user?->nama_lengkap,
                    'email' => $a->mahasiswa?->user?->email,
                    'avatar' => $a->mahasiswa?->user?->avatar,
                ],
            ]);

        return Inertia::render('Mahasiswa/Kelas/Show', [
            'kelas' => [
                'id' => $kelas->id,
                'uuid' => $kelas->uuid,
                'nama_kelas' => $kelas->nama_kelas,
                'deskripsi' => $kelas->deskripsi,
                'theme' => $theme,
                'dosen' => [
                    'nama_lengkap' => $kelas->dosen?->user?->nama_lengkap,
                    'email' => $kelas->dosen?->user?->email,
                    'avatar' => $kelas->dosen?->user?->avatar,
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
                    'anggota' => $totalAnggota,
                ],
            ],
            'materis' => $materis,
            'penilaians' => $penilaians,
            'anggota' => $anggota,
        ]);
    }
}
