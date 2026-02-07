<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MataKuliah;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MataKuliahController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');

        $mataKuliahs = MataKuliah::query()
            ->when(
                $q,
                fn($query) => $query
                    ->where('kode_mk', 'like', "%{$q}%")
                    ->orWhere('nama_mk', 'like', "%{$q}%")
                    ->orWhere('jenis_mk', 'like', "%{$q}%")
                    ->orWhere('sks', 'like', "%{$q}%")
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.mata_kuliahs.index', compact('mataKuliahs', 'q'));
    }

    public function create()
    {
        return view('admin.mata_kuliahs.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_mk' => ['required', 'string', 'max:50', 'unique:mata_kuliahs,kode_mk'],
            'nama_mk' => ['required', 'string', 'max:255'],
            'sks' => ['required', 'integer', 'min:1', 'max:10'],
            'jenis_mk' => ['required', Rule::in(['Umum', 'Spesial'])],
        ]);

        MataKuliah::create($validated);

        return redirect()
            ->route('admin.mata_kuliahs.index')
            ->with('success', 'Mata kuliah berhasil ditambahkan.');
    }

    public function edit(MataKuliah $mata_kuliah)
    {
        return view('admin.mata_kuliahs.edit', [
            'mataKuliah' => $mata_kuliah
        ]);
    }

    public function update(Request $request, MataKuliah $mata_kuliah)
    {
        $validated = $request->validate([
            'kode_mk' => [
                'required',
                'string',
                'max:50',
                Rule::unique('mata_kuliahs', 'kode_mk')->ignore($mata_kuliah->id),
            ],
            'nama_mk' => ['required', 'string', 'max:255'],
            'sks' => ['required', 'integer', 'min:1', 'max:10'],
            'jenis_mk' => ['required', Rule::in(['Umum', 'Spesial'])],
        ]);

        $mata_kuliah->update($validated);

        return redirect()
            ->route('admin.mata_kuliahs.index')
            ->with('success', 'Mata kuliah berhasil diperbarui.');
    }

    public function destroy(MataKuliah $mata_kuliah)
    {
        $mata_kuliah->delete();

        return redirect()
            ->route('admin.mata_kuliahs.index')
            ->with('success', 'Mata kuliah berhasil dihapus.');
    }
}
