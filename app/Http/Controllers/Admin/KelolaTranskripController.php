<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use App\Models\Mahasiswa;
use Illuminate\Support\Str;

class KelolaTranskripController extends Controller
{
    public function index(Request $request)
    {
        $mahasiswas = Mahasiswa::with('user', 'jurusan')
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('nama_lengkap', 'like', '%' . $request->search . '%');
                })->orWhere('nim', 'like', '%' . $request->search . '%');
            })
            ->when($request->filled('jurusan_id'), function ($query) use ($request) {
                $query->where('jurusan_id', $request->jurusan_id);
            })
            ->when($request->filled('angkatan'), function ($query) use ($request) {
                $query->where('angkatan', $request->angkatan);
            })
            ->orderBy('nim', 'asc')
            ->paginate(15);

        // Pass search params to maintain filters in pagination
        $mahasiswas->appends($request->only(['search', 'jurusan_id', 'angkatan']));

        $jurusans = DB::table('jurusans')->orderBy('nama_jurusan')->get();
        $angkatans = Mahasiswa::selectRaw('DISTINCT angkatan')
            ->orderBy('angkatan', 'asc')
            ->pluck('angkatan');

        return view('admin.transkrip.index', compact('mahasiswas', 'jurusans', 'angkatans'));
    }

    private function getTranskripData($mahasiswaId)
    {
        // Query Gabungan Umum & Spesial dengan pengambilan nilai tertinggi
        $subQuery = DB::table('rekap_nilais as r')
            ->join('mata_kuliahs as mk', 'r.mata_kuliah_id', '=', 'mk.id')
            ->where('r.mahasiswa_id', $mahasiswaId)
            ->select(
                'mk.kode_mk',
                'mk.nama_mk',
                'mk.sks',
                'r.nilai_akhir_angka',
                'r.nilai_indeks'
            )
            ->union(
                DB::table('bimbingans as b')
                    ->join('mata_kuliahs as mk', 'b.mata_kuliah_id', '=', 'mk.id')
                    ->where('b.mahasiswa_id', $mahasiswaId)
                    ->where('b.status', 'approved')
                    ->select(
                        'mk.kode_mk',
                        'mk.nama_mk',
                        'mk.sks',
                        'b.nilai_angka as nilai_akhir_angka',
                        DB::raw('NULL as nilai_indeks')
                    )
            );

        // Grouping untuk mengambil nilai terbaik jika ada matkul yang sama (retake)
        $data = DB::table(DB::raw("({$subQuery->toSql()}) as combined"))
            ->mergeBindings($subQuery)
            ->select(
                'kode_mk',
                'nama_mk',
                'sks',
                DB::raw('MAX(nilai_akhir_angka) as nilai_angka')
            )
            ->groupBy('kode_mk', 'nama_mk', 'sks')
            ->orderBy('kode_mk', 'asc')
            ->get();

        return $data->map(function ($item) {
            $item->huruf = $this->konversiHuruf($item->nilai_angka);
            $item->indeks = $this->konversiIndeks($item->nilai_angka);
            $item->bobot = $item->sks * $item->indeks;
            return $item;
        });
    }

    private function konversiHuruf($n)
    {
        if ($n >= 90) return 'A';
        if ($n >= 86) return 'A-';
        if ($n >= 80) return 'B+';
        if ($n >= 76) return 'B';
        if ($n >= 70) return 'B-';
        if ($n >= 66) return 'C+';
        if ($n >= 60) return 'C';
        if ($n >= 55) return 'C-';
        if ($n >= 40) return 'D';
        return 'E';
    }

    private function konversiIndeks($n)
    {
        if ($n >= 90) return 4.0;
        if ($n >= 86) return 3.5;
        if ($n >= 80) return 3.25;
        if ($n >= 76) return 3.0;
        if ($n >= 70) return 2.75;
        if ($n >= 66) return 2.5;
        if ($n >= 60) return 2.0;
        if ($n >= 55) return 1.5;
        if ($n >= 40) return 1.0;
        return 0.0;
    }

    public function cetak($id)
    {
        $mahasiswa = Mahasiswa::with(['user', 'jurusan'])->findOrFail($id);
        $allData = $this->getTranskripData($id);

        // Pecah data menjadi 2 kolom (kiri & kanan) seperti di gambar
        $totalData = $allData->count();
        $half = ceil($totalData / 2);
        $leftColumn = $allData->slice(0, $half);
        $rightColumn = $allData->slice($half);

        $totalSks = $allData->sum('sks');
        $totalKredit = $allData->sum('bobot'); // Total SKS x Indeks
        $ipk = $totalSks > 0 ? round($totalKredit / $totalSks, 2) : 0;

        $data = [
            'mahasiswa' => $mahasiswa,
            'leftColumn' => $leftColumn,
            'rightColumn' => $rightColumn,
            'totalSks' => $totalSks,
            'totalKredit' => $totalKredit,
            'ipk' => $ipk,
            'tanggal_cetak' => now()->translatedFormat('d F Y')
        ];

        return PDF::loadView('admin.transkrip.cetak', $data)
            ->setPaper('a4', 'portrait')
            ->stream("Transkrip-{$mahasiswa->nim}.pdf");
    }
}
