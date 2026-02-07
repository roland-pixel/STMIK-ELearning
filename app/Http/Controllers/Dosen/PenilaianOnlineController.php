<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\OpsiJawaban;
use App\Models\Penilaian;
use App\Models\Pertanyaan;
use App\Models\PertanyaanImage;
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
        $this->ensureOwner($kelas);

        $openId = $request->query('open'); // id penilaian yang dipilih (dari halaman sebelumnya)
        $openId = $openId ? (int) $openId : null;

        // default mode: summary, tapi kalau openId ada -> paksa full
        $with = $request->query('with', 'summary'); // summary | full
        if ($openId) $with = 'full';

        $query = Penilaian::query()
            ->where('kelas_id', $kelas->id)
            ->where('mode_penilaian', 'online')
            ->latest();

        // ✅ kalau openId ada -> tampilkan CUMA penilaian itu
        if ($openId) {
            $query->where('id', $openId);
        }

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
                        'url' => Storage::disk('public')->url($img->path),
                    ])->values();
                }

                return $row;
            })->values();

            return $base;
        });

        return Inertia::render('Dosen/Kelas/Tugas/Penilaian/Online/Index', [
            'kelas' => [
                'id' => $kelas->id,
                'uuid' => $kelas->uuid,
                'nama' => $kelas->nama ?? null,
            ],
            'penilaians' => $penilaians, // ✅ kalau openId ada -> isinya cuma 1 item
            'with' => $with,
            'open' => $openId,
        ]);
    }



    public function create(Kelas $kelas)
    {
        $this->ensureOwner($kelas);

        return Inertia::render('Dosen/Kelas/Tugas/Penilaian/Online/Create', [
            'kelas' => [
                'id' => $kelas->id,
                'uuid' => $kelas->uuid,
                'nama' => $kelas->nama ?? null,
            ],
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

        $penilaian = null;

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

            return redirect()
                ->route('dosen.kelas.show', $kelas->uuid)
                ->with('success', 'Penilaian online berhasil dibuat.');
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($penilaian && $penilaian->uuid) {
                Storage::disk('public')->deleteDirectory("penilaian/{$penilaian->uuid}");
            }

            throw $e;
        }
    }

    /** ================== EDIT ================== */
    public function edit(Kelas $kelas, Penilaian $penilaian)
    {

        $this->ensureOwner($kelas);
        $this->ensurePenilaianInKelas($kelas, $penilaian);
        $this->ensurePenilaianOnline($penilaian);

        $penilaian->load([
            'pertanyaans' => function ($q) {
                $q->orderBy('nomor_urut');
            },
            'pertanyaans.opsiJawabans',
            'pertanyaans.images',
        ]);

        return Inertia::render('Dosen/Kelas/Tugas/Penilaian/Online/Edit', [
            'kelas' => [
                'id' => $kelas->id,
                'uuid' => $kelas->uuid,
                'nama' => $kelas->nama ?? null,
            ],
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
                'pertanyaans' => $penilaian->pertanyaans->map(function ($pt) {
                    return [
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
                    ];
                })->values(),
            ],
        ]);
    }

    /** ================== UPDATE ================== */
    public function update(Request $request, Kelas $kelas, Penilaian $penilaian)
    {
        $this->ensureOwner($kelas);
        $this->ensurePenilaianInKelas($kelas, $penilaian);
        $this->ensurePenilaianOnline($penilaian);

        $data = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'instruksi' => ['nullable', 'string'],
            'kategori' => ['required', 'in:tugas,uts,uas'],
            'tenggat_waktu' => ['nullable', 'date'],

            'pertanyaans' => ['required', 'array', 'min:1'],
            'pertanyaans.*.id' => ['nullable', 'integer'],
            'pertanyaans.*.client_key' => ['required', 'string', 'max:100'], // ✅ stabil key
            'pertanyaans.*.nomor_urut' => ['required', 'integer', 'min:1'],
            'pertanyaans.*.text_pertanyaan' => ['required', 'string'],
            'pertanyaans.*.jenis_pertanyaan' => ['required', 'in:essai,pilihan_ganda,upload_file'],
            'pertanyaans.*.bobot_soal' => ['nullable', 'integer', 'min:0'],

            'pertanyaans.*.opsi_jawabans' => ['nullable', 'array'],
            'pertanyaans.*.opsi_jawabans.*.teks_opsi' => ['required_with:pertanyaans.*.opsi_jawabans', 'string', 'max:255'],
            'pertanyaans.*.opsi_jawabans.*.is_benar' => ['nullable', 'boolean'],

            // ✅ images keyed by client_key
            'images' => ['nullable', 'array'],
            'images.*' => ['nullable', 'array'],
            'images.*.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],

            // hapus gambar lama by ids
            'remove_image_ids' => ['nullable', 'array'],
            'remove_image_ids.*' => ['integer'],
        ]);

        // validasi nomor urut unik
        $nomors = collect($data['pertanyaans'])->pluck('nomor_urut');
        if ($nomors->count() !== $nomors->unique()->count()) {
            return back()->withErrors(['pertanyaans' => 'Nomor urut pertanyaan tidak boleh duplikat.']);
        }

        // validasi pilihan ganda
        foreach ($data['pertanyaans'] as $i => $q) {
            if (($q['jenis_pertanyaan'] ?? '') === 'pilihan_ganda') {
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
            // update header
            $penilaian->update([
                'judul' => $data['judul'],
                'instruksi' => $data['instruksi'] ?? null,
                'kategori' => $data['kategori'],
                'tenggat_waktu' => $data['tenggat_waktu'] ?? null,
            ]);

            /**
             * 1) Hapus gambar lama yang dipilih
             */
            $removeIds = $data['remove_image_ids'] ?? [];
            if (!empty($removeIds)) {
                $imgs = PertanyaanImage::query()
                    ->whereIn('id', $removeIds)
                    ->whereHas('pertanyaan', fn($q) => $q->where('penilaian_id', $penilaian->id))
                    ->get();

                foreach ($imgs as $img) {
                    Storage::disk('public')->delete($img->path);
                    $img->delete();
                }
            }

            /**
             * 2) Sync pertanyaan (update/create)
             */
            $existingQuestionIds = Pertanyaan::query()
                ->where('penilaian_id', $penilaian->id)
                ->pluck('id')
                ->all();

            $keptIds = [];

            foreach ($data['pertanyaans'] as $qIndex => $q) {
                $qid = $q['id'] ?? null;

                if ($qid) {
                    $pertanyaan = Pertanyaan::query()
                        ->where('penilaian_id', $penilaian->id)
                        ->where('id', $qid)
                        ->first();

                    if (!$pertanyaan) {
                        // id invalid -> create
                        $pertanyaan = Pertanyaan::create([
                            'penilaian_id' => $penilaian->id,
                            'nomor_urut' => (int) $q['nomor_urut'],
                            'text_pertanyaan' => $q['text_pertanyaan'],
                            'jenis_pertanyaan' => $q['jenis_pertanyaan'],
                            'bobot_soal' => (int) ($q['bobot_soal'] ?? 0),
                        ]);
                    } else {
                        $pertanyaan->update([
                            'nomor_urut' => (int) $q['nomor_urut'],
                            'text_pertanyaan' => $q['text_pertanyaan'],
                            'jenis_pertanyaan' => $q['jenis_pertanyaan'],
                            'bobot_soal' => (int) ($q['bobot_soal'] ?? 0),
                        ]);
                    }
                } else {
                    $pertanyaan = Pertanyaan::create([
                        'penilaian_id' => $penilaian->id,
                        'nomor_urut' => (int) $q['nomor_urut'],
                        'text_pertanyaan' => $q['text_pertanyaan'],
                        'jenis_pertanyaan' => $q['jenis_pertanyaan'],
                        'bobot_soal' => (int) ($q['bobot_soal'] ?? 0),
                    ]);
                }

                $keptIds[] = $pertanyaan->id;

                /**
                 * 3) Upload gambar baru berdasarkan client_key (stabil)
                 */
                $clientKey = $q['client_key']; // ✅
                $files = $request->file("images.$clientKey", []);
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

                /**
                 * 4) Sync opsi jawaban
                 */
                if (($q['jenis_pertanyaan'] ?? '') !== 'pilihan_ganda') {
                    OpsiJawaban::query()->where('pertanyaan_id', $pertanyaan->id)->delete();
                } else {
                    OpsiJawaban::query()->where('pertanyaan_id', $pertanyaan->id)->delete();
                    foreach (($q['opsi_jawabans'] ?? []) as $opsi) {
                        OpsiJawaban::create([
                            'pertanyaan_id' => $pertanyaan->id,
                            'teks_opsi' => $opsi['teks_opsi'],
                            'is_benar' => (bool) ($opsi['is_benar'] ?? false),
                        ]);
                    }
                }
            }

            /**
             * 5) Hapus pertanyaan yang dibuang
             */
            $toDelete = array_values(array_diff($existingQuestionIds, $keptIds));
            if (!empty($toDelete)) {
                $imgs = PertanyaanImage::query()->whereIn('pertanyaan_id', $toDelete)->get();
                foreach ($imgs as $img) {
                    Storage::disk('public')->delete($img->path);
                }

                Pertanyaan::query()
                    ->where('penilaian_id', $penilaian->id)
                    ->whereIn('id', $toDelete)
                    ->delete();
            }

            DB::commit();

            return redirect()
                ->route('dosen.kelas.show', $kelas->uuid)
                ->with('success', 'Penilaian online berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /** ================== DESTROY ================== */
    public function destroy(Kelas $kelas, Penilaian $penilaian)
    {
        $this->ensureOwner($kelas);
        $this->ensurePenilaianInKelas($kelas, $penilaian);
        $this->ensurePenilaianOnline($penilaian);

        DB::beginTransaction();
        try {
            // hapus folder storage (gambar2 pertanyaan)
            if (!empty($penilaian->uuid)) {
                Storage::disk('public')->deleteDirectory("penilaian/{$penilaian->uuid}");
            }

            // hapus record penilaian (cascade akan hapus pertanyaans, opsi_jawabans, pertanyaan_images)
            $penilaian->delete();

            DB::commit();

            return redirect()
                ->route('dosen.kelas.show', $kelas->uuid)
                ->with('success', 'Penilaian online berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
