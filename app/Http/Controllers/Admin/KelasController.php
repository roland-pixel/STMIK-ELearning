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
                    $query->where('nama_kelas', 'like', "%{$q}%")
                        ->orWhere('kode_gabung', 'like', "%{$q}%")

                        ->orWhereHas('semester', fn($s) => $s->where('nama_semester', 'like', "%{$q}%"))
                        ->orWhereHas('dosen.user', fn($u) => $u->where('nama_lengkap', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"))
                        ->orWhereHas('mataKuliah', fn($mk) => $mk->where('nama_mk', 'like', "%{$q}%"));
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
        $mataKuliahs = MataKuliah::query()->where('jenis_mk', 'Umum')->orderBy('nama_mk')->get();
        $semesters = Semester::query()->orderByDesc('id')->get();

        return view('admin.kelases.create', compact('dosens', 'mataKuliahs', 'semesters'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'dosen_id' => ['required', 'exists:dosens,id'],
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

        if (((int)$validated['persentase_tugas'] + (int)$validated['persentase_uts'] + (int)$validated['persentase_uas']) !== 100) {
            return back()->withErrors(['persentase_uas' => 'Total persentase harus 100.'])->withInput();
        }

        if (empty($validated['kode_gabung'])) {
            do {
                $kode = strtoupper(Str::random(8));
            } while (Kelas::where('kode_gabung', $kode)->exists());
            $validated['kode_gabung'] = $kode;
        }

        $validated['uuid'] = (string) Str::uuid();
        Kelas::create($validated);

        return redirect()->route('admin.kelases.index')->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function edit(Kelas $kelase)
    {
        $kelase->load(['dosen.user', 'mataKuliah', 'semester']);
        $dosens = Dosen::query()->with('user')->orderByDesc('id')->get();
        $mataKuliahs = MataKuliah::query()->where('jenis_mk', 'Umum')->orderBy('nama_mk')->get();
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
            'kode_gabung' => ['required', 'string', 'max:50', Rule::unique('kelases', 'kode_gabung')->ignore($kelase->id)],
            'deskripsi' => ['nullable', 'string'],
            'persentase_tugas' => ['required', 'integer', 'min:0', 'max:100'],
            'persentase_uts' => ['required', 'integer', 'min:0', 'max:100'],
            'persentase_uas' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        if (((int)$validated['persentase_tugas'] + (int)$validated['persentase_uts'] + (int)$validated['persentase_uas']) !== 100) {
            return back()->withErrors(['persentase_uas' => 'Total persentase harus 100.'])->withInput();
        }

        try {
            DB::transaction(function () use ($kelase, $validated) {
                // Simpan persentase lama untuk perbandingan
                $pOld = [$kelase->persentase_tugas, $kelase->persentase_uts, $kelase->persentase_uas];

                $kelase->update($validated);

                // Cek apakah ada perubahan bobot nilai
                $pNew = [(int)$validated['persentase_tugas'], (int)$validated['persentase_uts'], (int)$validated['persentase_uas']];

                if ($pOld !== $pNew) {
                    Log::info("Bobot nilai kelas {$kelase->id} berubah, menghitung ulang rekap.");
                    $this->regenerateAllRekapNilaiKelas($kelase);
                }
                \Illuminate\Support\Facades\Cache::forget("kelas:global_detail:{$kelase->id}");
            });

            return redirect()->route('admin.kelases.index')->with('success', 'Kelas diperbarui dan rekap nilai disinkronkan.');
        } catch (\Exception $e) {
            Log::error('Update kelas gagal: ' . $e->getMessage());
            return back()->with('error', 'Gagal memperbarui kelas.');
        }
    }

    public function duplicate(Kelas $kelase)
    {
        $hasMahasiswa = $kelase->anggotaKelases()->exists();
        $hasMateri = $kelase->materis()->exists();
        $hasPenilaian = $kelase->penilaians()->exists();

        if ($hasMahasiswa || $hasMateri || $hasPenilaian) {
            return back()->with(
                'error',
                'Hanya kelas kosong yang bisa dicopy.'
            );
        }

        $newKelas = $kelase->replicate([
            'uuid',
            'kode_gabung',
            'created_at',
            'updated_at',
        ]);

        $newKelas->uuid = (string) Str::uuid();

        do {
            $kode = strtoupper(Str::random(8));
        } while (
            Kelas::where(
                'kode_gabung',
                $kode
            )->exists()
        );

        $newKelas->kode_gabung = $kode;

        $newKelas->nama_kelas =
            $kelase->nama_kelas . ' (Copy)';

        $newKelas->save();

        return back()->with(
            'success',
            'Kelas berhasil dicopy.'
        );
    }

    public function destroy(Kelas $kelase)
    {
        try {
            $hasMahasiswa = $kelase->anggotaKelases()->exists();
            $hasMateri = $kelase->materis()->exists();
            $hasPenilaian = $kelase->penilaians()->exists();

            if ($hasMahasiswa || $hasMateri || $hasPenilaian) {
                return back()->with(
                    'error',
                    'Kelas tidak bisa dihapus karena sudah memiliki data mahasiswa, materi, atau penilaian.'
                );
            }

            $kelase->delete();

            \Illuminate\Support\Facades\Cache::forget("kelas:global_detail:{$kelase->id}");

            return redirect()
                ->route('admin.kelases.index')
                ->with('success', 'Kelas berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {

            return redirect()
                ->route('admin.kelases.index')
                ->with(
                    'error',
                    'Kelas tidak bisa dihapus karena masih digunakan pada data akademik.'
                );
        }
    }

    /**
     * LOGIK REKAP NILAI (OPTIMIZED)
     */
    private function regenerateAllRekapNilaiKelas(Kelas $kelas)
    {
        // Ambil mahasiswa melalui relasi anggotaKelases
        $mahasiswaIds = $kelas->anggotaKelases()->pluck('mahasiswa_id');

        foreach ($mahasiswaIds as $mahasiswaId) {
            // Kita oper Objek $kelas langsung, bukan ID-nya saja
            $this->regenerateRekapNilaiForMahasiswa($kelas, $mahasiswaId);
        }
    }

    private function regenerateRekapNilaiForMahasiswa(Kelas $kelas, $mahasiswaId)
    {
        // 1. Ambil data pengumpulan & penilaian dalam satu tarikan untuk KELAS INI
        $semuaPengumpulan = Pengumpulan::whereHas('penilaian', fn($q) => $q->where('kelas_id', $kelas->id))
            ->where('mahasiswa_id', $mahasiswaId)
            ->with('penilaian')
            ->get()
            ->groupBy('penilaian.kategori');

        // 2. Hitung Rata-rata Tugas
        $totalTugasDiKelas = Penilaian::where('kelas_id', $kelas->id)->where('kategori', 'tugas')->count();
        $sumNilaiTugas = $semuaPengumpulan->get('tugas', collect())->sum('nilai_total');
        $totalTugas = $totalTugasDiKelas > 0 ? round($sumNilaiTugas / $totalTugasDiKelas, 2) : 0;

        // 3. Hitung Rata-rata UTS & UAS
        $totalUts = $this->hitungRataRataKategori($semuaPengumpulan, 'uts');
        $totalUas = $this->hitungRataRataKategori($semuaPengumpulan, 'uas');

        // 4. Hitung Nilai Akhir Angka
        $nilaiAkhir = round(
            ($totalTugas * $kelas->persentase_tugas / 100) +
                ($totalUts * $kelas->persentase_uts / 100) +
                ($totalUas * $kelas->persentase_uas / 100),
            2
        );

        // 5. 🔥 SIMPAN/UPDATE UNIK PER KELAS
        // Logic: Gunakan updateOrCreate dengan kombinasi mahasiswa_id DAN kelas_id.
        // Hasil: Jika mahasiswa mengulang (beda kelas), sistem otomatis membuat baris baru.
        RekapNilai::updateOrCreate(
            [
                'mahasiswa_id' => $mahasiswaId,
                'kelas_id'     => $kelas->id, // Kunci utama: Unik per kelas
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
        $data = $semuaPengumpulan->get($kategori, collect());
        return $data->isEmpty() ? 0 : round($data->avg('nilai_total'), 2);
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
