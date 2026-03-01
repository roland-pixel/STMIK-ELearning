<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';



    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'csrf_token' => csrf_token(),
            "weekly_quote" => Cache::get("weekly_quote"),
            'auth' => [
                'user' => fn() => $request->user()
                    ? [
                        'id' => $request->user()->id,
                        'uuid' => $request->user()->uuid,
                        'nama_lengkap' => $request->user()->nama_lengkap,
                        'email' => $request->user()->email,
                        'avatar' => $request->user()->avatar,
                        'peran' => $request->user()->peran,
                    ]
                    : null,
            ],
            'flash' => [
                'success' => fn() => $request->session()->get('success'),
            ],
            'ziggy' => function () {
                return (new Ziggy())->toArray();
            },
            'dosen_classes' => fn() => $this->shareDosenClasses($request),
            'mahasiswa_classes' => fn() => $this->shareMahasiswaClasses($request),
        ]);
    }
    private function shareDosenClasses(Request $request): array
    {
        $user = $request->user();
        if (!$user || $user->peran !== 'dosen') return [];

        // kalau relasi dosen belum ada
        if (!$user->relationLoaded('dosen')) {
            $user->load('dosen');
        }
        if (!$user->dosen) return [];

        return \App\Models\Kelas::query()
            ->where('dosen_id', $user->dosen->id)
            ->whereHas(
                'semester',
                fn($q) =>
                $q->where('status_aktif', 'active')
            )
            ->with(['mataKuliah:id,nama_mk', 'semester:id,nama_semester,status_aktif',])
            ->orderBy('nama_kelas')
            ->get()
            ->map(fn($k) => [
                'id' => $k->id,
                'uuid' => $k->uuid,
                'nama' => $k->nama_kelas,
                'mata_kuliah' => $k->mataKuliah?->nama_mk,
                'semester' => $k->semester?->nama_semester,
            ])
            ->toArray();
    }

    private function shareMahasiswaClasses(Request $request): array
    {
        $user = $request->user();
        if (!$user || $user->peran !== 'mahasiswa') return [];

        // pastikan relasi mahasiswa ada
        if (!$user->relationLoaded('mahasiswa')) {
            $user->load('mahasiswa');
        }
        if (!$user->mahasiswa) return [];

        $mahasiswaId = $user->mahasiswa->id;

        return \App\Models\Kelas::query()
            ->whereHas('semester', fn($q) => $q->where('status_aktif', 'active'))
            ->whereHas('anggotaKelases', fn($q) => $q->where('mahasiswa_id', $mahasiswaId))
            ->with([
                'mataKuliah:id,nama_mk',
                'semester:id,nama_semester,status_aktif',
                'dosen:id,user_id',
                'dosen.user:id,nama_lengkap',
            ])
            ->orderBy('nama_kelas')
            ->get()
            ->map(fn($k) => [
                'id' => $k->id,
                'uuid' => $k->uuid,
                'nama' => $k->nama_kelas,
                'mata_kuliah' => $k->mataKuliah?->nama_mk,
                'semester' => $k->semester?->nama_semester,
                'dosen' => $k->dosen?->user?->nama_lengkap,
            ])
            ->toArray();
    }
}
