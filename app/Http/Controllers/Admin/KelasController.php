<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\Kelas;
use App\Models\MataKuliah;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class KelasController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');

        // filter semester: active | inactive | all (opsional)
        // default: active
        $semesterStatus = $request->get('semester_status', 'active');

        $kelases = Kelas::query()
            ->with(['dosen.user', 'mataKuliah', 'semester'])
            ->when(
                $semesterStatus !== 'all',
                fn($query) => $query->whereHas(
                    'semester',
                    fn($s) => $s->where('status_aktif', $semesterStatus)
                )
            )
            ->when(
                $q,
                fn($query) => $query->where(function ($query) use ($q) {
                    $query
                        ->where('nama_kelas', 'like', "%{$q}%")
                        ->orWhere('kode_gabung', 'like', "%{$q}%")
                        ->orWhereHas(
                            'mataKuliah',
                            fn($mk) => $mk
                                ->where('kode_mk', 'like', "%{$q}%")
                                ->orWhere('nama_mk', 'like', "%{$q}%")
                        )
                        ->orWhereHas(
                            'semester',
                            fn($s) => $s->where('nama_semester', 'like', "%{$q}%")
                        )
                        ->orWhereHas(
                            'dosen.user',
                            fn($u) => $u
                                ->where('nama_lengkap', 'like', "%{$q}%")
                                ->orWhere('email', 'like', "%{$q}%")
                        );
                })
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.kelases.index', compact('kelases', 'q', 'semesterStatus'));
    }

    public function create()
    {
        $dosens = Dosen::query()->with('user')->orderByDesc('id')->get();

        // ✅ Hanya MK dengan jenis_mk = "Umum"
        $mataKuliahs = MataKuliah::query()
            ->where('jenis_mk', 'Umum')
            ->orderBy('nama_mk')
            ->get();

        $semesters = Semester::query()->orderByDesc('id')->get();

        return view('admin.kelases.create', compact('dosens', 'mataKuliahs', 'semesters'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'dosen_id' => ['required', 'exists:dosens,id'],

            // ✅ Wajib MK "Umum" (aman dari request yang dimanipulasi)
            'mata_kuliah_id' => [
                'required',
                Rule::exists('mata_kuliahs', 'id')->where(fn($q) => $q->where('jenis_mk', 'Umum')),
            ],

            'semester_id' => ['required', 'exists:semesters,id'],
            'nama_kelas' => ['required', 'string', 'max:255'],
            'kode_gabung' => ['nullable', 'string', 'max:50', 'unique:kelases,kode_gabung'],
            'deskripsi' => ['nullable', 'string'],
            'persentase_tugas' => ['required', 'integer', 'min:0', 'max:100'],
            'persentase_uts' => ['required', 'integer', 'min:0', 'max:100'],
            'persentase_uas' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $total = (int) $validated['persentase_tugas']
            + (int) $validated['persentase_uts']
            + (int) $validated['persentase_uas'];

        if ($total !== 100) {
            return back()
                ->withErrors(['persentase_uas' => 'Total persentase Tugas + UTS + UAS harus 100.'])
                ->withInput();
        }

        // Auto generate kode_gabung jika kosong
        if (empty($validated['kode_gabung'])) {
            do {
                $kode = strtoupper(Str::random(8));
            } while (Kelas::where('kode_gabung', $kode)->exists());

            $validated['kode_gabung'] = $kode;
        }

        $validated['uuid'] = (string) Str::uuid();

        Kelas::create($validated);

        return redirect()
            ->route('admin.kelases.index')
            ->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function edit(Kelas $kelas)
    {
        $kelas->load(['dosen.user', 'mataKuliah', 'semester']);

        $dosens = Dosen::query()->with('user')->orderByDesc('id')->get();

        // ✅ Hanya MK dengan jenis_mk = "Umum"
        $mataKuliahs = MataKuliah::query()
            ->where('jenis_mk', 'Umum')
            ->orderBy('nama_mk')
            ->get();

        $semesters = Semester::query()->orderByDesc('id')->get();

        return view('admin.kelases.edit', compact('kelas', 'dosens', 'mataKuliahs', 'semesters'));
    }

    public function update(Request $request, Kelas $kelas)
    {
        $validated = $request->validate([
            'dosen_id' => ['required', 'exists:dosens,id'],

            // ✅ Wajib MK "Umum" (aman dari request yang dimanipulasi)
            'mata_kuliah_id' => [
                'required',
                Rule::exists('mata_kuliahs', 'id')->where(fn($q) => $q->where('jenis_mk', 'Umum')),
            ],

            'semester_id' => ['required', 'exists:semesters,id'],
            'nama_kelas' => ['required', 'string', 'max:255'],
            'kode_gabung' => [
                'required',
                'string',
                'max:50',
                Rule::unique('kelases', 'kode_gabung')->ignore($kelas->id),
            ],
            'deskripsi' => ['nullable', 'string'],
            'persentase_tugas' => ['required', 'integer', 'min:0', 'max:100'],
            'persentase_uts' => ['required', 'integer', 'min:0', 'max:100'],
            'persentase_uas' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $total = (int) $validated['persentase_tugas']
            + (int) $validated['persentase_uts']
            + (int) $validated['persentase_uas'];

        if ($total !== 100) {
            return back()
                ->withErrors(['persentase_uas' => 'Total persentase Tugas + UTS + UAS harus 100.'])
                ->withInput();
        }

        $kelas->update($validated);

        return redirect()
            ->route('admin.kelases.index')
            ->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Kelas $kelas)
    {
        $kelas->delete();

        return redirect()
            ->route('admin.kelases.index')
            ->with('success', 'Kelas berhasil dihapus.');
    }
}
