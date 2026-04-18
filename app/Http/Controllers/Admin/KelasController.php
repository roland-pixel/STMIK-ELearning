<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\Kelas;
use App\Models\MataKuliah;
use App\Models\Pengumpulan;
use App\Models\Penilaian;
use App\Models\RekapNilai;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class KelasController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');

        // filter semester: active | inactive | all (opsional)
        // default: active
        $semesterStatus = $request->get('semester_status', 'active');

        $kelases = Kelas::query()
            ->with(['dosen.user', 'mataKuliah', 'semester'])
            ->when(
                $semesterStatus !== 'all',
                fn($query) => $query->whereHas(
                    'semester',
                    fn($s) => $s->where('status_aktif', $semesterStatus)
                )
            )
            ->when(
                $q,
                fn($query) => $query->where(function ($query) use ($q) {
                    $query
                        ->where('nama_kelas', 'like', "%{$q}%")
                        ->orWhere('kode_gabung', 'like', "%{$q}%")
                        ->orWhereHas(
                            'mataKuliah',
                            fn($mk) => $mk
                                ->where('kode_mk', 'like', "%{$q}%")
                                ->orWhere('nama_mk', 'like', "%{$q}%")
                        )
                        ->orWhereHas(
                            'semester',
                            fn($s) => $s->where('nama_semester', 'like', "%{$q}%")
                        )
                        ->orWhereHas(
                            'dosen.user',
                            fn($u) => $u
                                ->where('nama_lengkap', 'like', "%{$q}%")
                                ->orWhere('email', 'like', "%{$q}%")
                        );
                })
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.kelases.index', compact('kelases', 'q', 'semesterStatus'));
    }

    public function create()
    {
        $dosens = Dosen::query()->with('user')->orderByDesc('id')->get();

        // ✅ Hanya MK dengan jenis_mk = "Umum"
        $mataKuliahs = MataKuliah::query()
            ->where('jenis_mk', 'Umum')
            ->orderBy('nama_mk')
            ->get();

        $semesters = Semester::query()->orderByDesc('id')->get();

        return view('admin.kelases.create', compact('dosens', 'mataKuliahs', 'semesters'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'dosen_id' => ['required', 'exists:dosens,id'],

            // ✅ Wajib MK "Umum" (aman dari request yang dimanipulasi)
            'mata_kuliah_id' => [
                'required',
                Rule::exists('mata_kuliahs', 'id')->where(fn($q) => $q->where('jenis_mk', 'Umum')),
            ],

            'semester_id' => ['required', 'exists:semesters,id'],
            'nama_kelas' => ['required', 'string', 'max:255'],
            'kode_gabung' => ['nullable', 'string', 'max:50', 'unique:kelases,kode_gabung'],
            'deskripsi' => ['nullable', 'string'],
            'persentase_tugas' => ['required', 'integer', 'min:0', 'max:100'],
            'persentase_uts' => ['required', 'integer', 'min:0', 'max:100'],
            'persentase_uas' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $total = (int) $validated['persentase_tugas']
            + (int) $validated['persentase_uts']
            + (int) $validated['persentase_uas'];

        if ($total !== 100) {
            return back()
                ->withErrors(['persentase_uas' => 'Total persentase Tugas + UTS + UAS harus 100.'])
                ->withInput();
        }

        // Auto generate kode_gabung jika kosong
        if (empty($validated['kode_gabung'])) {
            do {
                $kode = strtoupper(Str::random(8));
            } while (Kelas::where('kode_gabung', $kode)->exists());

            $validated['kode_gabung'] = $kode;
        }

        $validated['uuid'] = (string) Str::uuid();

        Kelas::create($validated);

        return redirect()
            ->route('admin.kelases.index')
            ->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function edit(Kelas $kelase)
    {
        $kelase->load(['dosen.user', 'mataKuliah', 'semester']);

        $dosens = Dosen::query()->with('user')->orderByDesc('id')->get();

        // ✅ Hanya MK dengan jenis_mk = "Umum"
        $mataKuliahs = MataKuliah::query()
            ->where('jenis_mk', 'Umum')
            ->orderBy('nama_mk')
            ->get();

        $semesters = Semester::query()->orderByDesc('id')->get();

        return view('admin.kelases.edit', compact('kelase', 'dosens', 'mataKuliahs', 'semesters'));
    }

    public function update(Request $request, Kelas $kelase)
    {
        $validated = $request->validate([
            'dosen_id' => ['required', 'exists:dosens,id'],
            'mata_kuliah_id' => [
                'required',
                Rule::exists('mata_kuliahs', 'id')->where(fn($q) => $q->where('jenis_mk', 'Umum')),
            ],
            'semester_id' => ['required', 'exists:semesters,id'],
            'nama_kelas' => ['required', 'string', 'max:255'],
            'kode_gabung' => [
                'required',
                'string',
                'max:50',
                Rule::unique('kelases', 'kode_gabung')->ignore($kelase->id),
            ],
            'deskripsi' => ['nullable', 'string'],
            'persentase_tugas' => ['required', 'integer', 'min:0', 'max:100'],
            'persentase_uts' => ['required', 'integer', 'min:0', 'max:100'],
            'persentase_uas' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $total = (int) $validated['persentase_tugas']
            + (int) $validated['persentase_uts']
            + (int) $validated['persentase_uas'];

        if ($total !== 100) {
            return back()
                ->withErrors(['persentase_uas' => 'Total persentase Tugas + UTS + UAS harus 100.'])
                ->withInput();
        }

        try {
            DB::transaction(function () use ($kelase, $validated) {
                // 1. Simpan persentase lama untuk cek perubahan
                $persentaseLama = [
                    'tugas' => $kelase->persentase_tugas,
                    'uts' => $kelase->persentase_uts,
                    'uas' => $kelase->persentase_uas
                ];

                // 2. UPDATE kelas
                $kelase->update($validated);

                // 🔥 3. CEK APAKAH PERSENTASE BERUBAH
                $persentaseBaru = [
                    'tugas' => $validated['persentase_tugas'],
                    'uts' => $validated['persentase_uts'],
                    'uas' => $validated['persentase_uas']
                ];

                $adaPerubahanPersentase = $persentaseLama !== $persentaseBaru;

                if ($adaPerubahanPersentase) {
                    Log::info("Persentase kelas {$kelase->nama_kelas} berubah, regenerate rekap nilai");
                    $this->regenerateAllRekapNilaiKelas($kelase->id);
                }
            });

            return redirect()
                ->route('admin.kelases.index')
                ->with('success', 'Kelas berhasil diperbarui. Rekap nilai otomatis terupdate!');
        } catch (\Exception $e) {
            Log::error('Update kelas admin gagal: ' . $e->getMessage());
            return back()->with('error', 'Gagal memperbarui kelas. Coba lagi.');
        }
    }

    public function destroy(Kelas $kelas)
    {
        $kelas->delete();

        return redirect()
            ->route('admin.kelases.index')
            ->with('success', 'Kelas berhasil dihapus.');
    }

    private function regenerateAllRekapNilaiKelas($kelasId)
    {
        $kelas = Kelas::find($kelasId);
        if (!$kelas) return;

        // Ambil SEMUA mahasiswa di kelas
        $mahasiswaIds = $kelas->anggotaKelases()->pluck('mahasiswa_id');

        foreach ($mahasiswaIds as $mahasiswaId) {
            $this->regenerateRekapNilaiForMahasiswa($kelasId, $mahasiswaId);
        }
    }

    /**
     * Regenerate rekap_nilai untuk 1 mahasiswa di kelas
     */
    private function regenerateRekapNilaiForMahasiswa($kelasId, $mahasiswaId)
    {
        $kelas = Kelas::find($kelasId);
        if (!$kelas) return;

        // 1. Semua pengumpulan mahasiswa di kelas ini
        $semuaPengumpulan = Pengumpulan::whereHas('penilaian', fn($q) => $q->where('kelas_id', $kelasId))
            ->where('mahasiswa_id', $mahasiswaId)
            ->with('penilaian')
            ->get()
            ->groupBy('penilaian.kategori');

        // 2. Total tugas di kelas
        $totalTugasDiKelas = Penilaian::where('kelas_id', $kelasId)
            ->where('kategori', 'tugas')
            ->count();

        // 3. Rata-rata tugas
        $pengumpulanTugas = $semuaPengumpulan->get('tugas', collect());
        $sumNilaiTugas = $pengumpulanTugas->sum('nilai_total');
        $totalTugas = $totalTugasDiKelas > 0 ? round($sumNilaiTugas / $totalTugasDiKelas, 2) : 0;

        // 4. UTS & UAS
        $totalUts = $this->hitungRataRataKategori($semuaPengumpulan, 'uts');
        $totalUas = $this->hitungRataRataKategori($semuaPengumpulan, 'uas');

        // 5. Nilai akhir dengan persentase BARU
        $nilaiAkhir = round(
            ($totalTugas * $kelas->persentase_tugas / 100) +
                ($totalUts * $kelas->persentase_uts / 100) +
                ($totalUas * $kelas->persentase_uas / 100),
            2
        );

        // 6. Update rekap_nilai
        RekapNilai::updateOrCreate(
            [
                'mahasiswa_id' => $mahasiswaId,
                'kelas_id' => $kelasId
            ],
            [
                'semester_id' => $kelas->semester_id,
                'mata_kuliah_id' => $kelas->mata_kuliah_id,
                'total_tugas' => $totalTugas,
                'total_uts' => $totalUts,
                'total_uas' => $totalUas,
                'nilai_akhir_angka' => $nilaiAkhir,
                'nilai_huruf' => $this->konversiHuruf($nilaiAkhir),
                'nilai_indeks' => $this->konversiIndeks($nilaiAkhir),
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
}
