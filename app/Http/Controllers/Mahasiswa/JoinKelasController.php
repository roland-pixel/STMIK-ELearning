<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JoinKelasController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();

        abort_if($user->peran !== 'mahasiswa', 403);
        abort_if(!$user->mahasiswa, 403, 'Akun mahasiswa belum terhubung ke data mahasiswa.');

        $data = $request->validate([
            'kode_gabung' => ['required', 'string', 'max:255'],
        ]);

        $kode = trim($data['kode_gabung']);

        $kelas = Kelas::query()
            ->where('kode_gabung', $kode)
            ->whereHas('semester', fn($q) => $q->where('status_aktif', 'active'))
            ->first();

        if (!$kelas) {
            return back()->withErrors([
                'kode_gabung' => 'Kode gabung tidak ditemukan / kelas tidak aktif.',
            ])->withInput();
        }

        $mahasiswaId = $user->mahasiswa->id;

        // insert pivot, aman dari double join karena ada unique(kelas_id, mahasiswa_id)
        try {
            DB::table('anggota_kelases')->insert([
                'kelas_id' => $kelas->id,
                'mahasiswa_id' => $mahasiswaId,
                'tanggal_gabung' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // biasanya duplicate entry (sudah gabung)
            return back()->withErrors([
                'kode_gabung' => 'Kamu sudah terdaftar di kelas ini.',
            ])->withInput();
        }

        return redirect()
            ->route('mahasiswa.dashboard')
            ->with('success', 'Berhasil gabung kelas.');
    }
}
