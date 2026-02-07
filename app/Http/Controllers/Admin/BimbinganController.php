<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bimbingan;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BimbinganController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');

        $bimbingans = Bimbingan::query()
            ->with([
                'semester',
                'mataKuliah',
                'mahasiswa.user',
                'dosen.user',
            ])
            ->when(
                $q,
                fn($query) => $query
                    ->whereHas(
                        'mahasiswa.user',
                        fn($u) => $u
                            ->where('nama_lengkap', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%")
                    )
                    ->orWhereHas(
                        'dosen.user',
                        fn($u) => $u
                            ->where('nama_lengkap', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%")
                    )
                    ->orWhereHas(
                        'semester',
                        fn($s) => $s
                            ->where('nama_semester', 'like', "%{$q}%")
                    )
                    ->orWhereHas(
                        'mataKuliah',
                        fn($mk) => $mk
                            ->where('kode_mk', 'like', "%{$q}%")
                            ->orWhere('nama_mk', 'like', "%{$q}%")
                    )
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.bimbingans.index', compact('bimbingans', 'q'));
    }

    public function create()
    {
        $semesters = Semester::query()->orderByDesc('id')->get();
        $mahasiswas = Mahasiswa::query()->with('user')->orderByDesc('id')->get();
        $dosens = Dosen::query()->with('user')->orderByDesc('id')->get();

        // Hanya MK jenis "Spesial"
        $mataKuliahs = MataKuliah::query()
            ->where('jenis_mk', 'Spesial')
            ->orderBy('nama_mk')
            ->get();

        return view('admin.bimbingans.create', compact('semesters', 'mahasiswas', 'dosens', 'mataKuliahs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'semester_id' => ['required', 'exists:semesters,id'],
            'mahasiswa_id' => ['required', 'exists:mahasiswas,id'],
            'dosen_pembimbing_id' => ['required', 'exists:dosens,id'],
            'mata_kuliah_id' => ['required', 'exists:mata_kuliahs,id'],
        ]);

        // hard guard: pastikan mk yang dipilih memang "Spesial"
        $isSpesial = MataKuliah::where('id', $validated['mata_kuliah_id'])
            ->where('jenis_mk', 'Spesial')
            ->exists();

        if (!$isSpesial) {
            return back()
                ->withErrors(['mata_kuliah_id' => 'Mata kuliah yang dipilih harus jenis "Spesial".'])
                ->withInput();
        }

        Bimbingan::create([
            'uuid' => (string) Str::uuid(),
            'semester_id' => $validated['semester_id'],
            'mahasiswa_id' => $validated['mahasiswa_id'],
            'dosen_pembimbing_id' => $validated['dosen_pembimbing_id'],
            'mata_kuliah_id' => $validated['mata_kuliah_id'],

            // field dosen (biarkan null/default)
            'judul_penelitian' => null,
            'nilai_angka' => null,
            'status' => 'pending',
        ]);

        return redirect()
            ->route('admin.bimbingans.index')
            ->with('success', 'Bimbingan berhasil ditambahkan.');
    }

    public function edit(Bimbingan $bimbingan)
    {
        $bimbingan->load(['semester', 'mataKuliah', 'mahasiswa.user', 'dosen.user']);

        $semesters = Semester::query()->orderByDesc('id')->get();
        $mahasiswas = Mahasiswa::query()->with('user')->orderByDesc('id')->get();
        $dosens = Dosen::query()->with('user')->orderByDesc('id')->get();
        $mataKuliahs = MataKuliah::query()
            ->where('jenis_mk', 'Spesial')
            ->orderBy('nama_mk')
            ->get();

        return view('admin.bimbingans.edit', compact('bimbingan', 'semesters', 'mahasiswas', 'dosens', 'mataKuliahs'));
    }

    public function update(Request $request, Bimbingan $bimbingan)
    {
        $validated = $request->validate([
            'semester_id' => ['required', 'exists:semesters,id'],
            'mahasiswa_id' => ['required', 'exists:mahasiswas,id'],
            'dosen_pembimbing_id' => ['required', 'exists:dosens,id'],
            'mata_kuliah_id' => ['required', 'exists:mata_kuliahs,id'],
        ]);

        $isSpesial = MataKuliah::where('id', $validated['mata_kuliah_id'])
            ->where('jenis_mk', 'Spesial')
            ->exists();

        if (!$isSpesial) {
            return back()
                ->withErrors(['mata_kuliah_id' => 'Mata kuliah yang dipilih harus jenis "Spesial".'])
                ->withInput();
        }

        // admin hanya update penugasan, tidak mengubah field dosen
        $bimbingan->update([
            'semester_id' => $validated['semester_id'],
            'mahasiswa_id' => $validated['mahasiswa_id'],
            'dosen_pembimbing_id' => $validated['dosen_pembimbing_id'],
            'mata_kuliah_id' => $validated['mata_kuliah_id'],
        ]);

        return redirect()
            ->route('admin.bimbingans.index')
            ->with('success', 'Bimbingan berhasil diperbarui.');
    }

    public function destroy(Bimbingan $bimbingan)
    {
        $bimbingan->delete();

        return redirect()
            ->route('admin.bimbingans.index')
            ->with('success', 'Bimbingan berhasil dihapus.');
    }
}
