<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Materi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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

    // ✅ PERBAIKAN UTAMA DI SINI
    public function index(Request $request, Kelas $kelas)
    {
        $this->ensureOwner($kelas);

        $openId = $request->query('open'); // ?open=123

        $query = Materi::query()
            ->where('kelas_id', $kelas->id)
            ->latest();

        // kalau ada open, filter hanya materi itu
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

        // ✅ kalau openId ada tapi gak ketemu (atau bukan materi kelas ini), balikin 404 biar jelas
        if (!empty($openId) && $materis->count() === 0) {
            abort(404, 'Materi tidak ditemukan di kelas ini.');
        }

        return Inertia::render('Dosen/Kelas/Tugas/Materi/Index', [
            'kelas' => [
                'id' => $kelas->id,
                'uuid' => $kelas->uuid,
                'nama' => $kelas->nama ?? null,
            ],
            'materis' => $materis,
            'open' => !empty($openId) ? (int) $openId : null, // opsional untuk UI
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
            'file' => ['nullable', 'file', 'max:10240'], // 10MB
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

        if ($removeLink) {
            $linkUrl = null;
        } else {
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

        return redirect()
            ->route('dosen.kelas.show', $kelas->uuid)
            ->with('success', 'Materi berhasil dihapus.');
    }
}
