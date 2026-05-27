<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Materi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class MateriController extends Controller
{
    private function ensureOwner(Kelas $kelas): void
    {
        $dosenId = Auth::user()?->dosen?->id;

        abort_if(!$dosenId, 403, 'Akun dosen tidak valid.');
        abort_if((int) $kelas->dosen_id !== (int) $dosenId, 403, 'Tidak berhak mengakses kelas ini.');
    }

    private function ensureMateriInKelas(Kelas $kelas, Materi $materi): void
    {
        abort_if((int) $materi->kelas_id !== (int) $kelas->id, 404, 'Materi tidak ditemukan di kelas ini.');
    }

    public function index(Request $request, Kelas $kelas)
    {
        $this->ensureOwner($kelas);

        $openId = $request->query('open');

        $query = Materi::query()
            ->where('kelas_id', $kelas->id)
            ->latest();

        if (!empty($openId)) {
            $query->where('id', $openId);
        }

        $materis = $query->get()->map(function ($m) {
            return [
                'id' => $m->id,
                'judul' => $m->judul,
                'deskripsi' => $m->deskripsi,
                'link_url' => $m->link_url,
                'file_path' => $m->file_path,
                'file_name' => $m->file_path ? basename($m->file_path) : null,
                'created_at' => optional($m->created_at)->toIso8601String(),
            ];
        });

        if (!empty($openId) && $materis->count() === 0) {
            abort(404, 'Materi tidak ditemukan di kelas ini.');
        }

        return Inertia::render('Dosen/Kelas/Tugas/Materi/Index', [
            'kelas' => [
                'id' => $kelas->id,
                'uuid' => $kelas->uuid,
                'nama' => $kelas->nama_kelas ?? $kelas->nama ?? null, // Fallback pengaman nama kolom
            ],
            'materis' => $materis,
            'open' => !empty($openId) ? (int) $openId : null,
        ]);
    }

    public function create(Kelas $kelas)
    {
        $this->ensureOwner($kelas);

        return Inertia::render('Dosen/Kelas/Tugas/Materi/Create', [
            'kelas' => $kelas,
        ]);
    }

    public function store(Request $request, Kelas $kelas)
    {
        $this->ensureOwner($kelas);

        $data = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'link_url' => ['nullable', 'url', 'max:2048'],
            'file' => [
                'nullable',
                'file',
                'mimes:pdf,docx,doc,pptx,ppt,xlsx,xls,zip,rar,png,jpg,jpeg',
                'max:10240'
            ],
        ]);

        if (!$request->hasFile('file') && empty($data['link_url'])) {
            return back()->withErrors([
                'file' => 'Isi minimal salah satu: upload file atau link.',
            ]);
        }

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('materi', 'public');
        }

        Materi::create([
            'kelas_id' => $kelas->id,
            'judul' => $data['judul'],
            'deskripsi' => $data['deskripsi'] ?? null,
            'file_path' => $filePath,
            'link_url' => $data['link_url'] ?? null,
        ]);

        // Cache::forget("kelas:global_detail:{$kelas->id}");

        return redirect()
            ->route('dosen.kelas.show', $kelas->uuid)
            ->with('success', 'Materi berhasil ditambahkan.');
    }

    public function edit(Kelas $kelas, Materi $materi)
    {
        $this->ensureOwner($kelas);
        $this->ensureMateriInKelas($kelas, $materi);

        return Inertia::render('Dosen/Kelas/Tugas/Materi/Edit', [
            'kelas' => $kelas,
            'materi' => $materi,
        ]);
    }

    public function update(Request $request, Kelas $kelas, Materi $materi)
    {
        $this->ensureOwner($kelas);
        $this->ensureMateriInKelas($kelas, $materi);

        $data = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'link_url' => ['nullable', 'url', 'max:2048'],
            'file' => ['nullable', 'file', 'max:10240'],
            'remove_file' => ['nullable', 'boolean'],
            'remove_link' => ['nullable', 'boolean'],
        ]);

        $removeFile = (bool) ($data['remove_file'] ?? false);
        $removeLink = (bool) ($data['remove_link'] ?? false);

        $filePath = $materi->file_path;
        $linkUrl  = $materi->link_url;

        // ✅ Perbaikan logika update link agar tidak sengaja ter-overwrite null
        if ($removeLink) {
            $linkUrl = null;
        } elseif ($request->has('link_url')) {
            $linkUrl = $data['link_url'] ?? null;
        }

        if ($request->hasFile('file')) {
            if ($materi->file_path) {
                Storage::disk('public')->delete($materi->file_path);
            }
            $filePath = $request->file('file')->store('materi', 'public');
        } elseif ($removeFile) {
            if ($materi->file_path) {
                Storage::disk('public')->delete($materi->file_path);
            }
            $filePath = null;
        }

        if (!$filePath && !$linkUrl) {
            return back()->withErrors([
                'file' => 'Isi minimal salah satu: upload file atau link.',
            ]);
        }

        $materi->update([
            'judul' => $data['judul'],
            'deskripsi' => $data['deskripsi'] ?? null,
            'file_path' => $filePath,
            'link_url' => $linkUrl,
        ]);

        // Cache::forget("kelas:global_detail:{$materi->kelas_id}");

        return redirect()
            ->route('dosen.kelas.show', $kelas->uuid)
            ->with('success', 'Materi berhasil diperbarui.');
    }

    public function destroy(Kelas $kelas, Materi $materi)
    {
        $this->ensureOwner($kelas);
        $this->ensureMateriInKelas($kelas, $materi);

        if ($materi->file_path) {
            Storage::disk('public')->delete($materi->file_path);
        }

        $materi->delete();

        // Cache::forget("kelas:global_detail:{$materi->kelas_id}");

        return redirect()
            ->route('dosen.kelas.show', $kelas->uuid)
            ->with('success', 'Materi berhasil dihapus.');
    }
}
