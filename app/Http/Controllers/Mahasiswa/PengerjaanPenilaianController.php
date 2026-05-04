<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\AnggotaKelas;
use App\Models\JawabanDetail;
use App\Models\Kelas;
use App\Models\Penilaian;
use App\Models\Pengumpulan;
use App\Models\RekapNilai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Carbon\Carbon;

class PengerjaanPenilaianController extends Controller
{
    /**
     * Pastikan mahasiswa terdaftar di kelas ini
     */
    private function ensureEnrolled(Kelas $kelas)
    {
        $mahasiswaId = Auth::user()?->mahasiswa?->id;
        abort_if(!$mahasiswaId, 403, 'Profil mahasiswa tidak ditemukan.');

        $isEnrolled = AnggotaKelas::where('kelas_id', $kelas->id)
            ->where('mahasiswa_id', $mahasiswaId)
            ->exists();

        abort_if(!$isEnrolled, 403, 'Anda tidak terdaftar di kelas ini.');

        return $mahasiswaId;
    }

    /**
     * Tampilan awal sebelum mulai mengerjakan (Instruksi)
     */
    public function show(Kelas $kelas, Penilaian $penilaian)
    {
        $mahasiswaId = $this->ensureEnrolled($kelas);

        $pengumpulan = Pengumpulan::where('penilaian_id', $penilaian->id)
            ->where('mahasiswa_id', $mahasiswaId)
            ->first();

        // Cek apakah ada soal selain PG
        $hasManualGrading = $penilaian->pertanyaans()
            ->whereIn('jenis_pertanyaan', ['essai', 'upload_file'])
            ->exists();

        return Inertia::render('Mahasiswa/Kelas/Tugas/Penilaian/Online/Show', [
            'kelas' => $kelas->only(['uuid', 'nama_kelas']),
            'penilaian' => [
                'uuid' => $penilaian->uuid,
                'judul' => $penilaian->judul,
                'instruksi' => $penilaian->instruksi,
                'kategori' => $penilaian->kategori,
                'tenggat_waktu' => $penilaian->tenggat_waktu,
                'is_selesai' => $pengumpulan && $pengumpulan->waktu_selesai !== null,
                // Nilai hanya tampil jika sudah selesai DAN isinya cuma PG
                'nilai_total' => (!$hasManualGrading) ? $pengumpulan?->nilai_total : null,
                'need_manual_grading' => $hasManualGrading,
            ]
        ]);
    }

    /**
     * Mulai mengerjakan (Membuka soal)
     */
    public function kerjakan(Kelas $kelas, Penilaian $penilaian)
    {
        $mahasiswaId = $this->ensureEnrolled($kelas);

        // 1. Cek tenggat waktu
        if ($penilaian->tenggat_waktu && Carbon::now()->isAfter($penilaian->tenggat_waktu)) {
            return back()->withErrors(['message' => 'Waktu pengerjaan sudah berakhir.']);
        }

        // 2. Ambil atau buat data pengumpulan (untuk mencatat waktu_mulai)
        $pengumpulan = Pengumpulan::firstOrCreate(
            ['penilaian_id' => $penilaian->id, 'mahasiswa_id' => $mahasiswaId],
            ['uuid' => Str::uuid(), 'waktu_mulai' => Carbon::now()]
        );

        // Jika sudah selesai, jangan izinkan buka soal lagi
        if ($pengumpulan->waktu_selesai) {
            return redirect()->route('mahasiswa.kelas.penilaian.online.show', [$kelas->uuid, $penilaian->uuid])
                ->with('error', 'Anda sudah mengumpulkan tugas ini.');
        }

        // 3. Load soal dan opsi (Sembunyikan is_benar untuk keamanan!)
        $penilaian->load([
            'pertanyaans' => fn($q) => $q->orderBy('nomor_urut'),
            'pertanyaans.opsiJawabans' => fn($q) => $q->select('id', 'pertanyaan_id', 'teks_opsi'),
            'pertanyaans.images'
        ]);

        $penilaian->pertanyaans->transform(function ($pt) {
            $pt->images->transform(function ($img) {
                // Ini kuncinya: ubah path mentah jadi URL publik
                $img->url = \Illuminate\Support\Facades\Storage::disk('public')->url($img->path);
                return $img;
            });
            return $pt;
        });

        return Inertia::render('Mahasiswa/Kelas/Tugas/Penilaian/Online/Kerjakan', [
            'kelas' => $kelas,
            'penilaian' => $penilaian,
            'pengumpulan' => $pengumpulan
        ]);
    }

