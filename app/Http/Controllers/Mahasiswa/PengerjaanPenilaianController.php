<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\AnggotaKelas;
use App\Models\JawabanDetail;
use App\Models\Kelas;
use App\Models\Penilaian;
use App\Models\Pengumpulan;
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

        $request->validate([
            'jawaban' => 'required|array',
            'jawaban.*.pertanyaan_id' => 'required|exists:pertanyaans,id',
            'jawaban.*.opsi_jawaban_id' => 'nullable|exists:opsi_jawabans,id',
            'jawaban.*.text_jawaban' => 'nullable|string',
            'jawaban.*.file' => 'nullable|file|max:10240', // max 10MB
        ]);

        DB::beginTransaction();
        try {
            $nilaiTotalPG = 0;

            // Cek apakah kuis ini mengandung unsur manual grading
            $hasManualGrading = $penilaian->pertanyaans()
                ->whereIn('jenis_pertanyaan', ['essai', 'upload_file'])
                ->exists();

            foreach ($request->jawaban as $item) {
                $pertanyaan = $penilaian->pertanyaans()->find($item['pertanyaan_id']);
                $nilaiPerSoal = 0;
                $filePath = null;

                // Handle File (tetap sama)
                if ($request->hasFile("jawaban.{$item['pertanyaan_id']}.file")) {
                    $file = $request->file("jawaban.{$item['pertanyaan_id']}.file");
                    $filePath = $file->store("pengumpulan/{$pengumpulan->uuid}", 'public');
                }

                // Auto-grading PG
                if ($pertanyaan->jenis_pertanyaan === 'pilihan_ganda' && !empty($item['opsi_jawaban_id'])) {
                    $isCorrect = DB::table('opsi_jawabans')
                        ->where('id', $item['opsi_jawaban_id'])
                        ->where('is_benar', true)
                        ->exists();

                    if ($isCorrect) {
                        $nilaiPerSoal = $pertanyaan->bobot_soal;
                    }
                    $nilaiTotalPG += $nilaiPerSoal;
                }

                JawabanDetail::create([
                    'pengumpulan_id' => $pengumpulan->id,
                    'pertanyaan_id' => $pertanyaan->id,
                    'opsi_jawaban_id' => $item['opsi_jawaban_id'] ?? null,
                    'text_jawaban' => $item['text_jawaban'] ?? null,
                    'file_jawaban' => $filePath,
                    'nilai_per_soal' => ($pertanyaan->jenis_pertanyaan === 'pilihan_ganda') ? $nilaiPerSoal : null,
                    // Kita set NULL untuk essai agar dosen tahu ini belum dinilai
                ]);
            }

            // Update status pengumpulan
            $pengumpulan->update([
                'waktu_selesai' => Carbon::now(),
                // Jika ada essai, nilai total sementara adalah nilai PG saja, 
                // atau bisa kamu set 0 dulu sampai dosen selesai koreksi.
                'nilai_total' => $nilaiTotalPG,
            ]);

            DB::commit();

            $msg = $hasManualGrading
                ? 'Jawaban berhasil dikirim. Nilai akan muncul setelah dikoreksi dosen.'
                : 'Jawaban berhasil dikirim.';

            return redirect()->route('mahasiswa.kelas.penilaian.online.show', [$kelas->uuid, $penilaian->uuid])
                ->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }
}
