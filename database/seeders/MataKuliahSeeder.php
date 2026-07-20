<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MataKuliahSeeder extends Seeder
{
    public function run(): void
    {
        $si = 1;
        $ti = 2; // Sesuaikan ID jurusan di tabel jurusans Anda

        // Format: 'NAMA MK' => [SKS, [[JurusanID, Kode], ...]]
        $map = [
            'PEMROGRAMAN DASAR' => [4, [[$si, 'KAB3101'], [$ti, 'KAB4101']]],
            'PENGANTAR TEKNOLOGI INFORMASI' => [4, [[$si, 'KAB3102']]],
            'PAKET PEMROGRAMAN APLIKASI' => [4, [[$si, 'KAB3103'], [$ti, 'KAB4102']]],
            'ALJABAR LINIER' => [2, [[$si, 'KIT3101'], [$ti, 'KIT4101']]],
            'PENDIDIKAN PANCASILA' => [2, [[$si, 'KPP3101'], [$ti, 'KPP4101']]],
            'BAHASA INGGRIS I' => [2, [[$si, 'KPP3102'], [$ti, 'KPP4102']]],
            'JARINGAN KOMPUTER' => [4, [[$si, 'KAB3204'], [$ti, 'KAB4204']]],
            'LOGIKA ALGORITMA' => [4, [[$si, 'KAB3205'], [$ti, 'KAB4203']]],
            'PENGETAHUAN BISNIS' => [2, [[$si, 'KIT3202']]],
            'SISTEM OPERASI' => [4, [[$si, 'KIT3203'], [$ti, 'KAB4205']]],
            'KONSEP SISTEM INFORMASI' => [2, [[$si, 'KIT3204']]],
            'BAHASA INGGRIS II' => [2, [[$si, 'KPP3203'], [$ti, 'KPP4203']]],
            'BAHASA INDONESIA' => [2, [[$si, 'KPP3204'], [$ti, 'KPP4204']]],
            'PEMROGRAMAN BERORIENTASI OBYEK' => [4, [[$si, 'KAB3306'], [$ti, 'KAB4307']]],
            'SISTEM BASIS DATA' => [4, [[$si, 'KAB3307'], [$ti, 'KAB4306']]],
            'TEKNIK & INSTALASI KOMPUTER' => [2, [[$si, 'KAB3308'], [$ti, 'KIT4513']]],
            'PENGANTAR MANAJEMEN' => [2, [[$si, 'KIT3305']]],
            'STATISTIKA' => [4, [[$si, 'KIT3306'], [$ti, 'KIT4306']]],
            'MANAJEMEN SAINS' => [2, [[$si, 'KIT3307']]],
            'PENDIDIKAN AGAMA I' => [2, [[$si, 'KPP3305'], [$ti, 'KPP4305']]],
            'STRUKTUR DATA' => [4, [[$si, 'KAB3409'], [$ti, 'KIT4409']]],
            'APLIKASI INTERNET' => [4, [[$si, 'KAB3410'], [$ti, 'KAB4408']]],
            'DESAIN GRAFIS' => [4, [[$si, 'KAB3411'], [$ti, 'KAB4409']]],
            'PENDIDIKAN ANTI KORUPSI' => [2, [[$si, 'KBB3406'], [$ti, 'KPP4808']]],
            'ANALISA SISTEM INFORMASI' => [4, [[$si, 'KIT3408']]],
            'KOMPUTER & MASYARAKAT' => [2, [[$si, 'KPB3401']]],
            'PEMROGRAMAN MOBILE I' => [4, [[$si, 'KAB3512'], [$ti, 'KAB4512']]],
            'PEMROGRAMAN BASIS DATA' => [4, [[$si, 'KAB3513']]],
            'VISUALISASI INFORMASI' => [4, [[$si, 'KAB3514'], [$ti, 'KAB4511']]],
            'TESTING & IMPLEMENTASI SI' => [4, [[$si, 'KIT3509']]],
            'PERANCANGAN SISTEM INFORMASI' => [4, [[$si, 'KIT3510']]],
            'SISTEM INFORMASI MANAJEMEN' => [4, [[$ti, 'KIT4410']]],
            'METODOLOGI PENELITIAN' => [2, [[$si, 'KPB3502'], [$ti, 'KPB4501']]],
            'TEKNOLOGI BASIS DATA' => [4, [[$si, 'KAB3615']]],
            'PEMROGRAMAN MOBILE II' => [4, [[$si, 'KAB3616'], [$ti, 'KAB4616']]],
            'REKAYASA PERANGKAT LUNAK' => [4, [[$si, 'KAB3617'], [$ti, 'KAB4613']]],
            'MANAJEMEN PROYEK SI' => [4, [[$si, 'KIT3611']]],
            'ETIKA PROFESI' => [2, [[$si, 'KPB3603'], [$ti, 'KPB4602']]],
            'PENDIDIKAN AGAMA II' => [2, [[$si, 'KPP3607'], [$ti, 'KPP4606']]],
            'INTERNET UNTUK SEGALA' => [4, [[$si, 'KAB3718'], [$ti, 'KAB4717']]],
            'RISET TEKNOLOGI INFORMASI' => [4, [[$si, 'KAB3719'], [$ti, 'KAB4510']]],
            'SISTEM TERDISTRIBUSI' => [4, [[$si, 'KAB3720'], [$ti, 'KAB4718']]],
            'KECAKAPAN ANTAR PERSONAL' => [2, [[$si, 'KBB3701'], [$ti, 'KBB4701']]],
            'PRAKTEK KERJA LAPANGAN' => [2, [[$si, 'KBB3702'], [$ti, 'KBB4702']]],
            'E-COMMERCE' => [4, [[$si, 'KIT3712']]],
            'PRA SKRIPSI' => [2, [[$si, 'KPB3704'], [$ti, 'KPB4703']]],
            'PEMROGRAMAN WEB' => [4, [[$si, 'KAB3821']]],
            'PENDIDIKAN KEWARGANEGARAAN' => [2, [[$si, 'KPP3808'], [$ti, 'KPP4807']]],
            'SKRIPSI' => [6, [[$ti, 'KPB4804'], [$si, 'KPB3805']]],
            'KALKULUS' => [2, [[$ti, 'KIT4102']]],
            'LOGIKA INFORMATIKA' => [2, [[$ti, 'KIT4103']]],
            'SISTEM DIGITAL' => [2, [[$ti, 'KIT4205']]],
            'MATEMATIKA DISKRIT' => [2, [[$ti, 'KIT4204']]],
            'KECERDASAN BUATAN' => [4, [[$ti, 'KIT4308']]],
            'METODE NUMERIK' => [2, [[$ti, 'KIT4307']]],
            'DATA MINING' => [4, [[$ti, 'KIT4411']]],
            'ARSITEKTUR DAN ORGANISASI KOMPUTER' => [4, [[$ti, 'KAB4614']]],
            'MIKROKONTROLLER' => [4, [[$ti, 'KIT4512']]],
            'PEMROGRAMAN GRAFIK' => [4, [[$ti, 'KAB4615']]],
            'INTERAKSI MANUSIA & KOMPUTER' => [2, [[$ti, 'KAB4820']]],
            'SISTEM INFORMASI GEOGRAFIS' => [4, [[$ti, 'KAB4819']]],
            'PENGOLAHAN CITRA DIGITAL' => [4, [[$ti, 'KIT4714']]],
        ];

        foreach ($map as $nama => $info) {
            $kodeContoh = $info[1][0][1];
            $mkId = DB::table('mata_kuliahs')->insertGetId([
                'nama_mk'     => $nama,
                'sks'         => $info[0],
                'jenis_mk' => (
                    Str::contains($nama, ['SKRIPSI', 'PRA SKRIPSI', 'PRAKTEK KERJA LAPANGAN'])
                    ? 'Spesial'
                    : 'Umum'
                ),
                'kategori_mk' => Str::substr($kodeContoh, 0, 3),
                'created_at'  => now(),
            ]);

            foreach ($info[1] as $m) {
                DB::table('kurikulums')->insert([
                    'mata_kuliah_id'  => $mkId,
                    'jurusan_id'      => $m[0],
                    'kode_mk_jurusan' => $m[1],
                    'created_at'      => now(),
                ]);
            }
        }
    }
}
