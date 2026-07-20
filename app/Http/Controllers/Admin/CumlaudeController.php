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

    /**
     * Halaman Daftar Mahasiswa Cumlaude
     */
    public function index(Request $request)
    {
        // 1. Cari ID jurusan SI untuk dijadikan default
        $defaultJurusan = Jurusan::where('nama_jurusan', 'SI')
            ->orWhere('kode_jurusan', 'SI')
            ->first();

        // 2. Ambil filter dari request, jika kosong gunakan ID jurusan SI
        $jurusanId = $request->query('jurusan_id') ?? ($defaultJurusan ? $defaultJurusan->id : null);

        // PERBAIKAN: Ubah dari 'angkatan' menjadi 'tahun_lulus' (Default ke tahun sekarang jika kosong)
        $tahunLulus = $request->query('tahun_lulus') ?? date('Y');

        $filters = [
            'jurusan_id'  => $jurusanId,
            'tahun_lulus' => $tahunLulus, // Diubah agar sinkron dengan Service
        ];

        // 3. Ambil daftar cumlaude berdasarkan filter tahun kelulusan
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
            'daftar'          => $daftar,
            'total'           => $daftar->count(),
            'jurusans'        => Jurusan::all(),
            'selectedJurusan' => $jurusanId,
            'selectedTahun'   => $tahunLulus, // Dikirim ke view untuk mengisi input/dropdown tahun
        ]);
    }

    /**
     * FITUR BARU: Halaman Daftar Mahasiswa Terbaik (Ranking Berdasarkan IPK)
     */
    /**
     * FITUR BARU: Halaman Daftar Mahasiswa Terbaik (Ranking Berdasarkan IPK)
     */
    public function mahasiswaTerbaik(Request $request)
    {
        // 1. Ambil Default Jurusan SI
        $defaultJurusan = Jurusan::where('nama_jurusan', 'SI')
            ->orWhere('kode_jurusan', 'SI')
            ->first();

        $jurusanId = $request->query('jurusan_id') ?? ($defaultJurusan ? $defaultJurusan->id : null);
        $tahunLulus = $request->query('tahun_lulus') ?? date('Y');

        $filters = [
            'jurusan_id'  => $jurusanId,
            'tahun_lulus' => $tahunLulus,
        ];

        // 2. Ambil semua kandidat mahasiswa terbaik yang sudah di-ranking berdasarkan IPK
        $kandidatTerbaik = collect($this->service->getDaftarMahasiswaTerbaik($filters))->take(3);
        // ---------------------------------------------------------------------
        // EXPORT EXCEL DENGAN DESAIN STYLING (TANPA FILE SEPARATE)
        // ---------------------------------------------------------------------
        if ($request->query('export')) {

            $headings = ['Peringkat', 'NIM', 'Nama Lengkap', 'Jurusan', 'Angkatan', 'IPK', 'Nilai Skripsi'];
            $dataRows = [];

            foreach ($kandidatTerbaik as $index => $mhs) {
                $dataRows[] = [
                    $index + 1,
                    $mhs?->nim ?? '-',
                    $mhs?->nama_lengkap ?? '-',
                    $mhs?->nama_jurusan ?? ($mhs?->jurusan?->nama_jurusan ?? '-'),
                    $mhs?->angkatan ?? '-',
                    doubleval($mhs?->ipk ?? 0), // Di-convert ke float/double agar bisa diformat angka di Excel
                    $mhs?->nilai_skripsi ?? '-',
                ];
            }

            // Gabungan beberapa interface untuk mempercantik Excel
            return Excel::download(
                new class($headings, $dataRows) implements
                    \Maatwebsite\Excel\Concerns\FromCollection,
                    \Maatwebsite\Excel\Concerns\WithHeadings,
                    \Maatwebsite\Excel\Concerns\ShouldAutoSize, // Auto-lebar kolom
                    \Maatwebsite\Excel\Concerns\WithStyles // Desain Warna & Font
                {
                    public function __construct(private array $headings, private array $data) {}

                    public function collection()
                    {
                        return collect($this->data);
                    }

                    public function headings(): array
                    {
                        return $this->headings;
                    }

                    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
                    {
                        // Menghitung jumlah baris data untuk border dinamism
                        $highestRow = count($this->data) + 1; // +1 karena ada header

                        // 1. Styling untuk Header (Baris 1)
                        $sheet->getStyle('A1:G1')->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'color' => ['rgb' => 'FFFFFF'], // Teks Putih
                                'size' => 11,
                            ],
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['rgb' => '800000'], // Warna Maroon
                            ],
                            'alignment' => [
                                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                            ],
                        ]);

                        // 2. Styling untuk Seluruh Isi Tabel (A1 sampai kolom terakhir G)
                        $sheet->getStyle("A1:G{$highestRow}")->applyFromArray([
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                    'color' => ['rgb' => 'D1D5DB'], // Border abu-abu tipis (slate-300)
                                ],
                            ],
                        ]);

                        // 3. Merapikan Alignment Kolom Tertentu (Misal: Peringkat, NIM, Angkatan, IPK di tengah)
                        $sheet->getStyle("A2:A{$highestRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("B2:B{$highestRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("E2:F{$highestRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("G2:G{$highestRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                        // 4. Format desimal untuk kolom IPK (Kolom F) agar rapi dua angka di belakang koma (misal: 3.75)
                        $sheet->getStyle("F2:F{$highestRow}")->getNumberFormat()->setFormatCode('0.00');

                        return [];
                    }
                },
                'mahasiswa-terbaik-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        // 3. Ambil 1 orang baris pertama sebagai Mahasiswa Terbaik Utama
        $terbaikUtama = collect($kandidatTerbaik)->first();

        return view('admin.mahasiswa-terbaik.index', [
            'terbaik'         => $terbaikUtama,
            'semuaKandidat'   => $kandidatTerbaik,
            'jurusans'        => Jurusan::all(),
            'selectedJurusan' => $jurusanId,
            'selectedTahun'   => $tahunLulus,
        ]);
    }
}
