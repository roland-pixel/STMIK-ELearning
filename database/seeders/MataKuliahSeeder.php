<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MataKuliahSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            // Kode, Nama, SKS
            ['KAB3101', 'PEMROGRAMAN DASAR', 4],
            ['KAB3102', 'PENGANTAR TEKNOLOGI INFOR', 4],
            ['KAB3103', 'PAKET PEMROGRAMAN APLIKAS', 4],
            ['KIT3101', 'ALJABAR LINIER', 2],
            ['KPP3101', 'PENDIDIKAN PANCASILA', 2],
            ['KPP3102', 'BAHASA INGGRIS I', 2],
            ['KAB3204', 'JARINGAN KOMPUTER', 4],
            ['KAB3205', 'LOGIKA ALGORITMA', 4],
            ['KIT3202', 'PENGETAHUAN BISNIS', 2],
            ['KIT3203', 'SISTEM OPERASI', 4],
            ['KIT3204', 'KONSEP SISTEM INFORMASI', 2],
            ['KPP3203', 'BAHASA INGGRIS II', 2],
            ['KPP3204', 'BAHASA INDONESIA', 2],
            ['KAB3306', 'PEMROGRAMAN BERORIENTASI', 4],
            ['KAB3307', 'SISTEM BASIS DATA', 4],
            ['KAB3308', 'TEKNIK & INSTALASI KOMP.', 2],
            ['KIT3305', 'PENGANTAR MANAJEMEN', 2],
            ['KIT3306', 'STATISTIKA', 4],
            ['KIT3307', 'MANAJEMEN SAINS', 2],
            ['KPP3305', 'PENDIDIKAN AGAMA I', 2],
            ['KAB3409', 'STRUKTUR DATA', 4],
            ['KAB3410', 'APLIKASI INTERNET', 4],
            ['KAB3411', 'DESAIN GRAFIS', 4],
            ['KBB3406', 'PENDIDIKAN ANTI KORUPSI', 2],
            ['KIT3408', 'ANALISA SISTEM INFORMASI', 4],
            ['KPB3401', 'KOMPUTER & MASYARAKAT', 2],
            ['KAB3512', 'PEMROGRAMAN MOBILE I', 4],
            ['KAB3513', 'PEMROGRAMAN BASIS DATA', 4],
            ['KAB3514', 'VISUALISASI INFORMASI', 4],
            ['KIT3509', 'TESTING & IMPLEMENTASI SI', 4],
            ['KIT3510', 'PERANCANGAN SISTEM INFORM', 4],
            ['KPB3502', 'METODOLOGI PENELITIAN', 2],
            ['KAB3615', 'TEKNOLOGI BASIS DATA', 4],
            ['KAB3616', 'PEMROGRAMAN MOBILE II', 4],
            ['KAB3617', 'REKAYASA PERANGKAT LUNAK', 4],
            ['KIT3611', 'MANAJEMEN PROYEK SI', 4],
            ['KPB3603', 'ETIKA PROFESI', 2],
            ['KPP3607', 'PENDIDIKAN AGAMA II', 2],
            ['KAB3718', 'INTERNET UNTUK SEGALA', 4],
            ['KAB3719', 'RISET TEKNOLOGI INFORMASI', 4],
            ['KAB3720', 'SISTEM TERDISTRIBUSI', 4],
            ['KBB3701', 'KECAKAPAN ANTAR PERSONAL', 2],
            ['KBB3702', 'PRAKTEK KERJA LAPANGAN', 2],
            ['KIT3712', 'E-COMMERCE', 4],
            ['KPB3704', 'PRA SKRIPSI', 2],
            ['KAB3821', 'PEMROGRAMAN WEB', 4],
            ['KPP3808', 'PENDIDIKAN KEWARGANEGARAA', 2],
        ];

        foreach ($data as $item) {
            $kode = $item[0];
            $nama = $item[1];
            $sks  = $item[2];

            // Logika Kategori MK berdasarkan 3 huruf pertama kode
            $kategori = substr($kode, 0, 3);

            // Logika Jenis MK: Spesial hanya untuk Pra Skripsi (dan Skripsi ke depannya)
            $jenis = (Str::contains($nama, 'PRA SKRIPSI') || Str::contains($nama, 'SKRIPSI'))
                ? 'Spesial'
                : 'Umum';

            DB::table('mata_kuliahs')->insert([
                'kode_mk'     => $kode,
                'nama_mk'     => $nama,
                'sks'         => $sks,
                'jenis_mk'    => $jenis,
                'kategori_mk' => $kategori,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }
}
