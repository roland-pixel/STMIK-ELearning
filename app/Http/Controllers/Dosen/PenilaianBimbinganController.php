<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Bimbingan;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PenilaianBimbinganController extends Controller
{
    private function dosenId(Request $request): int
    {
        return (int) DB::table('dosens')->where('user_id', $request->user()->id)->value('id');
    }

    private function activeSemesterId(): ?int
    {
        return Semester::where('status_aktif', 'active')->value('id');
    }

    public function index(Request $request)
    {
        $dosenId = $this->dosenId($request);

        // Filter input
        $semesterId = $request->integer('semester_id');
        $status = $request->string('status', 'all')->toString(); // all|pending|approved
        $mataKuliahId = $request->integer('mata_kuliah_id');
        $q = trim((string) $request->query('q', ''));

        // Default semester aktif kalau belum pilih
        if (!$semesterId) {
            $semesterId = $this->activeSemesterId();
        }

        $query = Bimbingan::query()
            ->where('dosen_pembimbing_id', $dosenId)
            ->with([
                'mahasiswa.user:id,nama_lengkap,email',
                'mahasiswa.jurusan:id,nama_jurusan',
                'semester:id,nama_semester,status_aktif',
                'mataKuliah:id,nama_mk,kode_mk,jenis_mk',
            ]);

        if ($semesterId) {
            $query->where('semester_id', $semesterId);
        }

        if (in_array($status, ['pending', 'approved'], true)) {
            $query->where('status', $status);
        }

        if ($mataKuliahId) {
            $query->where('mata_kuliah_id', $mataKuliahId);
        }

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('judul_penelitian', 'like', "%{$q}%")
                    ->orWhereHas('mahasiswa', function ($m) use ($q) {
                        $m->where('nim', 'like', "%{$q}%")
                            ->orWhereHas('user', function ($u) use ($q) {
                                $u->where('nama_lengkap', 'like', "%{$q}%");
                            });
                    })
                    ->orWhereHas('mataKuliah', function ($mk) use ($q) {
                        $mk->where('nama_mk', 'like', "%{$q}%")
                            ->orWhere('kode_mk', 'like', "%{$q}%");
                    });
            });
        }

        $bimbingans = $query->latest()->get()->map(fn($b) => [
            'id' => $b->id,
            'uuid' => $b->uuid,
            'judul_penelitian' => $b->judul_penelitian,
            'nilai_angka' => $b->nilai_angka,
            'status' => $b->status,

            'semester' => $b->semester ? [
                'id' => $b->semester->id,
                'nama_semester' => $b->semester->nama_semester,
                'status_aktif' => $b->semester->status_aktif,
            ] : null,

            'mata_kuliah' => $b->mataKuliah ? [
                'id' => $b->mataKuliah->id,
                'kode_mk' => $b->mataKuliah->kode_mk,
                'nama_mk' => $b->mataKuliah->nama_mk,
                'jenis_mk' => $b->mataKuliah->jenis_mk,
            ] : null,

            'mahasiswa' => $b->mahasiswa ? [
                'id' => $b->mahasiswa->id,
                'uuid' => $b->mahasiswa->uuid,
                'nim' => $b->mahasiswa->nim,
                'jurusan' => $b->mahasiswa->jurusan?->nama_jurusan,
                'user' => [
                    'nama_lengkap' => $b->mahasiswa->user?->nama_lengkap,
                    'email' => $b->mahasiswa->user?->email,
                ],
            ] : null,
        ]);

        // Options filter
        $semesters = Semester::orderByDesc('id')->get(['id', 'nama_semester', 'status_aktif']);
        $mataKuliahs = MataKuliah::query()
            ->where('jenis_mk', 'Spesial')
            ->orderBy('nama_mk')
            ->get(['id', 'kode_mk', 'nama_mk', 'jenis_mk']);


        return Inertia::render('Dosen/PenilaianBimbingan/Index', [
            'bimbingans' => $bimbingans,
            'semesters' => $semesters,
            'mata_kuliahs' => $mataKuliahs,
            'activeSemesterId' => $this->activeSemesterId(),
            'filters' => [
                'semester_id' => $semesterId,
                'status' => $status,
                'mata_kuliah_id' => $mataKuliahId,
                'q' => $q,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $dosenId = $this->dosenId($request);

        $validated = $request->validate([
            'semester_id' => ['required', 'exists:semesters,id'],
            'mahasiswa_id' => ['required', 'exists:mahasiswas,id'],
            'mata_kuliah_id' => ['required', 'exists:mata_kuliahs,id'],

            'judul_penelitian' => ['nullable', 'string', 'max:255'],
            'nilai_angka' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status' => ['required', Rule::in(['pending', 'approved'])],
        ]);

        Bimbingan::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'semester_id' => $validated['semester_id'],
            'mahasiswa_id' => $validated['mahasiswa_id'],
            'dosen_pembimbing_id' => $dosenId,
            'mata_kuliah_id' => $validated['mata_kuliah_id'],
            'judul_penelitian' => $validated['judul_penelitian'] ?? null,
            'nilai_angka' => $validated['nilai_angka'] ?? null,
            'status' => $validated['status'],
        ]);

        return redirect()->route('dosen.penilaian_bimbingan.index')
            ->with('success', 'Data bimbingan berhasil dibuat.');
    }

    public function edit(Request $request, Bimbingan $bimbingan)
    {
        $dosenId = $this->dosenId($request);
        abort_if($bimbingan->dosen_pembimbing_id !== $dosenId, 403);

        $bimbingan->load(['mahasiswa.user:id,nama_lengkap,email', 'mataKuliah:id,nama_mk,kode_mk,jenis_mk', 'semester:id,nama_semester,status_aktif']);

        return Inertia::render('Dosen/PenilaianBimbingan/Edit', [
            'bimbingan' => [
                'id' => $bimbingan->id,
                'uuid' => $bimbingan->uuid,
                'semester_id' => $bimbingan->semester_id,
                'mahasiswa_id' => $bimbingan->mahasiswa_id,
                'mata_kuliah_id' => $bimbingan->mata_kuliah_id,
                'judul_penelitian' => $bimbingan->judul_penelitian,
                'nilai_angka' => $bimbingan->nilai_angka,
                'status' => $bimbingan->status,
                'mahasiswa' => [
                    'nim' => $bimbingan->mahasiswa?->nim,
                    'nama_lengkap' => $bimbingan->mahasiswa?->user?->nama_lengkap,
                    'email' => $bimbingan->mahasiswa?->user?->email,
                ],
                'mata_kuliah' => [
                    'kode_mk' => $bimbingan->mataKuliah?->kode_mk,
                    'nama_mk' => $bimbingan->mataKuliah?->nama_mk,
                ],
                'semester' => [
                    'nama_semester' => $bimbingan->semester?->nama_semester,
                    'status_aktif' => $bimbingan->semester?->status_aktif,
                ],
            ],
        ]);
    }

    public function update(Request $request, Bimbingan $bimbingan)
    {
        $dosenId = $this->dosenId($request);
        abort_if($bimbingan->dosen_pembimbing_id !== $dosenId, 403);

        $validated = $request->validate([
            'judul_penelitian' => ['nullable', 'string', 'max:255'],
            'nilai_angka' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status' => ['required', Rule::in(['pending', 'approved'])],
        ]);

        $bimbingan->update($validated);

        return back()->with('success', 'Penilaian bimbingan berhasil diperbarui.');
    }

    public function destroy(Request $request, Bimbingan $bimbingan)
    {
        $dosenId = $this->dosenId($request);
        abort_if($bimbingan->dosen_pembimbing_id !== $dosenId, 403);

        $bimbingan->delete();

        return redirect()->route('dosen.penilaian_bimbingan.index')
            ->with('success', 'Data bimbingan berhasil dihapus.');
    }
}
