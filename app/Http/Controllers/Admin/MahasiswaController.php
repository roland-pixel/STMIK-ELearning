<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jurusan;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MahasiswaController extends Controller
{
    public function index(Request $request)
    {
        // Tangkap semua input filter
        $q = $request->get('q');
        $angkatan = $request->get('angkatan');
        $jurusan_id = $request->get('jurusan_id');
        $status = $request->get('status');

        $mahasiswas = Mahasiswa::query()
            ->with(['user', 'jurusan'])
            // Filter Pencarian Global (NIM, Nama, dll)
            ->when($q, function ($query) use ($q) {
                $query->where(function ($query) use ($q) {
                    $query->where('nim', 'like', "%{$q}%")
                        ->orWhereHas('user', fn($u) => $u->where('nama_lengkap', 'like', "%{$q}%"));
                });
            })
            // Filter Spesifik Angkatan
            ->when($angkatan, fn($query) => $query->where('angkatan', $angkatan))
            // Filter Spesifik Jurusan
            ->when($jurusan_id, fn($query) => $query->where('jurusan_id', $jurusan_id))
            // Filter Spesifik Status
            ->when($status, fn($query) => $query->where('status', $status))

            ->latest()
            ->paginate(10)
            ->withQueryString();

        // Ambil data pendukung untuk dropdown filter di view
        $list_jurusan = Jurusan::orderBy('nama_jurusan')->get(['id', 'nama_jurusan']);
        $list_angkatan = Mahasiswa::select('angkatan')->distinct()->orderBy('angkatan', 'desc')->get();

        return view('admin.mahasiswas.index', compact(
            'mahasiswas',
            'q',
            'angkatan',
            'jurusan_id',
            'status',
            'list_jurusan',
            'list_angkatan'
        ));
    }

    public function create()
    {
        $jurusans = Jurusan::query()->orderBy('nama_jurusan')->get(['id', 'kode_jurusan', 'nama_jurusan', 'jenjang']);
        return view('admin.mahasiswas.create', compact('jurusans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // user
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],

            // mahasiswa
            'jurusan_id' => ['required', 'exists:jurusans,id'],
            'nim' => ['required', 'string', 'max:50', 'unique:mahasiswas,nim'],
            'angkatan' => ['required', 'integer', 'min:2000', 'max:' . (date('Y') + 1)],
            'status' => ['required', Rule::in(['aktif', 'lulus'])],
            'jenis_program' => ['required', Rule::in(['reguler', 'malam', 'pegawai'])],
            'status_masuk' => ['required', Rule::in(['transfer', 'normal'])],
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'uuid' => (string) Str::uuid(),
                'nama_lengkap' => $validated['nama_lengkap'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'peran' => 'mahasiswa',
            ]);

            Mahasiswa::create([
                'uuid' => (string) Str::uuid(),
                'user_id' => $user->id,
                'jurusan_id' => $validated['jurusan_id'],
                'nim' => $validated['nim'],
                'angkatan' => $validated['angkatan'],
                'status' => $validated['status'],
                'jenis_program' => $validated['jenis_program'],
                'status_masuk' => $validated['status_masuk'],
            ]);
        });

        return redirect()
            ->route('admin.mahasiswas.index')
            ->with('success', 'Mahasiswa berhasil ditambahkan.');
    }

    public function edit(Mahasiswa $mahasiswa)
    {
        $mahasiswa->load(['user', 'jurusan']);
        $jurusans = Jurusan::query()->orderBy('nama_jurusan')->get(['id', 'kode_jurusan', 'nama_jurusan', 'jenjang']);

        return view('admin.mahasiswas.edit', compact('mahasiswa', 'jurusans'));
    }

    public function update(Request $request, Mahasiswa $mahasiswa)
    {
        $mahasiswa->load('user');

        $validated = $request->validate([
            // user
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($mahasiswa->user_id),
            ],
            'password' => ['nullable', 'string', 'min:8'],

            // mahasiswa
            'jurusan_id' => ['required', 'exists:jurusans,id'],
            'nim' => [
                'required',
                'string',
                'max:50',
                Rule::unique('mahasiswas', 'nim')->ignore($mahasiswa->id),
            ],
            'angkatan' => ['required', 'integer', 'min:2000', 'max:' . (date('Y') + 1)],
            'status' => ['required', Rule::in(['aktif', 'lulus'])],
            'jenis_program' => ['required', Rule::in(['reguler', 'malam', 'pegawai'])],
            'status_masuk' => ['required', Rule::in(['transfer', 'normal'])],
        ]);

        DB::transaction(function () use ($validated, $mahasiswa) {
            $mahasiswa->user->update([
                'nama_lengkap' => $validated['nama_lengkap'],
                'email' => $validated['email'],
                'peran' => 'mahasiswa', // jaga-jaga
            ]);

            if (!empty($validated['password'])) {
                $mahasiswa->user->update([
                    'password' => Hash::make($validated['password']),
                ]);
            }

            $mahasiswa->update([
                'jurusan_id' => $validated['jurusan_id'],
                'nim' => $validated['nim'],
                'angkatan' => $validated['angkatan'],
                'status' => $validated['status'],
                'jenis_program' => $validated['jenis_program'],
                'status_masuk' => $validated['status_masuk'],
            ]);
        });

        return redirect()
            ->route('admin.mahasiswas.index')
            ->with('success', 'Mahasiswa berhasil diperbarui.');
    }

    public function destroy(Mahasiswa $mahasiswa)
    {
        try {
            // hapus user, mahasiswa ikut kehapus (cascade)
            $user = $mahasiswa->user;

            DB::transaction(function () use ($user) {
                $user->delete();
            });

            return redirect()
                ->route('admin.mahasiswas.index')
                ->with('success', 'Mahasiswa berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {

            return redirect()
                ->route('admin.mahasiswas.index')
                ->with(
                    'error',
                    'Mahasiswa tidak bisa dihapus karena masih digunakan pada data akademik.'
                );
        }
    }
}
