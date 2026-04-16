<?php

namespace App\Http\Controllers\Admin;

use App\Models\Kelas; // Sesuaikan nama model kelas Anda
use App\Models\Mahasiswa;
use App\Models\Penilaian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\MataKuliah;
use App\Models\Pengumpulan;
use App\Models\RekapNilai;
use Illuminate\Support\Str;

class InputNilaiManualController extends Controller
{
    public function index(Request $request)
    {
        // 1. Query dasar: Ambil kelas yang belum punya nilai
        $query = Kelas::with(['mataKuliah', 'dosen.user'])
            ->whereDoesntHave('penilaians');

        // 2. Filter berdasarkan Mata Kuliah (jika ada)
        $query->when($request->mata_kuliah_id, function ($q) use ($request) {
            return $q->where('mata_kuliah_id', $request->mata_kuliah_id);
        });

        // 3. Filter berdasarkan Dosen (jika ada)
        $query->when($request->dosen_id, function ($q) use ($request) {
            return $q->where('dosen_id', $request->dosen_id);
        });

        $kelasKosong = $query->latest()->get();

        // 4. Ambil data untuk dropdown filter
        $listMataKuliah = MataKuliah::orderBy('nama_mk')->get();
        $listDosen = Dosen::with('user')->get();

        return view('admin.input_nilai_manual.index', compact('kelasKosong', 'listMataKuliah', 'listDosen'));
    }

    /**
     * Form input nilai untuk semua mahasiswa di kelas tersebut
     */
    public function create($kelas_id)
    {
        $kelas = Kelas::with(['mataKuliah', 'anggotaKelases.mahasiswa.user'])->findOrFail($kelas_id);

        // Proteksi tambahan: Jika tiba-tiba sudah ada nilai, tendang balik
        if ($kelas->penilaians()->exists()) {
            return redirect()->route('admin.input_nilai_manual.index')
                ->with('error', 'Kelas ini sudah memiliki nilai. Admin tidak dapat menginput manual.');
        }

        return view('admin.input_nilai_manual.create', compact('kelas'));
    }

    /**
     * Simpan nilai secara massal
     */
    public function store(Request $request, $kelas_id)
    {
        // 1. Validasi Input
        $request->validate([
            'data_nilai' => 'required|array',
            'data_nilai.*.tugas' => 'required|numeric|min:0|max:100',
            'data_nilai.*.uts'   => 'required|numeric|min:0|max:100',
            'data_nilai.*.uas'   => 'required|numeric|min:0|max:100',
        ]);

        try {
            DB::beginTransaction();

            // 2. Buat 3 Kategori Penilaian di tabel 'penilaians'
            // Ini agar sistem tahu kelas ini punya komponen Tugas, UTS, dan UAS
            $kategoriList = ['tugas', 'uts', 'uas'];
            $mapPenilaian = [];

            foreach ($kategoriList as $kat) {
                $penilaian = Penilaian::create([
                    'uuid'           => (string) Str::uuid(),
                    'kelas_id'       => $kelas_id,
                    'judul'          => 'Nilai ' . strtoupper($kat) . ' (Input Manual Admin)',
                    'instruksi'      => 'Diinput secara massal oleh Administrator.',
                    'kategori'       => $kat,
                    'mode_penilaian' => 'manual',
                ]);

                // Simpan ID penilaian yang baru dibuat untuk digunakan di tabel pengumpulans
                $mapPenilaian[$kat] = $penilaian->id;
            }

            // 3. Simpan Nilai masing-masing Mahasiswa ke tabel 'pengumpulans'
            foreach ($request->data_nilai as $mahasiswa_id => $nilai) {

                // Simpan Nilai Tugas
                $tugasPengumpulan = Pengumpulan::create([
                    'uuid'         => (string) Str::uuid(),
                    'penilaian_id' => $mapPenilaian['tugas'],
                    'mahasiswa_id' => $mahasiswa_id,
                    'waktu_mulai'  => now(),
                    'waktu_selesai' => now(),
                    'nilai_total'  => $nilai['tugas'],
                ]);

                // Simpan Nilai UTS
                Pengumpulan::create([
                    'uuid'         => (string) Str::uuid(),
                    'penilaian_id' => $mapPenilaian['uts'],
                    'mahasiswa_id' => $mahasiswa_id,
                    'waktu_mulai'  => now(),
                    'waktu_selesai' => now(),
                    'nilai_total'  => $nilai['uts'],
                ]);

                // Simpan Nilai UAS
                Pengumpulan::create([
                    'uuid'         => (string) Str::uuid(),
                    'penilaian_id' => $mapPenilaian['uas'],
                    'mahasiswa_id' => $mahasiswa_id,
                    'waktu_mulai'  => now(),
                    'waktu_selesai' => now(),
                    'nilai_total'  => $nilai['uas'],
                ]);

                $this->regenerateRekapNilai($tugasPengumpulan->id);
            }

            DB::commit();

            return redirect()->route('admin.input_nilai_manual.index')
                ->with('success', 'Data penilaian dan pengumpulan nilai berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat menyimpan: ' . $e->getMessage());
        }
    }

    /**
     * Regenerate rekap_nilais untuk mahasiswa di kelas
     * (SUDAH RATA‑RATA BERDASARKAN JUMLAH TUGAS DI KELAS)
     */
    private function regenerateRekapNilai($pengumpulanId)
    {
        $pengumpulan = Pengumpulan::with(['penilaian.kelas', 'mahasiswa'])->find($pengumpulanId);
        if (!$pengumpulan) {
            return;
        }

        $mahasiswaId = $pengumpulan->mahasiswa_id;
        $kelasId = $pengumpulan->penilaian->kelas_id;
        $kelas = Kelas::find($kelasId);

        // 1. Semua pengumpulan mahasiswa di kelas ini
        $semuaPengumpulan = Pengumpulan::whereHas('penilaian', fn($q) => $q->where('kelas_id', $kelasId))
            ->where('mahasiswa_id', $mahasiswaId)
            ->get()
            ->groupBy('penilaian.kategori');

        // 2. Hitung total tugas di kelas (semua tugas)
        $totalTugasDiKelas = Penilaian::where('kelas_id', $kelasId)
            ->where('kategori', 'tugas')
            ->count();

        // 3. Hitung total nilai tugas yang sudah ada
        $pengumpulanTugas = $semuaPengumpulan->get('tugas', collect());
        $sumNilaiTugas = $pengumpulanTugas->sum('nilai_total');

        // 4. total_tugas = rata‑rata dari semua tugas kelas
        $totalTugas = $totalTugasDiKelas > 0
            ? $sumNilaiTugas / $totalTugasDiKelas
            : 0;

        // 5. UTS & UAS
        $totalUts = 0;
        $totalUas = 0;

        if ($semuaPengumpulan->has('uts')) {
            $totalUts = $semuaPengumpulan->get('uts')->sum('nilai_total');
        }
        if ($semuaPengumpulan->has('uas')) {
            $totalUas = $semuaPengumpulan->get('uas')->sum('nilai_total');
        }

        // 6. Hitung nilai akhir
        $nilaiAkhir = round(
            ($totalTugas * $kelas->persentase_tugas / 100) +
                ($totalUts * $kelas->persentase_uts / 100) +
                ($totalUas * $kelas->persentase_uas / 100),
            2
        );

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

    /**
     * Konversi nilai angka ke huruf (sama di seluruh aplikasi)
     */
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

    /**
     * Konversi nilai angka ke indeks
     */
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
