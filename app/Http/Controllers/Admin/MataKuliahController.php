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
        // 1. Ambil semua input dari form
        $q = $request->get('q');
        $jenis = $request->get('jenis');
        $kategori = $request->get('kategori');

        $mataKuliahs = MataKuliah::query()
            // 2. Filter berdasarkan Jenis (Combobox)
            ->when($jenis, function ($query, $jenis) {
                return $query->where('jenis_mk', $jenis);
            })
            // 3. Filter berdasarkan Kategori (Combobox)
            ->when($kategori, function ($query, $kategori) {
                return $query->where('kategori_mk', $kategori);
            })
            // 4. Filter Pencarian Teks (Search Bar)
            // Dibungkus function agar orWhere tidak bertabrakan dengan filter jenis/kategori
            ->when($q, function ($query, $q) {
                return $query->where(function ($subQuery) use ($q) {
                    $subQuery->where('kode_mk', 'like', "%{$q}%")
                        ->orWhere('nama_mk', 'like', "%{$q}%")
                        ->orWhere('jenis_mk', 'like', "%{$q}%")
                        ->orWhere('kategori_mk', 'like', "%{$q}%")
                        ->orWhere('sks', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // 5. Jangan lupa kirim semua variabel filter kembali ke view agar input tidak reset
        return view('admin.mata_kuliahs.index', compact('mataKuliahs', 'q', 'jenis', 'kategori'));
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
            // Validasi untuk kategori_mk
            'kategori_mk' => ['required', Rule::in(['KPP', 'KIT', 'KAB', 'KPB', 'KBB'])],
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
            // Validasi untuk kategori_mk di update
            'kategori_mk' => ['required', Rule::in(['KPP', 'KIT', 'KAB', 'KPB', 'KBB'])],
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
