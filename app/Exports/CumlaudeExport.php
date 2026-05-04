<?php

namespace App\Exports;

use App\Services\CumlaudeService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class CumlaudeExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $service;
    protected $filters;

    public function __construct(CumlaudeService $service, array $filters = [])
    {
        $this->service = $service;
        $this->filters = $filters;
    }

    public function collection()
    {
        return $this->service->getDaftarCumlaude($this->filters)->map(function ($mhs, $index) {
            $mhs->peringkat = $index + 1;
            return $mhs;
        });
    }

    public function headings(): array
    {
        return [
            ['LAPORAN DAFTAR MAHASISWA CUMLAUDE'], // Judul besar
            ['Tanggal Cetak: ' . now()->format('d-m-Y H:i')],
            [''], // Baris kosong
            [
                'RANK',
                'NIM',
                'NAMA LENGKAP',
                'JURUSAN',
                'ANGKATAN',
                'IPK',
                'NILAI SKRIPSI'
            ]
        ];
    }

    public function map($mhs): array
    {
        return [
            $mhs->peringkat,
            $mhs->nim,
            $mhs->nama_lengkap,
            $mhs->nama_jurusan,
            $mhs->angkatan,
            number_format($mhs->ipk, 2),
            $mhs->nilai_skripsi ?? '-'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Gabungkan sel untuk judul (Merge Cells)
        $sheet->mergeCells('A1:G1');
        $sheet->mergeCells('A2:G2');

        return [
            // Style Judul Utama
            1 => [
                'font' => ['bold' => true, 'size' => 16],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ],

            // Style Header Tabel (Baris ke-4)
            4 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '7F1D1D'] // Warna Maroon sesuai tema UI
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ],

            // Beri border untuk seluruh data (dinamis)
            'A4:G' . ($this->collection()->count() + 4) => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }
}
