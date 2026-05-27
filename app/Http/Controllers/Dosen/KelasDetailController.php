<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\AnggotaKelas;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\RekapNilai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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

    public function addMahasiswa(Request $request, Kelas $kelas)
    {
        $dosenId = $this->dosenId($request);
        abort_if((int) $kelas->dosen_id !== $dosenId, 403);

        // 1. Validasi input generic
        $request->validate([
            'identifier' => 'required',
        ], [
            'identifier.required' => 'NIM atau Email mahasiswa wajib diisi.',
        ]);

        // 2. Cari mahasiswa berdasarkan NIM atau Email (lewat relasi user)
        $mahasiswa = Mahasiswa::where('nim', $request->identifier)
            ->orWhereHas('user', function ($query) use ($request) {
                $query->where('email', $request->identifier);
            })
            ->first();

        // 3. Jika tidak ditemukan, kembalikan error
        if (!$mahasiswa) {
            return back()->withErrors([
                'identifier' => 'Mahasiswa dengan NIM atau Email tersebut tidak ditemukan.'
            ]);
        }

        // 4. Cek apakah sudah terdaftar di kelas
        $isExist = DB::table('anggota_kelases')
            ->where('kelas_id', $kelas->id)
            ->where('mahasiswa_id', $mahasiswa->id)
            ->exists();

        if ($isExist) {
            return back()->withErrors([
                'identifier' => 'Mahasiswa ini sudah terdaftar di dalam kelas.'
            ]);
        }
        // Gunakan Model Eloquent Anda (Pastikan sudah di-use di atas: use App\Models\AnggotaKelas; dan use App\Models\RekapNilai;)
        DB::transaction(function () use ($kelas, $mahasiswa) {

            // 1. Menggunakan Model Eloquent AnggotaKelas -> Memicu Observer!
            AnggotaKelas::create([
                'kelas_id' => $kelas->id,
                'mahasiswa_id' => $mahasiswa->id,
                'tanggal_gabung' => now(),
            ]);

            // 2. Menggunakan Model Eloquent RekapNilai
            RekapNilai::create([
                'mahasiswa_id' => $mahasiswa->id,
                'semester_id' => $kelas->semester_id,
                'mata_kuliah_id' => $kelas->mata_kuliah_id,
                'kelas_id' => $kelas->id,
                'total_tugas' => 0,
                'total_uts' => 0,
                'total_uas' => 0,
                'nilai_akhir_angka' => 0,
                'nilai_huruf' => 'E',
                'nilai_indeks' => 0,
            ]);
        });

        // Baris ini: // Cache::forget("kelas:global_detail:{$kelas->id}"); 
        // Sekarang AMAN untuk tetap dikomentari/dihapus karena Eloquent di atas sudah otomatis memicu Observer!

        // Cache::forget("kelas:global_detail:{$kelas->id}");

        return back()->with('success', 'Mahasiswa berhasil ditambahkan.');
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
            'dosen.user:id,nama_lengkap,email,avatar',
            'mataKuliah:id,nama_mk,sks,jenis_mk',
            'semester:id,nama_semester,status_aktif,tanggal_mulai,tanggal_selesai',
        ]);

        $rekapNilais = DB::table('anggota_kelases')
            ->join('mahasiswas', 'anggota_kelases.mahasiswa_id', '=', 'mahasiswas.id')
            ->leftJoin('users', 'mahasiswas.user_id', '=', 'users.id')
            ->leftJoin('rekap_nilais', function ($join) use ($kelas) {
                $join->on('anggota_kelases.mahasiswa_id', '=', 'rekap_nilais.mahasiswa_id')
                    ->where('rekap_nilais.kelas_id', '=', $kelas->id);
            })
            ->where('anggota_kelases.kelas_id', $kelas->id)
            ->select([
                'anggota_kelases.id',
                'mahasiswas.nim',
                'mahasiswas.uuid as mahasiswa_uuid',
                'users.nama_lengkap',
                'users.email',
                'users.avatar',
                'rekap_nilais.total_tugas',
                'rekap_nilais.total_uts',
                'rekap_nilais.total_uas',
                'rekap_nilais.nilai_akhir_angka',
                'rekap_nilais.nilai_huruf',
                'rekap_nilais.nilai_indeks',
                'anggota_kelases.tanggal_gabung',
            ])
            ->orderBy('users.nama_lengkap')
            ->get()
            ->map(function ($row) {
                return [
                    'id' => $row->id,
                    'nim' => $row->nim,
                    'mahasiswa_uuid' => $row->mahasiswa_uuid,
                    'nama_lengkap' => $row->nama_lengkap,
                    'email' => $row->email,
                    'avatar' => $row->avatar,
                    'tanggal_gabung' => $row->tanggal_gabung,
                    'nilai' => [
                        'total_tugas' => (float) ($row->total_tugas ?? 0),
                        'total_uts' => (float) ($row->total_uts ?? 0),
                        'total_uas' => (float) ($row->total_uas ?? 0),
                        'nilai_akhir_angka' => (float) ($row->nilai_akhir_angka ?? 0),
                        'nilai_huruf' => $row->nilai_huruf ?? '-',
                        'nilai_indeks' => (float) ($row->nilai_indeks ?? 0),
                    ]
                ];
            });


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
                    'nama_lengkap' => $a->mahasiswa?->user?->nama_lengkap,
                    'email' => $a->mahasiswa?->user?->email,
                    'avatar' => $a->mahasiswa?->user?->avatar,
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
                    'user_id' => $kelas->dosen?->user_id,
                    'avatar' => $kelas->dosen?->user?->avatar,
                    'nama_lengkap' => $kelas->dosen?->user?->nama_lengkap,
                    'email' => $kelas->dosen?->user?->email,
                ],
                'mata_kuliah' => [
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
            'rekap_nilais' => $rekapNilais,
        ]);
    }
}
