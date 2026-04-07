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

        $openId = $request->query('open');
        $openId = $openId ? (int) $openId : null;

        $with = $request->query('with', 'summary');
        if ($openId) $with = 'full';

        $query = Penilaian::query()
            ->where('kelas_id', $kelas->id)
            ->where('mode_penilaian', 'online')
            ->latest();

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
            'penilaians' => $penilaians,
            'with' => $with,
            'open' => $openId,
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
            'judul' => ['required', 'string', 'max:255'],
            'instruksi' => ['nullable', 'string'],
            'kategori' => ['required', 'in:tugas,uts,uas'],
            'tenggat_waktu' => ['nullable', 'date'],
            'pertanyaans' => ['required', 'array', 'min:1'],
            'pertanyaans.*.id' => ['nullable', 'integer'],
            'pertanyaans.*.client_key' => ['required', 'string', 'max:100'],
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
            'remove_image_ids' => ['nullable', 'array'],
            'remove_image_ids.*' => ['integer'],
        ]);

        // ✅ Validasi Backend: UTS/UAS cuma boleh satu (kecuali diri sendiri)
        if (in_array($data['kategori'], ['uts', 'uas'])) {
            $exists = Penilaian::where('kelas_id', $kelas->id)
                ->where('kategori', $data['kategori'])
                ->where('id', '!=', $penilaian->id)
                ->exists();

            if ($exists) {
                return back()->withErrors([
                    'kategori' => "Sudah ada penilaian " . strtoupper($data['kategori']) . " lain di kelas ini."
                ]);
            }
        }

        // Logic sync pertanyaan & gambar (sama seperti kode sebelumnya)
        DB::beginTransaction();
        try {
            $penilaian->update([
                'judul' => $data['judul'],
                'instruksi' => $data['instruksi'] ?? null,
                'kategori' => $data['kategori'],
                'tenggat_waktu' => $data['tenggat_waktu'] ?? null,
            ]);

            // ... (Kode sync gambar & pertanyaan dilanjutkan sesuai logika Anda sebelumnya) ...

            DB::commit();
            return redirect()->route('dosen.kelas.show', $kelas->uuid)->with('success', 'Penilaian online berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function destroy(Kelas $kelas, Penilaian $penilaian)
    {
        $this->ensureOwner($kelas);
        $this->ensurePenilaianInKelas($kelas, $penilaian);
        $this->ensurePenilaianOnline($penilaian);

        DB::beginTransaction();
        try {
            if (!empty($penilaian->uuid)) {
                Storage::disk('public')->deleteDirectory("penilaian/{$penilaian->uuid}");
            }
            $penilaian->delete();
            DB::commit();
            return redirect()->route('dosen.kelas.show', $kelas->uuid)->with('success', 'Penilaian online berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
