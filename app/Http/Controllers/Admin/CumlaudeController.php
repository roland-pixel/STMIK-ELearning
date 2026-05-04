<?php

namespace App\Http\Controllers\Admin;

use App\Exports\CumlaudeExport;
use App\Http\Controllers\Controller;
use App\Services\CumlaudeService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Jurusan;

class CumlaudeController extends Controller
{
    public function __construct(private CumlaudeService $service) {}

    public function index(Request $request)
    {
        // 1. Cari ID jurusan SI untuk dijadikan default
        // Sesuaikan 'SI' dengan nama_jurusan atau kode_jurusan di database-mu
        $defaultJurusan = Jurusan::where('nama_jurusan', 'SI')
            ->orWhere('kode_jurusan', 'SI')
            ->first();

        // 2. Ambil filter dari request, jika kosong gunakan ID jurusan SI
        $jurusanId = $request->query('jurusan_id') ?? ($defaultJurusan ? $defaultJurusan->id : null);

        $filters = [
            'jurusan_id' => $jurusanId,
            'angkatan'   => $request->query('angkatan'),
        ];

        // 3. Ambil daftar cumlaude berdasarkan filter
        $daftar = $this->service
            ->getDaftarCumlaude($filters)
            ->map(function ($mhs, $index) {
                $mhs->peringkat = $index + 1;
                return $mhs;
            });

        // 4. Logika Export (tetap membawa filter yang aktif)
        if ($request->query('export')) {
            return Excel::download(
                new CumlaudeExport($this->service, $filters),
                'cumlaude-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return view('admin.cumlaude.index', [
            'daftar'           => $daftar,
            'total'            => $daftar->count(),
            'jurusans'         => Jurusan::all(),
            'selectedJurusan'  => $jurusanId, // Kirim ini ke view untuk menandai "selected" di dropdown
        ]);
    }
}
