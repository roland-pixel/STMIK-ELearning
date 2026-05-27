<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\AnggotaKelas; // Pastikan Model ini di-import
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

class JoinKelasController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();

        // Validasi otoritas
        abort_if($user->peran !== 'mahasiswa', 403);
        abort_if(!$user->mahasiswa, 403, 'Akun mahasiswa belum terhubung ke data mahasiswa.');

        // Validasi input
        $data = $request->validate([
            'kode_gabung' => ['required', 'string', 'max:255'],
        ]);

        $kode = trim($data['kode_gabung']);

        // Cari kelas yang aktif
        $kelas = Kelas::query()
            ->where('kode_gabung', $kode)
            ->whereHas('semester', fn($q) => $q->where('status_aktif', 'active'))
            ->first();

        if (!$kelas) {
            return back()->withErrors([
                'kode_gabung' => 'Kode gabung tidak ditemukan atau kelas tidak aktif.',
            ])->withInput();
        }

        $mahasiswaId = $user->mahasiswa->id;

        try {
            // Menggunakan Eloquent Model agar Observer terpicu otomatis
            // Observer akan membuat record di rekap_nilais jika ada logika di sana
            AnggotaKelas::create([
                'kelas_id'     => $kelas->id,
                'mahasiswa_id' => $mahasiswaId,
                'tanggal_gabung' => now(),
            ]);

            // Catatan: Tidak perlu manual insert created_at/updated_at 
            // karena Eloquent menanganinya otomatis.

        } catch (QueryException $e) {
            // Jika error karena constraint database (misalnya sudah ada di kelas)
            // Error code 23000 adalah standar untuk integrity constraint violation
            if ($e->getCode() == 23000) {
                return back()->withErrors([
                    'kode_gabung' => 'Kamu sudah terdaftar di kelas ini.',
                ])->withInput();
            }

            // Jika error lain, lempar error aslinya
            throw $e;
        }

        return redirect()
            ->route('mahasiswa.dashboard')
            ->with('success', 'Berhasil gabung kelas.');
    }
}
