<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Penilaian;
use App\Models\Pengumpulan;
use App\Models\RekapNilai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class PenilaianManualController extends Controller
{
    private function ensureOwner(Kelas $kelas): void
    {
        $dosenId = Auth::user()?->dosen?->id;
        abort_if(!$dosenId, 403, 'Akun dosen tidak valid.');
        abort_if((int) $kelas->dosen_id !== (int) $dosenId, 403, 'Tidak berhak mengakses kelas ini.');
    }

    public function create(Kelas $kelas)
    {
        $this->ensureOwner($kelas);

        $mahasiswas = $kelas->anggotaKelases()
            ->with('mahasiswa.user')
            ->get()
            ->map(fn($item) => [
                'id' => $item->mahasiswa->id,
                'nim' => $item->mahasiswa->nim,
                'nama' => $item->mahasiswa->user->nama_lengkap,
                'nilai' => 0,
            ]);

        $usedCategories = Penilaian::where('kelas_id', $kelas->id)
            ->whereIn('kategori', ['uts', 'uas'])
            ->where('mode_penilaian', 'manual')
            ->pluck('kategori')
            ->toArray();

        return Inertia::render('Dosen/Kelas/Tugas/Penilaian/Manual/Create', [
            'kelas' => $kelas->only(['id', 'uuid', 'nama_kelas']),
            'mahasiswas' => $mahasiswas,
            'usedCategories' => $usedCategories,
        ]);
    }

    public function store(Request $request, Kelas $kelas)
    {
        $this->ensureOwner($kelas);

        $allowedIds = $kelas->anggotaKelases()->pluck('mahasiswa_id')->toArray();

        $data = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'in:tugas,uts,uas'],
            'instruksi' => ['nullable', 'string'],
            'nilai_mahasiswa' => ['required', 'array'],
            'nilai_mahasiswa.*.id' => ['required', \Illuminate\Validation\Rule::in($allowedIds)],
            'nilai_mahasiswa.*.nilai' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        if (in_array($data['kategori'], ['uts', 'uas'])) {
            $existing = Penilaian::where('kelas_id', $kelas->id)
                ->where('kategori', $data['kategori'])
                ->where('mode_penilaian', 'manual')
                ->exists();

            if ($existing) {
                throw ValidationException::withMessages([
                    'kategori' => ['Kategori ' . $data['kategori'] . ' sudah digunakan untuk penilaian manual']
                ]);
            }
        }

        return DB::transaction(function () use ($kelas, $data) {
            $penilaian = Penilaian::create([
                'uuid' => (string) Str::uuid(),
                'kelas_id' => $kelas->id,
                'judul' => $data['judul'],
                'instruksi' => $data['instruksi'],
                'kategori' => $data['kategori'],
                'mode_penilaian' => 'manual',
            ]);

            foreach ($data['nilai_mahasiswa'] as $item) {
                $pengumpulan = Pengumpulan::create([
                    'uuid' => (string) Str::uuid(),
                    'penilaian_id' => $penilaian->id,
                    'mahasiswa_id' => $item['id'],
                    'waktu_mulai' => now(),
                    'waktu_selesai' => now(),
                    'nilai_total' => $item['nilai'],
                ]);
                $this->regenerateRekapNilai($pengumpulan->id);
            }

            // \Illuminate\Support\Facades\Cache::forget("kelas:global_detail:{$kelas->id}");

            return redirect()->route('dosen.kelas.show', $kelas->uuid)
                ->with('success', 'Penilaian manual berhasil dibuat.');
        });
    }

    public function edit(Kelas $kelas, Penilaian $penilaian)
    {
        $this->ensureOwner($kelas);
        abort_if($penilaian->kelas_id !== $kelas->id, 404);
        abort_if($penilaian->mode_penilaian !== 'manual', 404);

        $pengumpulans = Pengumpulan::where('penilaian_id', $penilaian->id)
            ->with('mahasiswa.user')
            ->get()
            ->keyBy('mahasiswa_id');

        $mahasiswas = $kelas->anggotaKelases()
            ->with('mahasiswa.user')
            ->get()
            ->map(function ($item) use ($pengumpulans) {
                $mhs = $item->mahasiswa;
                $pengumpulan = $pengumpulans->get($mhs->id);

                return [
                    'id' => $mhs->id,
                    'nim' => $mhs->nim,
                    'nama' => $mhs->user->nama_lengkap,
                    'nilai' => $pengumpulan?->nilai_total ?? 0,
                ];
            });

        $usedCategories = Penilaian::where('kelas_id', $kelas->id)
            ->whereIn('kategori', ['uts', 'uas'])
            ->where('mode_penilaian', 'manual')
            ->where('id', '!=', $penilaian->id)
            ->pluck('kategori')
            ->toArray();

        return Inertia::render('Dosen/Kelas/Tugas/Penilaian/Manual/Edit', [
            'kelas' => $kelas->only(['id', 'uuid', 'nama_kelas']),
            'penilaian' => [
                'id' => $penilaian->id,
                'uuid' => $penilaian->uuid,
                'judul' => $penilaian->judul,
                'instruksi' => $penilaian->instruksi,
                'kategori' => $penilaian->kategori,
            ],
            'mahasiswas' => $mahasiswas,
            'usedCategories' => $usedCategories,
        ]);
    }

    public function update(Request $request, Kelas $kelas, Penilaian $penilaian)
    {
        $this->ensureOwner($kelas);
        abort_if($penilaian->kelas_id !== $kelas->id, 404);
        abort_if($penilaian->mode_penilaian !== 'manual', 404);

        $allowedIds = $kelas->anggotaKelases()->pluck('mahasiswa_id')->toArray();

        $data = $request->validate([
            'judul' => ['sometimes', 'required', 'string', 'max:255'],
            'kategori' => ['sometimes', 'required', 'in:tugas,uts,uas'],
            'instruksi' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'nilai_mahasiswa' => ['required', 'array', 'min:1'],
            'nilai_mahasiswa.*.id' => ['required', \Illuminate\Validation\Rule::in($allowedIds)],
            'nilai_mahasiswa.*.nilai' => ['required', 'numeric', 'between:0,100'],
        ]);

        if (isset($data['kategori']) && in_array($data['kategori'], ['uts', 'uas'])) {
            $existing = Penilaian::where('kelas_id', $kelas->id)
                ->where('kategori', $data['kategori'])
                ->where('id', '!=', $penilaian->id)
                ->where('mode_penilaian', 'manual')
                ->exists();

            if ($existing) {
                throw ValidationException::withMessages([
                    'kategori' => ["Kategori **{$data['kategori']}** sudah digunakan untuk penilaian manual lain di kelas ini!"]
                ]);
            }
        }

        return DB::transaction(function () use ($request, $kelas, $penilaian, $data) {
            $penilaianData = array_filter([
                'judul' => $data['judul'] ?? null,
                'instruksi' => $data['instruksi'] ?? null,
                'kategori' => $data['kategori'] ?? null,
            ], fn($value) => $value !== null);

            if (!empty($penilaianData)) {
                $penilaian->update($penilaianData);
            }

            $mahasiswaIds = array_column($data['nilai_mahasiswa'], 'id');
            $uniqueMahasiswaIds = array_unique($mahasiswaIds);

            foreach ($data['nilai_mahasiswa'] as $item) {
                $pengumpulan = Pengumpulan::updateOrCreate(
                    [
                        'penilaian_id' => $penilaian->id,
                        'mahasiswa_id' => $item['id'],
                    ],
                    [
                        'uuid'          => (string) \Illuminate\Support\Str::uuid(), // Tambahkan ini
                        'waktu_mulai'   => now(),
                        'waktu_selesai' => now(),
                        'nilai_total'   => $item['nilai'],
                    ]
                );
            }

            foreach ($uniqueMahasiswaIds as $mahasiswaId) {
                $this->regenerateRekapNilaiForMahasiswa($penilaian->id, $mahasiswaId);
            }

            // \Illuminate\Support\Facades\Cache::forget("kelas:global_detail:{$penilaian->kelas_id}");

            return redirect()->route('dosen.kelas.show', $kelas->uuid)
                ->with('success', 'Nilai berhasil disimpan!');
        });
    }

    public function destroy(Kelas $kelas, Penilaian $penilaian)
    {
        $this->ensureOwner($kelas);

        return DB::transaction(function () use ($penilaian, $kelas) {
            $kelasId = $kelas->id;

            // 1. Ambil list ID mahasiswa yang terlibat sebelum datanya dihapus
            $mahasiswaIds = Pengumpulan::where('penilaian_id', $penilaian->id)
                ->pluck('mahasiswa_id')
                ->toArray();

            // 2. Hapus data pengumpulan
            Pengumpulan::where('penilaian_id', $penilaian->id)->delete();

            // 3. Hapus data penilaian induk
            $penilaian->delete();

            // 4. Hitung ulang rekap nilai langsung menggunakan ID kelas dan ID mahasiswa
            foreach ($mahasiswaIds as $mahasiswaId) {
                $this->forceRegenerateRekap($kelasId, $mahasiswaId);
            }

            // \Illuminate\Support\Facades\Cache::forget("kelas:global_detail:{$penilaian->kelas_id}");

            return redirect()
                ->route('dosen.kelas.show', $kelas->uuid)
                ->with('success', 'Penilaian manual berhasil dihapus.');
        });
    }

    private function regenerateRekapNilaiForMahasiswa($penilaianId, $mahasiswaId)
    {
        $pengumpulan = Pengumpulan::where('penilaian_id', $penilaianId)
            ->where('mahasiswa_id', $mahasiswaId)
            ->first();

        if (!$pengumpulan) return;

        $this->regenerateRekapNilai($pengumpulan->id);
    }

    /**
     * Regenerate rekap_nilai (OPTIMIZED & KRONOLOGIS)
     * Sekarang menyimpan record secara unik per kelas.
     */
    private function regenerateRekapNilai($pengumpulanId)
    {
        $pengumpulan = Pengumpulan::with(['penilaian.kelas', 'mahasiswa'])->find($pengumpulanId);
        if (!$pengumpulan) return;

        $mahasiswaId = $pengumpulan->mahasiswa_id;
        $kelasId     = $pengumpulan->penilaian->kelas_id;
        $kelas       = Kelas::find($kelasId);
        if (!$kelas) return;

        // 1. Ambil data pengumpulan mahasiswa di KELAS INI
        $semuaPengumpulan = Pengumpulan::whereHas('penilaian', fn($q) => $q->where('kelas_id', $kelasId))
            ->where('mahasiswa_id', $mahasiswaId)
            ->with('penilaian')
            ->get()
            ->groupBy('penilaian.kategori');

        // 2. Hitung komponen nilai
        $totalTugasDiKelas = Penilaian::where('kelas_id', $kelasId)->where('kategori', 'tugas')->count();
        $sumNilaiTugas = $semuaPengumpulan->get('tugas', collect())->sum('nilai_total');
        $totalTugas = $totalTugasDiKelas > 0 ? round($sumNilaiTugas / $totalTugasDiKelas, 2) : 0;

        $totalUts = $this->hitungRataRataKategori($semuaPengumpulan, 'uts');
        $totalUas = $this->hitungRataRataKategori($semuaPengumpulan, 'uas');

        // 3. Hitung Nilai Akhir
        $nilaiAkhir = round(
            ($totalTugas * $kelas->persentase_tugas / 100) +
                ($totalUts * $kelas->persentase_uts / 100) +
                ($totalUas * $kelas->persentase_uas / 100),
            2
        );

        // 4. 🔥 SIMPAN/UPDATE UNIK PER KELAS
        // Menggunakan updateOrCreate dengan mahasiswa_id & kelas_id sebagai filter.
        // Dengan ini, koreksi nilai di kelas ini tidak akan mengganggu rekap nilai
        // dari mata kuliah yang sama di kelas/semester lainnya.
        RekapNilai::updateOrCreate(
            [
                'mahasiswa_id' => $mahasiswaId,
                'kelas_id'     => $kelasId
            ],
            [
                'semester_id'       => $kelas->semester_id,
                'mata_kuliah_id'    => $kelas->mata_kuliah_id,
                'total_tugas'       => $totalTugas,
                'total_uts'         => $totalUts,
                'total_uas'         => $totalUas,
                'nilai_akhir_angka' => $nilaiAkhir,
                'nilai_huruf'       => $this->konversiHuruf($nilaiAkhir),
                'nilai_indeks'      => $this->konversiIndeks($nilaiAkhir),
            ]
        );
    }

    private function hitungRataRataKategori($semuaPengumpulan, $kategori)
    {
        $pengumpulanKategori = $semuaPengumpulan->get($kategori, collect());
        return $pengumpulanKategori->isEmpty() ? 0 : round($pengumpulanKategori->avg('nilai_total'), 2);
    }

    private function konversiHuruf($nilai)
    {
        if ($nilai >= 90) return 'A';
        if ($nilai >= 86) return 'A-';
        if ($nilai >= 80) return 'B+';
        if ($nilai >= 76) return 'B';
        if ($nilai >= 70) return 'B-';
        if ($nilai >= 66) return 'C+';
        if ($nilai >= 60) return 'C';
        if ($nilai >= 55) return 'C-';
        if ($nilai >= 40) return 'D';
        return 'E';
    }

    private function konversiIndeks($nilai)
    {
        if ($nilai >= 90) return 4.0;
        if ($nilai >= 86) return 3.5;
        if ($nilai >= 80) return 3.25;
        if ($nilai >= 76) return 3.0;
        if ($nilai >= 70) return 2.75;
        if ($nilai >= 66) return 2.5;
        if ($nilai >= 60) return 2.0;
        if ($nilai >= 55) return 1.5;
        if ($nilai >= 40) return 1.0;
        return 0.0;
    }
    private function forceRegenerateRekap($kelasId, $mahasiswaId)
    {
        $kelas = Kelas::find($kelasId);
        if (!$kelas) return;

        // 1. Ambil semua sisa pengumpulan mahasiswa di kelas ini (yang tersisa setelah dihapus)
        $semuaPengumpulan = Pengumpulan::whereHas('penilaian', fn($q) => $q->where('kelas_id', $kelasId))
            ->where('mahasiswa_id', $mahasiswaId)
            ->with('penilaian')
            ->get()
            ->groupBy('penilaian.kategori');

        // 2. Hitung komponen sisa nilai tugas
        $totalTugasDiKelas = Penilaian::where('kelas_id', $kelasId)->where('kategori', 'tugas')->count();
        $sumNilaiTugas = $semuaPengumpulan->get('tugas', collect())->sum('nilai_total');
        $totalTugas = $totalTugasDiKelas > 0 ? round($sumNilaiTugas / $totalTugasDiKelas, 2) : 0;

        $totalUts = $this->hitungRataRataKategori($semuaPengumpulan, 'uts');
        $totalUas = $this->hitungRataRataKategori($semuaPengumpulan, 'uas');

        // 3. Hitung ulang Nilai Akhir Angka
        $nilaiAkhir = round(
            ($totalTugas * $kelas->persentase_tugas / 100) +
                ($totalUts * $kelas->persentase_uts / 100) +
                ($totalUas * $kelas->persentase_uas / 100),
            2
        );

        // 4. Update data rekap (jika kosong nilainya akan otomatis menjadi 0)
        RekapNilai::updateOrCreate(
            [
                'mahasiswa_id' => $mahasiswaId,
                'kelas_id'     => $kelasId
            ],
            [
                'semester_id'       => $kelas->semester_id,
                'mata_kuliah_id'    => $kelas->mata_kuliah_id,
                'total_tugas'       => $totalTugas,
                'total_uts'         => $totalUts,
                'total_uas'         => $totalUas,
                'nilai_akhir_angka' => $nilaiAkhir,
                'nilai_huruf'       => $this->konversiHuruf($nilaiAkhir),
                'nilai_indeks'      => $this->konversiIndeks($nilaiAkhir),
            ]
        );
    }
}
