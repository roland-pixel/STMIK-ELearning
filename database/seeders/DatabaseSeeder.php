<?php

namespace Database\Seeders;

use App\Models\Dosen;
use App\Models\Jurusan;
use App\Models\Mahasiswa;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ==========================================
        // 1. MASTER USERS (AKUN AKSES UTAMA)
        // ==========================================
        User::create([
            'uuid' => Str::uuid(),
            'nama_lengkap' => 'Evelyn Chevalier',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'peran' => 'admin',
        ]);

        User::create([
            'uuid' => Str::uuid(),
            'nama_lengkap' => 'Mejiro Ramonu',
            'email' => 'dosen@example.com',
            'password' => Hash::make('password'),
            'peran' => 'dosen',
        ]);

        User::create([
            'uuid' => Str::uuid(),
            'nama_lengkap' => 'Alexander Granat',
            'email' => 'mahasiswa@example.com',
            'password' => Hash::make('password'),
            'peran' => 'mahasiswa',
        ]);

        // ==========================================
        // 2. MASTER DATA JURUSAN
        // ==========================================
        $jurusan1 = Jurusan::create([
            'kode_jurusan' => 'SI',
            'nama_jurusan' => 'Sistem Informasi',
            'jenjang' => 'S1',
        ]);

        $jurusan2 = Jurusan::create([
            'kode_jurusan' => 'TI',
            'nama_jurusan' => 'Teknik Informatika',
            'jenjang' => 'S1',
        ]);

        // ==========================================
        // 3. PANGGIL MASTER SEEDER MATA KULIAH
        // ==========================================
        $this->call([
            MataKuliahSeeder::class,
        ]);

        // ==========================================
        // 4. MASTER DATA SEMESTER
        // ==========================================
        Semester::create([
            'nama_semester' => 'Ganjil 2022/2023',
            'status_aktif' => 'active',
            'tanggal_mulai' => '2022-11-30',
            'tanggal_selesai' => '2023-05-31',
        ]);

        $semesterLainnya = [
            'Genap 2022/2023',
            'Ganjil 2023/2024',
            'Genap 2023/2024',
            'Ganjil 2024/2025',
            'Genap 2024/2025',
            'Ganjil 2025/2026',
            'Genap 2025/2026'
        ];

        foreach ($semesterLainnya as $smstr) {
            Semester::create([
                'nama_semester' => $smstr,
                'status_aktif' => 'inactive',
                'tanggal_mulai' => '2023-05-31',
                'tanggal_selesai' => '2023-11-30',
            ]);
        }

        // ==========================================
        // 5. MASTER DATA DOSEN
        // ==========================================
        $userDosenUtama = User::where('peran', 'dosen')->first();
        Dosen::create([
            'uuid' => Str::uuid(),
            'user_id' => $userDosenUtama->id,
            'nip' => '1987654321',
        ]);

        // Dosen Kedua (Siti Dosen)
        $userDosenSiti = User::create([
            'uuid' => Str::uuid(),
            'nama_lengkap' => 'Siti Dosen',
            'email' => 'dosen2@example.com',
            'password' => bcrypt('password'),
            'peran' => 'dosen',
        ]);

        Dosen::create([
            'uuid' => Str::uuid(),
            'user_id' => $userDosenSiti->id,
            'nip' => '1234567890',
        ]);

        // ==========================================
        // 6. DATA MAHASISWA - SISTEM INFORMASI (SI)
        // ==========================================
        $userMhs1 = User::where('peran', 'mahasiswa')->first();
        Mahasiswa::create([
            'uuid' => Str::uuid(),
            'user_id' => $userMhs1->id,
            'jurusan_id' => $jurusan1->id,
            'nim' => '22031001',
            'angkatan' => 2022,
            'status' => 'aktif',
            'jenis_program' => 'reguler',
            'status_masuk' => 'normal',
        ]);

        $userMhs2 = User::create([
            'uuid' => Str::uuid(),
            'nama_lengkap' => 'Rina Mahasiswa',
            'email' => 'mahasiswa2@example.com',
            'password' => bcrypt('password'),
            'peran' => 'mahasiswa',
        ]);

        Mahasiswa::create([
            'uuid' => Str::uuid(),
            'user_id' => $userMhs2->id,
            'jurusan_id' => $jurusan1->id,
            'nim' => '22031002',
            'angkatan' => 2022,
            'status' => 'aktif',
            'jenis_program' => 'Reguler',
            'status_masuk' => 'normal',
        ]);

        $userMhs5 = User::create([
            'uuid' => Str::uuid(),
            'nama_lengkap' => 'Palmer',
            'email' => 'mahasiswa5@example.com',
            'password' => bcrypt('password'),
            'peran' => 'mahasiswa',
        ]);

        Mahasiswa::create([
            'uuid' => Str::uuid(),
            'user_id' => $userMhs5->id,
            'jurusan_id' => $jurusan1->id,
            'nim' => '22039001',
            'angkatan' => 2022,
            'status' => 'aktif',
            'jenis_program' => 'malam',
            'status_masuk' => 'normal',
        ]);

        // ==========================================
        // 7. DATA MAHASISWA - TEKNIK INFORMATIKA (TI)
        // ==========================================
        $userMhs3 = User::create([
            'uuid' => Str::uuid(),
            'nama_lengkap' => 'Agus',
            'email' => 'mahasiswa3@example.com',
            'password' => bcrypt('password'),
            'peran' => 'mahasiswa',
        ]);

        Mahasiswa::create([
            'uuid' => Str::uuid(),
            'user_id' => $userMhs3->id,
            'jurusan_id' => $jurusan2->id,
            'nim' => '22041001',
            'angkatan' => 2022,
            'status' => 'aktif',
            'jenis_program' => 'Reguler',
            'status_masuk' => 'normal',
        ]);

        $userMhs4 = User::create([
            'uuid' => Str::uuid(),
            'nama_lengkap' => 'Hendra',
            'email' => 'mahasiswa4@example.com',
            'password' => bcrypt('password'),
            'peran' => 'mahasiswa',
        ]);

        Mahasiswa::create([
            'uuid' => Str::uuid(),
            'user_id' => $userMhs4->id,
            'jurusan_id' => $jurusan2->id,
            'nim' => '22041002',
            'angkatan' => 2022,
            'status' => 'aktif',
            'jenis_program' => 'Reguler',
            'status_masuk' => 'normal',
        ]);

        $userMhs6 = User::create([
            'uuid' => Str::uuid(),
            'nama_lengkap' => 'Sirius',
            'email' => 'mahasiswa6@example.com',
            'password' => bcrypt('password'),
            'peran' => 'mahasiswa',
        ]);

        Mahasiswa::create([
            'uuid' => Str::uuid(),
            'user_id' => $userMhs6->id,
            'jurusan_id' => $jurusan2->id,
            'nim' => '22049001',
            'angkatan' => 2022,
            'status' => 'aktif',
            'jenis_program' => 'malam',
            'status_masuk' => 'normal',
        ]);

        // ==========================================
        // 8. JALANKAN SEEDER GENERATE KELAS & ROMBEL
        // ==========================================
        $this->call([
            KelasSeeder::class,
            KelasMalamSeeder::class,
        ]);
    }
}
