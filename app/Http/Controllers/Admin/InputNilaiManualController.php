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

        $kelasKosong = $query->latest()->paginate(20);

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
        $request->validate([
            'data_nilai' => 'required|array',
            'data_nilai.*.tugas' => 'required|numeric|min:0|max:100',
            'data_nilai.*.uts'   => 'required|numeric|min:0|max:100',
            'data_nilai.*.uas'   => 'required|numeric|min:0|max:100',
        ]);

        try {
            DB::beginTransaction();

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
                $mapPenilaian[$kat] = $penilaian->id;
            }

            foreach ($request->data_nilai as $mahasiswa_id => $nilai) {
                // Kita simpan salah satu ID pengumpulan untuk trigger regenerasi rekap
                $lastPengumpulanId = null;

                foreach ($kategoriList as $kat) {
                    $p = Pengumpulan::create([
                        'uuid'         => (string) Str::uuid(),
                        'penilaian_id' => $mapPenilaian[$kat],
                        'mahasiswa_id' => $mahasiswa_id,
                        'waktu_mulai'  => now(),
                        'waktu_selesai' => now(),
                        'nilai_total'  => $nilai[$kat],
                    ]);
                    $lastPengumpulanId = $p->id;
                }

                // Panggil rekap SEKALI per mahasiswa setelah semua nilai kategori masuk
                $this->regenerateRekapNilai($lastPengumpulanId);
            }

            DB::commit();
            return redirect()->route('admin.input_nilai_manual.index')->with('success', 'Nilai berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * Regenerate rekap_nilais untuk mahasiswa di kelas
     * (Logika baru: Menyimpan nilai per kelas agar tidak tertimpa saat mengulang)
     */
    private function regenerateRekapNilai($pengumpulanId)
    {
        $pengumpulan = Pengumpulan::with(['penilaian.kelas', 'mahasiswa'])->find($pengumpulanId);
        if (!$pengumpulan) return;

        $mahasiswaId = $pengumpulan->mahasiswa_id;
        $kelas = $pengumpulan->penilaian->kelas;
        $kelasId = $kelas->id;

        // 1. Ambil semua data pengumpulan mahasiswa ini DI KELAS INI
        $semuaPengumpulan = Pengumpulan::whereHas('penilaian', fn($q) => $q->where('kelas_id', $kelasId))
            ->where('mahasiswa_id', $mahasiswaId)
            ->get()
            ->groupBy('penilaian.kategori');

        // 2. Hitung Rata-rata Tugas
        $totalTugasDiKelas = Penilaian::where('kelas_id', $kelasId)->where('kategori', 'tugas')->count();
        $sumNilaiTugas = $semuaPengumpulan->get('tugas', collect())->sum('nilai_total');
        $totalTugas = $totalTugasDiKelas > 0 ? $sumNilaiTugas / $totalTugasDiKelas : 0;

        // 3. Ambil Nilai UTS & UAS
        $totalUts = $semuaPengumpulan->has('uts') ? $semuaPengumpulan->get('uts')->avg('nilai_total') : 0;
        $totalUas = $semuaPengumpulan->has('uas') ? $semuaPengumpulan->get('uas')->avg('nilai_total') : 0;

        // 4. Hitung Nilai Akhir
        $nilaiAkhir = round(
            ($totalTugas * $kelas->persentase_tugas / 100) +
                ($totalUts * $kelas->persentase_uts / 100) +
                ($totalUas * $kelas->persentase_uas / 100),
            2
        );

        // 5. SIMPAN/UPDATE NILAI HANYA UNTUK KELAS INI (Tidak ada perbandingan nilai tertinggi)
        RekapNilai::updateOrCreate(
            [
                'mahasiswa_id' => $mahasiswaId,
                'kelas_id'     => $kelasId, // Kunci utama: Unik per kelas, aman untuk mengulang
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
