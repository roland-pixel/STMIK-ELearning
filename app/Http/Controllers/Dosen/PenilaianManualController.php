<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Penilaian;
use App\Models\Pengumpulan;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PenilaianManualController extends Controller
{
    private function ensureOwner(Kelas $kelas): void
    {
        $dosenId = Auth::user()?->dosen?->id;
        abort_if(!$dosenId, 403, 'Akun dosen tidak valid.');
        abort_if((int) $kelas->dosen_id !== (int) $dosenId, 403, 'Tidak berhak mengakses kelas ini.');
    }

    public function create(Kelas $kelas)
    {
        $this->ensureOwner($kelas);

        // Ambil daftar mahasiswa di kelas ini untuk diinput nilainya
        $mahasiswas = $kelas->anggotaKelases()
            ->with('mahasiswa.user')
            ->get()
            ->map(fn($item) => [
                'id' => $item->mahasiswa->id,
                'nim' => $item->mahasiswa->nim,
                'nama' => $item->mahasiswa->user->nama_lengkap,
                'nilai' => 0, // Default nilai awal di form
            ]);

        $usedCategories = Penilaian::where('kelas_id', $kelas->id)
            ->whereIn('kategori', ['uts', 'uas'])
            ->pluck('kategori')
            ->toArray();

        return Inertia::render('Dosen/Kelas/Tugas/Penilaian/Manual/Create', [
            'kelas' => $kelas->only(['id', 'uuid', 'nama_kelas']),
            'mahasiswas' => $mahasiswas,
            'usedCategories' => $usedCategories,
        ]);
    }

    public function store(Request $request, Kelas $kelas)
    {
        $this->ensureOwner($kelas);

        // Ambil ID mahasiswa yang sah di kelas ini
        $allowedIds = $kelas->anggotaKelases()->pluck('mahasiswa_id')->toArray();

        $data = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'in:tugas,uts,uas'],
            'instruksi' => ['nullable', 'string'],
            'nilai_mahasiswa' => ['required', 'array'],
            'nilai_mahasiswa.*.id' => ['required', \Illuminate\Validation\Rule::in($allowedIds)],
            'nilai_mahasiswa.*.nilai' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        // ... (validasi UTS/UAS tetap sama)

        return DB::transaction(function () use ($kelas, $data) {
            $penilaian = Penilaian::create([
                'uuid' => (string) Str::uuid(),
                'kelas_id' => $kelas->id,
                'judul' => $data['judul'],
                'instruksi' => $data['instruksi'],
                'kategori' => $data['kategori'],
                'mode_penilaian' => 'manual',
            ]);

            foreach ($data['nilai_mahasiswa'] as $item) {
                Pengumpulan::create([
                    'uuid' => (string) Str::uuid(),
                    'penilaian_id' => $penilaian->id,
                    'mahasiswa_id' => $item['id'],
                    'waktu_mulai' => now(),
                    'waktu_selesai' => now(),
                    'nilai_total' => $item['nilai'],
                ]);
            }

            // TIPS: Panggil Job atau Service di sini untuk hitung ulang rekap_nilais

            return redirect()->route('dosen.kelas.show', $kelas->uuid)
                ->with('success', 'Nilai berhasil disimpan.');
        });
    }
}
