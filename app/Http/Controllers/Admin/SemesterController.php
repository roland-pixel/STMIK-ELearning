<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SemesterController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');

        $semesters = Semester::query()
            ->when(
                $q,
                fn($query) => $query
                    ->where('nama_semester', 'like', "%{$q}%")
                    ->orWhere('status_aktif', 'like', "%{$q}%")
            )
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.semesters.index', compact('semesters', 'q'));
    }

    public function create()
    {
        return view('admin.semesters.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_semester' => ['required', 'string', 'max:255'],
            'status_aktif' => ['required', Rule::in(['active', 'inactive'])],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
        ]);

        // kalau diset active, nonaktifkan yang lain
        if ($validated['status_aktif'] === 'active') {
            Semester::query()->update(['status_aktif' => 'inactive']);
        }

        Semester::create($validated);

        return redirect()
            ->route('admin.semesters.index')
            ->with('success', 'Semester berhasil ditambahkan.');
    }

    public function edit(Semester $semester)
    {
        return view('admin.semesters.edit', compact('semester'));
    }

    public function update(Request $request, Semester $semester)
    {
        $validated = $request->validate([
            'nama_semester' => ['required', 'string', 'max:255'],
            'status_aktif' => ['required', Rule::in(['active', 'inactive'])],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
        ]);

        if ($validated['status_aktif'] === 'active') {
            Semester::query()->where('id', '!=', $semester->id)->update(['status_aktif' => 'inactive']);
        }

        $semester->update($validated);

        return redirect()
            ->route('admin.semesters.index')
            ->with('success', 'Semester berhasil diperbarui.');
    }

    public function destroy(Semester $semester)
    {
        try {
            $semester->delete();

            return redirect()
                ->route('admin.semesters.index')
                ->with('success', 'Semester berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {

            return redirect()
                ->route('admin.semesters.index')
                ->with(
                    'error',
                    'Semester tidak bisa dihapus karena masih digunakan pada data akademik.'
                );
        }
    }
}