    /**
     * Simpan jawaban (Submit)
     */
    public function submit(Request $request, Kelas $kelas, Penilaian $penilaian)
    {
        $mahasiswaId = $this->ensureEnrolled($kelas);

        $pengumpulan = Pengumpulan::where('penilaian_id', $penilaian->id)
            ->where('mahasiswa_id', $mahasiswaId)
            ->firstOrFail();

        if ($pengumpulan->waktu_selesai) {
            return abort(403, 'Jawaban sudah dikirim sebelumnya.');
        }

        // ✅ FIX VALIDATION: Handle file array dengan index pertanyaan
        $request->validate([
            'jawaban' => 'required|array',
            'jawaban.*.pertanyaan_id' => 'required|exists:pertanyaans,id',
            'jawaban.*.opsi_jawaban_id' => 'nullable|exists:opsi_jawabans,id',
            'jawaban.*.text_jawaban' => 'nullable|string|max:1000',
            // ✅ FIX: File validation per index array
            'jawaban.*.file' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx,zip,txt,rar',
        ]);

        DB::beginTransaction();
        try {
            $this->simpanJawabanDanHitungNilai($request, $pengumpulan, $penilaian);
            $this->regenerateRekapNilai($pengumpulan->id);
            DB::commit();

            // ... success message ...
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    private function simpanJawabanDanHitungNilai($request, $pengumpulan, $penilaian)
    {
        foreach ($request->jawaban as $index => $item) {  // ✅ $index = 0,1,2,...
            $pertanyaan = $penilaian->pertanyaans()->find($item['pertanyaan_id']);
            $nilaiPerSoal = 0;
            $filePath = null;

            // 🔥 FIX 1: Nama file BENAR SEKARANG!
            $fileKey = "jawaban.{$index}.file";  // jawaban[0].file, jawaban[1].file
            if ($request->hasFile($fileKey)) {
                $file = $request->file($fileKey);
                $filePath = $file->store("jawaban/{$pengumpulan->uuid}", 'public');  // ✅ Nama folder lebih jelas
                \Log::info("File uploaded: {$filePath}");  // Debug log
            }

            // Auto-grading PG (tetap sama)
            if ($pertanyaan->jenis_pertanyaan === 'pilihan_ganda' && !empty($item['opsi_jawaban_id'])) {
                $isCorrect = DB::table('opsi_jawabans')
                    ->where('id', $item['opsi_jawaban_id'])
                    ->where('is_benar', true)
                    ->exists();
                if ($isCorrect) {
                    $nilaiPerSoal = $pertanyaan->bobot_soal;
                }
            }

            JawabanDetail::create([
                'pengumpulan_id' => $pengumpulan->id,
                'pertanyaan_id' => $pertanyaan->id,
                'opsi_jawaban_id' => $item['opsi_jawaban_id'] ?? null,
                'text_jawaban' => $item['text_jawaban'] ?? null,
                'file_jawaban' => $filePath,  // ✅ SEKARANG ISI!
                'nilai_per_soal' => $nilaiPerSoal,
            ]);
        }

        $this->updatePengumpulanTotal($pengumpulan->id);
        $pengumpulan->update(['waktu_selesai' => Carbon::now()]);
    }

    /**
     * Hitung ulang nilai_total pengumpulan ✅ SAMA PERSIS
     */
    private function updatePengumpulanTotal($pengumpulanId)
    {
        $data = JawabanDetail::select('jawaban_details.nilai_per_soal', 'pertanyaans.bobot_soal')
            ->join('pertanyaans', 'jawaban_details.pertanyaan_id', '=', 'pertanyaans.id')
            ->where('jawaban_details.pengumpulan_id', $pengumpulanId)
            ->get();

        $nilaiTotal = 0;
        if (!$data->isEmpty()) {
            $sumNilai = $data->sum('nilai_per_soal');
            $sumBobot = $data->sum('bobot_soal');
            $nilaiTotal = $sumBobot > 0 ? round(($sumNilai / $sumBobot) * 100, 2) : 0;
        }

        Pengumpulan::where('id', $pengumpulanId)->update(['nilai_total' => $nilaiTotal]);
    }

    /**
     * Regenerate rekap_nilais (OPTIMIZED & KRONOLOGIS)
     * Tetap konsisten dengan aturan: 1 Kelas = 1 Record Nilai
     */
    private function regenerateRekapNilai($pengumpulanId)
    {
        $pengumpulan = Pengumpulan::with(['penilaian.kelas', 'mahasiswa'])->find($pengumpulanId);
        if (!$pengumpulan) return;

        $mahasiswaId = $pengumpulan->mahasiswa_id;
        $kelasId     = $pengumpulan->penilaian->kelas_id;
        $kelas       = $pengumpulan->penilaian->kelas;
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

        $payload = [
            'semester_id'       => $kelas->semester_id,
            'mata_kuliah_id'    => $kelas->mata_kuliah_id,
            'total_tugas'       => $totalTugas,
            'total_uts'         => $totalUts,
            'total_uas'         => $totalUas,
            'nilai_akhir_angka' => $nilaiAkhir,
            'nilai_huruf'       => $this->konversiHuruf($nilaiAkhir),
            'nilai_indeks'      => $this->konversiIndeks($nilaiAkhir),
        ];

        // 4. 🔥 SIMPAN/UPDATE UNIK PER KELAS
        // Menggunakan updateOrCreate agar setiap kelas memiliki record nilai sendiri.
        // Ini memastikan riwayat nilai mahasiswa tetap terjaga per semester/kelas.
        RekapNilai::updateOrCreate(
            [
                'mahasiswa_id' => $mahasiswaId,
                'kelas_id'     => $kelasId
            ],
            $payload
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
