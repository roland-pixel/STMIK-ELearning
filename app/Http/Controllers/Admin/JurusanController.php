<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jurusan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JurusanController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');

        $jurusans = Jurusan::query()
            ->when(
                $q,
                fn($query) => $query
                    ->where('kode_jurusan', 'like', "%{$q}%")
                    ->orWhere('nama_jurusan', 'like', "%{$q}%")
                    ->orWhere('jenjang', 'like', "%{$q}%")
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.jurusans.index', compact('jurusans', 'q'));
    }

    public function create()
    {
        return view('admin.jurusans.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_jurusan' => ['required', 'string', 'max:50', 'unique:jurusans,kode_jurusan'],
            'nama_jurusan' => ['required', 'string', 'max:255'],
            'jenjang' => ['required', Rule::in(['S1', 'D3'])],
        ]);

        Jurusan::create($validated);

        return redirect()
            ->route('admin.jurusans.index')
            ->with('success', 'Jurusan berhasil ditambahkan.');
    }

    public function edit(Jurusan $jurusan)
    {
        return view('admin.jurusans.edit', compact('jurusan'));
    }

    public function update(Request $request, Jurusan $jurusan)
    {
        $validated = $request->validate([
            'kode_jurusan' => [
                'required',
                'string',
                'max:50',
                Rule::unique('jurusans', 'kode_jurusan')->ignore($jurusan->id),
            ],
            'nama_jurusan' => ['required', 'string', 'max:255'],
            'jenjang' => ['required', Rule::in(['S1', 'D3'])],
        ]);

        $jurusan->update($validated);

        return redirect()
            ->route('admin.jurusans.index')
            ->with('success', 'Jurusan berhasil diperbarui.');
    }

    public function destroy(Jurusan $jurusan)
    {
        $jurusan->delete();

        return redirect()
            ->route('admin.jurusans.index')
            ->with('success', 'Jurusan berhasil dihapus.');
    }
}
