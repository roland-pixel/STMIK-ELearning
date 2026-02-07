<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KelasController extends Controller
{
    public function updateSettings(Request $request, Kelas $kelas)
    {
        // pastikan dosen cuma bisa ubah kelas yang dia ampu
        $user = $request->user();
        $dosenId = $user->dosen?->id;

        if (!$dosenId || (int) $kelas->dosen_id !== (int) $dosenId) {
            abort(403);
        }

        $validated = $request->validate([
            'nama_kelas' => ['required', 'string', 'max:255'],
            'kode_gabung' => [
                'required',
                'string',
                'max:50',
                Rule::unique('kelases', 'kode_gabung')->ignore($kelas->id),
            ],
            'deskripsi' => ['nullable', 'string', 'max:2000'],

            'persentase_tugas' => ['required', 'integer', 'min:0', 'max:100'],
            'persentase_uts'   => ['required', 'integer', 'min:0', 'max:100'],
            'persentase_uas'   => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $total = (int) $validated['persentase_tugas']
            + (int) $validated['persentase_uts']
            + (int) $validated['persentase_uas'];

        if ($total !== 100) {
            return back()->withErrors([
                'persentase_tugas' => 'Total persentase (Tugas + UTS + UAS) harus 100.',
            ]);
        }

        $kelas->update($validated);

        return back()->with('success', 'Pengaturan kelas berhasil diperbarui.');
    }
}
