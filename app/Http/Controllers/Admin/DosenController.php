<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\Jurusan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DosenController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');
        $homebase = $request->get('homebase');

        $dosens = Dosen::query()
            ->with('user')
            ->when(
                $q,
                fn($query) => $query->where('nip', 'like', "%{$q}%")
                    ->orWhereHas(
                        'user',
                        fn($u) => $u
                            ->where('nama_lengkap', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%")
                    )
            )
            ->when(
                $homebase,
                fn($query) => $query->where('homebase', $homebase)
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // Ambil semua homebase unik yang ada di DB untuk opsi filter
        $homebases = Dosen::whereNotNull('homebase')
            ->distinct()
            ->orderBy('homebase')
            ->pluck('homebase');

        return view('admin.dosens.index', compact('dosens', 'q', 'homebase', 'homebases'));
    }

    public function create()
    {
        $jurusans = Jurusan::all();
        return view('admin.dosens.create', compact('jurusans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'nip' => ['required', 'string', 'max:50', 'unique:dosens,nip'],
            'homebase' => ['required', 'exists:jurusans,nama_jurusan'],
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'uuid' => (string) Str::uuid(),
                'nama_lengkap' => $validated['nama_lengkap'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'peran' => 'dosen',
            ]);

            Dosen::create([
                'uuid' => (string) Str::uuid(),
                'user_id' => $user->id,
                'nip' => $validated['nip'],
                'homebase' => $validated['homebase'],
            ]);
        });

        return redirect()
            ->route('admin.dosens.index')
            ->with('success', 'Dosen berhasil ditambahkan.');
    }

    public function edit(Dosen $dosen)
    {
        $dosen->load('user');
        $jurusans = Jurusan::all();
        return view('admin.dosens.edit', compact('dosen', 'jurusans'));
    }

    public function update(Request $request, Dosen $dosen)
    {
        $dosen->load('user');

        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($dosen->user_id),
            ],
            'password' => ['nullable', 'string', 'min:8'],
            'nip' => [
                'required',
                'string',
                'max:50',
                Rule::unique('dosens', 'nip')->ignore($dosen->id),
            ],
            'homebase' => ['required', 'exists:jurusans,nama_jurusan'],
        ]);

        DB::transaction(function () use ($validated, $dosen) {
            $dosen->user->update([
                'nama_lengkap' => $validated['nama_lengkap'],
                'email' => $validated['email'],
                'peran' => 'dosen', // jaga-jaga
            ]);

            if (!empty($validated['password'])) {
                $dosen->user->update([
                    'password' => Hash::make($validated['password']),
                ]);
            }

            $dosen->update([
                'nip' => $validated['nip'],
                'homebase' => $validated['homebase'],
            ]);
        });

        return redirect()
            ->route('admin.dosens.index')
            ->with('success', 'Dosen berhasil diperbarui.');
    }

    public function destroy(Dosen $dosen)
    {
        // Karena dosens.user_id constrained cascade, kalau user dihapus dosen ikut terhapus.
        // Lebih aman: hapus user saja.
        $user = $dosen->user;

        DB::transaction(function () use ($user) {
            $user->delete();
        });

        return redirect()
            ->route('admin.dosens.index')
            ->with('success', 'Dosen berhasil dihapus.');
    }
}
