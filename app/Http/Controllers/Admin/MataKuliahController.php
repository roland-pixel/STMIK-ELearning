<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MataKuliah;
use App\Models\Jurusan;
use App\Models\Kurikulum;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class MataKuliahController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');
        $jenis = $request->get('jenis');
        $kategori = $request->get('kategori');

        $mataKuliahs = MataKuliah::query()
            ->with('kurikulums.jurusan')
            ->when($jenis, fn($query, $jenis) => $query->where('jenis_mk', $jenis))
            ->when($kategori, fn($query, $kategori) => $query->where('kategori_mk', $kategori))
            ->when($q, function ($query, $q) {
                return $query->where(function ($sub) use ($q) {
                    $sub->where('nama_mk', 'like', "%{$q}%")
                        ->orWhere('kategori_mk', 'like', "%{$q}%")
                        ->orWhere('sks', 'like', "%{$q}%")
                        ->orWhereHas('kurikulums', fn($k) => $k->where('kode_mk_jurusan', 'like', "%{$q}%"));
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.mata_kuliahs.index', compact('mataKuliahs', 'q', 'jenis', 'kategori'));
    }

    public function create()
    {
        $jurusans = Jurusan::all();
        return view('admin.mata_kuliahs.create', compact('jurusans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_mk' => ['required', 'string', 'max:255'],
            'sks' => ['required', 'integer', 'min:1', 'max:10'],
            'jenis_mk' => ['required', Rule::in(['Umum', 'Spesial'])],
            'kategori_mk' => ['required', Rule::in(['KPP', 'KIT', 'KAB', 'KPB', 'KBB'])],
            'mapping' => ['required', 'array'], // Array berisi [jurusan_id => kode_mk]
        ]);

        DB::transaction(function () use ($validated) {
            $mk = MataKuliah::create([
                'nama_mk' => $validated['nama_mk'],
                'sks' => $validated['sks'],
                'jenis_mk' => $validated['jenis_mk'],
                'kategori_mk' => $validated['kategori_mk'],
            ]);

            foreach ($validated['mapping'] as $jurusan_id => $kode) {
                if (!empty($kode)) {
                    $mk->kurikulums()->create([
                        'jurusan_id' => $jurusan_id,
                        'kode_mk_jurusan' => $kode,
                    ]);
                }
            }
        });

        return redirect()->route('admin.mata_kuliahs.index')->with('success', 'Mata kuliah berhasil ditambahkan.');
    }

    public function edit(MataKuliah $mata_kuliah)
    {
        $mata_kuliah->load('kurikulums');
        $jurusans = Jurusan::all();
        return view('admin.mata_kuliahs.edit', [
            'mataKuliah' => $mata_kuliah,
            'jurusans' => $jurusans
        ]);
    }

    public function update(Request $request, MataKuliah $mata_kuliah)
    {
        $validated = $request->validate([
            'nama_mk' => ['required', 'string', 'max:255'],
            'sks' => ['required', 'integer', 'min:1', 'max:10'],
            'jenis_mk' => ['required', Rule::in(['Umum', 'Spesial'])],
            'kategori_mk' => ['required', Rule::in(['KPP', 'KIT', 'KAB', 'KPB', 'KBB'])],
            'mapping' => ['required', 'array'],
        ]);

        DB::transaction(function () use ($mata_kuliah, $validated) {
            $mata_kuliah->update([
                'nama_mk' => $validated['nama_mk'],
                'sks' => $validated['sks'],
                'jenis_mk' => $validated['jenis_mk'],
                'kategori_mk' => $validated['kategori_mk'],
            ]);

            // Hapus mapping lama dan input ulang agar konsisten
            $mata_kuliah->kurikulums()->delete();
            foreach ($validated['mapping'] as $jurusan_id => $kode) {
                if (!empty($kode)) {
                    $mata_kuliah->kurikulums()->create([
                        'jurusan_id' => $jurusan_id,
                        'kode_mk_jurusan' => $kode,
                    ]);
                }
            }
        });

        return redirect()->route('admin.mata_kuliahs.index')->with('success', 'Mata kuliah diperbarui.');
    }

    public function destroy(MataKuliah $mata_kuliah)
    {
        try {
            $mata_kuliah->delete(); // Kurikulums akan terhapus otomatis jika menggunakan onDelete('cascade') di migrasi
            return redirect()->route('admin.mata_kuliahs.index')->with('success', 'Mata kuliah dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {

            return redirect()
                ->route('admin.mata_kuliahs.index')
                ->with(
                    'error',
                    'Mata kuliah tidak bisa dihapus karena masih digunakan pada data akademik.'
                );
        }
    }
}
