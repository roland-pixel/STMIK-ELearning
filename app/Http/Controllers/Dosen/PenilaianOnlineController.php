<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\OpsiJawaban;
use App\Models\Pengumpulan;
use App\Models\Penilaian;
use App\Models\Pertanyaan;
use App\Models\PertanyaanImage;
use Aws\S3\S3Client; // <-- Tambahan import SDK S3 AWS
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PenilaianOnlineController extends Controller
{
    private function ensureOwner(Kelas $kelas): void
    {
        $dosenId = Auth::user()?->dosen?->id;
        abort_if(!$dosenId, 403, 'Akun dosen tidak valid.');
        abort_if((int) $kelas->dosen_id !== (int) $dosenId, 403, 'Tidak berhak mengakses kelas ini.');
    }

    private function ensurePenilaianInKelas(Kelas $kelas, Penilaian $penilaian): void
    {
        abort_if((int) $penilaian->kelas_id !== (int) $kelas->id, 404, 'Penilaian tidak ditemukan di kelas ini.');
    }

    private function ensurePenilaianOnline(Penilaian $penilaian): void
    {
        abort_if($penilaian->mode_penilaian !== 'online', 404, 'Penilaian ini bukan penilaian online.');
    }

    public function index(Request $request, Kelas $kelas)
    {
        // 1. Ambil Parameter Query
        $openId = $request->query('open') ? (int) $request->query('open') : null;
        $currentTab = $request->query('tab', 'pertanyaan');
        $with = $request->query('with', 'summary');

        if ($openId) {
            $with = 'full';
        }

        // 2. Query Utama Penilaian
        $query = Penilaian::query()
            ->where('kelas_id', $kelas->id)
            ->where('mode_penilaian', 'online')
            ->latest();

        if ($openId) {
            $query->where('id', $openId);
        }

        // Eager Loading
        if ($with === 'full') {
            $query->with([
                'pertanyaans' => fn($q) => $q->orderBy('nomor_urut'),
                'pertanyaans.opsiJawabans',
                'pertanyaans.images',
            ]);
        } else {
            $query->with([
                'pertanyaans' => fn($q) => $q->select(
                    'id',
                    'penilaian_id',
                    'nomor_urut',
                    'text_pertanyaan',
                    'jenis_pertanyaan',
                    'bobot_soal'
                )->orderBy('nomor_urut'),
            ]);
        }

        // 3. Mapping Data Penilaian
        $penilaians = $query->get()->map(function ($p) use ($with) {
            $pertanyaans = $p->pertanyaans ?? collect();

            $base = [
                'id' => $p->id,
                'uuid' => $p->uuid,
                'judul' => $p->judul,
                'instruksi' => $p->instruksi,
                'kategori' => $p->kategori,
                'mode_penilaian' => $p->mode_penilaian,
                'tenggat_waktu' => optional($p->tenggat_waktu)->toIso8601String(),
                'created_at' => optional($p->created_at)->toIso8601String(),
                'meta' => [
                    'jumlah_pertanyaan' => $pertanyaans->count(),
                    'total_bobot' => (int) $pertanyaans->sum('bobot_soal'),
                ],
            ];

            $base['pertanyaans'] = $pertanyaans->map(function ($pt) use ($with) {
                $row = [
                    'id' => $pt->id,
                    'nomor_urut' => $pt->nomor_urut,
                    'text_pertanyaan' => $pt->text_pertanyaan,
                    'jenis_pertanyaan' => $pt->jenis_pertanyaan,
                    'bobot_soal' => (int) $pt->bobot_soal,
                ];

                if ($with === 'full') {
                    $row['opsi_jawabans'] = $pt->opsiJawabans->map(fn($o) => [
                        'id' => $o->id,
                        'teks_opsi' => $o->teks_opsi,
                        'is_benar' => (bool) $o->is_benar,
                    ])->values();

                    $row['images'] = $pt->images->map(fn($img) => [
                        'id' => $img->id,
                        'path' => $img->path,
                        'url' => \Storage::disk('public')->url($img->path),
                    ])->values();
                }

                return $row;
            })->values();

            return $base;
        });

        // 4. Logic Data Koreksi
        $dataKoreksi = [];
        $currentDetail = null;
        $totalPoinKuis = 0;

        if ($openId && $currentTab === 'jawaban') {

            // 🔥 Ambil semua pengumpulan
            $pengumpulans = Pengumpulan::where('penilaian_id', $openId)
                ->with('mahasiswa.user:id,nama_lengkap,email')
                ->get();

            // 🔹 Dropdown mahasiswa
            $dataKoreksi = $pengumpulans->map(fn($item) => [
                'id' => $item->id,
                'email' => $item->mahasiswa->user->email,
                'nama' => $item->mahasiswa->user->nama_lengkap,
            ]);

            // 🔥 FIX UTAMA: AUTO PILIH MAHASISWA PERTAMA
            $targetId = $request->query('pengumpulan_id');

            if (!$targetId && $pengumpulans->isNotEmpty()) {
                $targetId = $pengumpulans->first()->id;
            }

            // 🔥 Query detail jawaban
            $pengumpulan = Pengumpulan::where('id', $targetId)
                ->with([
                    'mahasiswa.user',
                    'jawabanDetails',
                    'jawabanDetails.pertanyaan.opsiJawabans',
                    'jawabanDetails.pertanyaan.images',
                ])
                ->first();

            // 🔥 Mapping detail dengan tambahan Presigned URL MinIO
            if ($pengumpulan) {
                // Konfigurasi S3 Client khusus MinIO
                $externalUrl = config('filesystems.disks.s3.url');
                $bucket = config('filesystems.disks.s3.bucket');

                $externalS3Client = new S3Client([
                    'version' => 'latest',
                    'region'  => config('filesystems.disks.s3.region', 'us-east-1'),
                    'endpoint' => $externalUrl,
                    'use_path_style_endpoint' => true,
                    'credentials' => [
                        'key'    => config('filesystems.disks.s3.key'),
                        'secret' => config('filesystems.disks.s3.secret'),
                    ],
                ]);

                $currentDetail = [
                    'id' => $pengumpulan->id,
                    'uuid' => $pengumpulan->uuid,
                    'nilai_total' => $pengumpulan->nilai_total,
                    'mahasiswa' => [
                        'nama' => $pengumpulan->mahasiswa->user->nama_lengkap,
                        'email' => $pengumpulan->mahasiswa->user->email,
                    ],
                    'jawaban' => $pengumpulan->jawabanDetails->map(function ($jd) use ($externalS3Client, $bucket) {
                        $fileUrl = null;

                        // Jika mahasiswa mengupload file jawaban, generate secure presigned URL dari MinIO
                        if ($jd->file_jawaban) {
                            try {
                                $fileName = basename($jd->file_jawaban);
                                $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                                $mimeTypes = [
                                    'pdf'  => 'application/pdf',
                                    'png'  => 'image/png',
                                    'jpg'  => 'image/jpeg',
                                    'jpeg' => 'image/jpeg',
                                    'gif'  => 'image/gif',
                                    'webp' => 'image/webp',
                                    'mp4'  => 'video/mp4',
                                    'webm' => 'video/webm',
                                    'mp3'  => 'audio/mpeg',
                                    'txt'  => 'text/plain',
                                ];

                                $contentType = $mimeTypes[$extension] ?? 'application/octet-stream';

                                $command = $externalS3Client->getCommand('GetObject', [
                                    'Bucket'                     => $bucket,
                                    'Key'                        => $jd->file_jawaban,
                                    'ResponseContentDisposition' => 'inline',
                                    'ResponseContentType'        => $contentType,
                                ]);

                                $presignedRequest = $externalS3Client->createPresignedRequest($command, '+60 minutes');
                                $fileUrl = (string) $presignedRequest->getUri();
                            } catch (\Exception $e) {
                                \Illuminate\Support\Facades\Log::error('Gagal generate URL presigned jawaban: ' . $e->getMessage());
                                $fileUrl = null;
                            }
                        }

                        return [
                            'id' => $jd->id,
                            'pertanyaan_id' => $jd->pertanyaan_id,
                            'text_jawaban' => $jd->text_jawaban,
                            'file_jawaban' => $jd->file_jawaban,
                            'file_name' => $jd->file_jawaban ? basename($jd->file_jawaban) : null,
                            'file_url' => $fileUrl, // <-- Properti Baru hasil generate URL MinIO
                            'opsi_jawaban_id' => $jd->opsi_jawaban_id,
                            'nilai_per_soal' => $jd->nilai_per_soal,
                            'pertanyaan' => [
                                'text' => $jd->pertanyaan->text_pertanyaan,
                                'jenis' => $jd->pertanyaan->jenis_pertanyaan,
                                'bobot' => (int) $jd->pertanyaan->bobot_soal,
                                'opsi_opsi' => $jd->pertanyaan->opsiJawabans,
                            ]
                        ];
                    })->values(),
                ];
            }

            // 🔹 Total poin
            $totalPoinKuis = (int) Pertanyaan::where('penilaian_id', $openId)->sum('bobot_soal');
        }

        return \Inertia\Inertia::render('Dosen/Kelas/Tugas/Penilaian/Online/Index', [
            'kelas' => [
                'id' => $kelas->id,
                'uuid' => $kelas->uuid,
                'nama' => $kelas->nama,
            ],
            'penilaians' => $penilaians,
            'open' => $openId,
            'tab' => $currentTab,
            'dataKoreksi' => $dataKoreksi,
            'currentDetail' => $currentDetail,
            'totalPoinKuis' => $totalPoinKuis,
            'with' => $with,
        ]);
    }

    public function create(Kelas $kelas)
    {
        $this->ensureOwner($kelas);

        // ✅ Ambil kategori yang sudah digunakan (kecuali tugas)
        $usedCategories = Penilaian::where('kelas_id', $kelas->id)
            ->whereIn('kategori', ['uts', 'uas'])
            ->pluck('kategori')
            ->toArray();


        return Inertia::render('Dosen/Kelas/Tugas/Penilaian/Online/Create', [
            'kelas' => [
                'id' => $kelas->id,
                'uuid' => $kelas->uuid,
                'nama' => $kelas->nama ?? null,
            ],
            'usedCategories' => $usedCategories, // ✅ Kirim ke Vue
            'kategoriOptions' => [
                ['value' => 'tugas', 'label' => 'Tugas'],
                ['value' => 'uts', 'label' => 'UTS'],
                ['value' => 'uas', 'label' => 'UAS'],
            ],
            'jenisPertanyaanOptions' => [
                ['value' => 'essai', 'label' => 'Essai'],
                ['value' => 'pilihan_ganda', 'label' => 'Pilihan Ganda'],
                ['value' => 'upload_file', 'label' => 'Upload File'],
            ],
        ]);
    }

    public function store(Request $request, Kelas $kelas)
    {
        $this->ensureOwner($kelas);

        $data = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'instruksi' => ['nullable', 'string'],
            'kategori' => ['required', 'in:tugas,uts,uas'],
            'tenggat_waktu' => ['nullable', 'date'],
            'pertanyaans' => ['required', 'array', 'min:1'],
            'pertanyaans.*.nomor_urut' => ['required', 'integer', 'min:1'],
            'pertanyaans.*.text_pertanyaan' => ['required', 'string'],
            'pertanyaans.*.jenis_pertanyaan' => ['required', 'in:essai,pilihan_ganda,upload_file'],
            'pertanyaans.*.bobot_soal' => ['nullable', 'integer', 'min:0'],
            'pertanyaans.*.opsi_jawabans' => ['nullable', 'array'],
            'pertanyaans.*.opsi_jawabans.*.teks_opsi' => ['required_with:pertanyaans.*.opsi_jawabans', 'string', 'max:255'],
            'pertanyaans.*.opsi_jawabans.*.is_benar' => ['nullable', 'boolean'],
            'images' => ['nullable', 'array'],
            'images.*' => ['nullable', 'array'],
            'images.*.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        // ✅ Validasi Backend: UTS/UAS cuma boleh satu
        if (in_array($data['kategori'], ['uts', 'uas'])) {
            $exists = Penilaian::where('kelas_id', $kelas->id)
                ->where('kategori', $data['kategori'])
                ->exists();

            if ($exists) {
                return back()->withErrors([
                    'kategori' => "Penilaian kategori " . strtoupper($data['kategori']) . " sudah ada di kelas ini."
                ]);
            }
        }

        $nomors = collect($data['pertanyaans'])->pluck('nomor_urut');
        if ($nomors->count() !== $nomors->unique()->count()) {
            return back()->withErrors(['pertanyaans' => 'Nomor urut pertanyaan tidak boleh duplikat.']);
        }

        foreach ($data['pertanyaans'] as $i => $q) {
            if ($q['jenis_pertanyaan'] === 'pilihan_ganda') {
                $opsi = $q['opsi_jawabans'] ?? [];
                if (count($opsi) < 2) {
                    return back()->withErrors(["pertanyaans.$i.opsi_jawabans" => 'Pilihan ganda minimal memiliki 2 opsi jawaban.']);
                }
                $benarCount = collect($opsi)->filter(fn($o) => (bool) ($o['is_benar'] ?? false))->count();
                if ($benarCount < 1) {
                    return back()->withErrors(["pertanyaans.$i.opsi_jawabans" => 'Pilihan ganda minimal memiliki 1 jawaban benar.']);
                }
            }
        }

        DB::beginTransaction();
        try {
            $penilaian = Penilaian::create([
                'uuid' => (string) Str::uuid(),
                'kelas_id' => $kelas->id,
                'judul' => $data['judul'],
                'instruksi' => $data['instruksi'] ?? null,
                'kategori' => $data['kategori'],
                'mode_penilaian' => 'online',
                'tenggat_waktu' => $data['tenggat_waktu'] ?? null,
            ]);

            foreach ($data['pertanyaans'] as $index => $q) {
                $pertanyaan = Pertanyaan::create([
                    'penilaian_id' => $penilaian->id,
                    'nomor_urut' => (int) $q['nomor_urut'],
                    'text_pertanyaan' => $q['text_pertanyaan'],
                    'jenis_pertanyaan' => $q['jenis_pertanyaan'],
                    'bobot_soal' => (int) ($q['bobot_soal'] ?? 0),
                ]);

                $files = $request->file("images.$index", []);
                if (is_array($files)) {
                    foreach ($files as $file) {
                        if (!$file) continue;
                        $path = $file->store("penilaian/{$penilaian->uuid}/pertanyaan/{$pertanyaan->id}", 'public');
                        PertanyaanImage::create([
                            'pertanyaan_id' => $pertanyaan->id,
                            'path' => $path,
                        ]);
                    }
                }

                if ($q['jenis_pertanyaan'] === 'pilihan_ganda') {
                    foreach (($q['opsi_jawabans'] ?? []) as $opsi) {
                        OpsiJawaban::create([
                            'pertanyaan_id' => $pertanyaan->id,
                            'teks_opsi' => $opsi['teks_opsi'],
                            'is_benar' => (bool) ($opsi['is_benar'] ?? false),
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('dosen.kelas.show', $kelas->uuid)->with('success', 'Penilaian online berhasil dibuat.');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function edit(Kelas $kelas, Penilaian $penilaian)
    {
        $this->ensureOwner($kelas);
        $this->ensurePenilaianInKelas($kelas, $penilaian);
        $this->ensurePenilaianOnline($penilaian);

        // ✅ Ambil kategori terpakai kecuali diri sendiri
        $usedCategories = Penilaian::where('kelas_id', $kelas->id)
            ->where('id', '!=', $penilaian->id)
            ->whereIn('kategori', ['uts', 'uas'])
            ->pluck('kategori')
            ->toArray();

        $penilaian->load([
            'pertanyaans' => fn($q) => $q->orderBy('nomor_urut'),
            'pertanyaans.opsiJawabans',
            'pertanyaans.images',
        ]);

        return Inertia::render('Dosen/Kelas/Tugas/Penilaian/Online/Edit', [
            'kelas' => [
                'id' => $kelas->id,
                'uuid' => $kelas->uuid,
                'nama' => $kelas->nama ?? null,
            ],
            'usedCategories' => $usedCategories, // ✅ Kirim ke Vue
            'kategoriOptions' => [
                ['value' => 'tugas', 'label' => 'Tugas'],
                ['value' => 'uts', 'label' => 'UTS'],
                ['value' => 'uas', 'label' => 'UAS'],
            ],
            'jenisPertanyaanOptions' => [
                ['value' => 'essai', 'label' => 'Essai'],
                ['value' => 'pilihan_ganda', 'label' => 'Pilihan Ganda'],
                ['value' => 'upload_file', 'label' => 'Upload File'],
            ],
            'penilaian' => [
                'id' => $penilaian->id,
                'uuid' => $penilaian->uuid,
                'judul' => $penilaian->judul,
                'instruksi' => $penilaian->instruksi,
                'kategori' => $penilaian->kategori,
                'tenggat_waktu' => optional($penilaian->tenggat_waktu)->toIso8601String(),
                'pertanyaans' => $penilaian->pertanyaans->map(fn($pt) => [
                    'id' => $pt->id,
                    'nomor_urut' => $pt->nomor_urut,
                    'text_pertanyaan' => $pt->text_pertanyaan,
                    'jenis_pertanyaan' => $pt->jenis_pertanyaan,
                    'bobot_soal' => $pt->bobot_soal,
                    'opsi_jawabans' => $pt->opsiJawabans->map(fn($o) => [
                        'id' => $o->id,
                        'teks_opsi' => $o->teks_opsi,
                        'is_benar' => (bool) $o->is_benar,
                    ])->values(),
                    'images' => $pt->images->map(fn($img) => [
                        'id' => $img->id,
                        'path' => $img->path,
                        'url' => Storage::disk('public')->url($img->path),
                    ])->values(),
                ])->values(),
            ],
        ]);
    }

    public function update(Request $request, Kelas $kelas, Penilaian $penilaian)
    {
        $this->ensureOwner($kelas);
        $this->ensurePenilaianInKelas($kelas, $penilaian);
        $this->ensurePenilaianOnline($penilaian);

        $data = $request->validate([
            'judul' => ['sometimes', 'required', 'string', 'max:255'],
            'instruksi' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'kategori' => ['sometimes', 'in:tugas,uts,uas'],
            'tenggat_waktu' => ['sometimes', 'nullable', 'date'],
            'pertanyaans' => ['sometimes', 'required', 'array', 'min:1'],
            'pertanyaans.*.id' => ['nullable', 'exists:pertanyaans,id'],
            'pertanyaans.*.client_key' => ['nullable', 'string'],
            'pertanyaans.*.nomor_urut' => ['required', 'integer', 'min:1'],
            'pertanyaans.*.text_pertanyaan' => ['required', 'string'],
            'pertanyaans.*.jenis_pertanyaan' => ['required', 'in:essai,pilihan_ganda,upload_file'],
            'pertanyaans.*.bobot_soal' => ['nullable', 'integer', 'min:0'],
            'pertanyaans.*.opsi_jawabans' => ['nullable', 'array'],
            'pertanyaans.*.opsi_jawabans.*.id' => ['nullable', 'exists:opsi_jawabans,id'],
            'pertanyaans.*.opsi_jawabans.*.teks_opsi' => ['required_with:pertanyaans.*.opsi_jawabans', 'string', 'max:255'],
            'pertanyaans.*.opsi_jawabans.*.is_benar' => ['nullable', 'boolean'],
            'images' => ['nullable', 'array'],
            'images.*' => ['nullable', 'array'],
            'images.*.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_image_ids' => ['nullable', 'array'],
            'remove_image_ids.*' => ['integer'],
        ]);

        return DB::transaction(function () use ($request, $kelas, $penilaian, $data) {
            $penilaianUpdate = array_filter([
                'judul' => $data['judul'] ?? null,
                'instruksi' => $data['instruksi'] ?? null,
                'kategori' => $data['kategori'] ?? null,
                'tenggat_waktu' => $data['tenggat_waktu'] ?? null,
            ], fn($value) => $value !== null);

            if (!empty($penilaianUpdate)) {
                $penilaian->update($penilaianUpdate);
            }

            if (!empty($data['remove_image_ids'] ?? [])) {
                PertanyaanImage::whereIn('id', $data['remove_image_ids'])
                    ->whereHas('pertanyaan', fn($q) => $q->where('penilaian_id', $penilaian->id))
                    ->chunk(100, function ($images) {
                        foreach ($images as $img) {
                            Storage::disk('public')->delete($img->path);
                            $img->delete();
                        }
                    });
            }

            if (!empty($data['pertanyaans'])) {
                $existingPertanyaans = Pertanyaan::where('penilaian_id', $penilaian->id)->get();

                foreach ($data['pertanyaans'] as $qData) {
                    $pertanyaanId = $qData['id'] ?? null;
                    $pertanyaan = $existingPertanyaans->firstWhere('id', $pertanyaanId);

                    if ($pertanyaan) {
                        $pertanyaan->update([
                            'nomor_urut' => (int) $qData['nomor_urut'],
                            'text_pertanyaan' => $qData['text_pertanyaan'],
                            'jenis_pertanyaan' => $qData['jenis_pertanyaan'],
                            'bobot_soal' => (int) ($qData['bobot_soal'] ?? 0),
                        ]);
                    } else {
                        $pertanyaan = Pertanyaan::create([
                            'penilaian_id' => $penilaian->id,
                            'nomor_urut' => (int) $qData['nomor_urut'],
                            'text_pertanyaan' => $qData['text_pertanyaan'],
                            'jenis_pertanyaan' => $qData['jenis_pertanyaan'],
                            'bobot_soal' => (int) ($qData['bobot_soal'] ?? 0),
                        ]);
                    }

                    $clientKey = $qData['client_key'] ?? $pertanyaan->id;
                    $files = $request->file("images.{$clientKey}", []);

                    if (is_array($files)) {
                        foreach ($files as $file) {
                            if (!$file) continue;
                            $path = $file->store("penilaian/{$penilaian->uuid}/pertanyaan/{$pertanyaan->id}", 'public');
                            PertanyaanImage::create([
                                'pertanyaan_id' => $pertanyaan->id,
                                'path' => $path,
                            ]);
                        }
                    }

                    if (isset($qData['opsi_jawabans']) && $qData['jenis_pertanyaan'] === 'pilihan_ganda') {
                        OpsiJawaban::where('pertanyaan_id', $pertanyaan->id)->delete();

                        foreach ($qData['opsi_jawabans'] as $opsi) {
                            OpsiJawaban::create([
                                'pertanyaan_id' => $pertanyaan->id,
                                'teks_opsi' => $opsi['teks_opsi'],
                                'is_benar' => (bool) ($opsi['is_benar'] ?? false),
                            ]);
                        }
                    }
                }
            }

            return redirect()->route('dosen.kelas.show', $kelas->uuid)
                ->with('success', 'Penilaian online berhasil diperbarui.');
        });
    }

    public function destroy(Kelas $kelas, Penilaian $penilaian)
    {
        $this->ensureOwner($kelas);
        $this->ensurePenilaianInKelas($kelas, $penilaian);
        $this->ensurePenilaianOnline($penilaian);

        $sudahAdaPengumpulan = Pengumpulan::where('penilaian_id', $penilaian->id)->exists();

        if ($sudahAdaPengumpulan) {
            return redirect()
                ->route('dosen.kelas.show', $kelas->uuid)
                ->with('error', 'Penilaian tidak dapat dihapus karena sudah ada mahasiswa yang mengerjakan.');
        }

        DB::beginTransaction();
        try {
            if (!empty($penilaian->uuid)) {
                Storage::disk('public')->deleteDirectory("penilaian/{$penilaian->uuid}");
            }

            $penilaian->delete();
            DB::commit();

            return redirect()
                ->route('dosen.kelas.show', $kelas->uuid)
                ->with('success', 'Penilaian online berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()
                ->route('dosen.kelas.show', $kelas->uuid)
                ->with('error', 'Gagal menghapus penilaian.');
        }
    }
}