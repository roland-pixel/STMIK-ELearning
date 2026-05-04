<?php

namespace Database\Seeders;

use App\Models\Dosen;
use App\Models\Jurusan;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
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

        Jurusan::create([
            'kode_jurusan' => 'SI',
            'nama_jurusan' => 'Sistem Informasi',
            'jenjang' => 'S1',
        ]);

        Jurusan::create([
            'kode_jurusan' => 'TI',
            'nama_jurusan' => 'Teknik Informatika',
            'jenjang' => 'S1',
        ]);

        $this->call([
            MataKuliahSeeder::class,
        ]);

        Semester::create([
            'nama_semester' => 'Ganjil 2022/2023',
            'status_aktif' => 'active',
            'tanggal_mulai' => '2022-11-30',
            'tanggal_selesai' => '2023-05-31',
        ]);

        Semester::create([
            'nama_semester' => 'Genap 2022/2023',
            'status_aktif' => 'inactive',
            'tanggal_mulai' => '2023-05-31',
            'tanggal_selesai' => '2023-11-30',
        ]);

        Semester::create([
            'nama_semester' => 'Ganjil 2023/2024',
            'status_aktif' => 'inactive',
            'tanggal_mulai' => '2023-11-30',
            'tanggal_selesai' => '2024-05-31',
        ]);

        Semester::create([
            'nama_semester' => 'Genap 2023/2024',
            'status_aktif' => 'inactive',
            'tanggal_mulai' => '2024-05-31',
            'tanggal_selesai' => '2024-11-30',
        ]);

        Semester::create([
            'nama_semester' => 'Ganjil 2024/2025',
            'status_aktif' => 'inactive',
            'tanggal_mulai' => '2024-11-30',
            'tanggal_selesai' => '2025-05-31',
        ]);

        Semester::create([
            'nama_semester' => 'Genap 2024/2025',
            'status_aktif' => 'inactive',
            'tanggal_mulai' => '2025-05-31',
            'tanggal_selesai' => '2025-11-30',
        ]);

        Semester::create([
            'nama_semester' => 'Ganjil 2025/2026',
            'status_aktif' => 'inactive',
            'tanggal_mulai' => '2025-11-30',
            'tanggal_selesai' => '2026-05-31',
        ]);

        Semester::create([
            'nama_semester' => 'Genap 2025/2026',
            'status_aktif' => 'inactive',
            'tanggal_mulai' => '2026-05-31',
            'tanggal_selesai' => '2026-11-30',
        ]);

        $userDosen = User::where('peran', 'dosen')->first();

        Dosen::create([
            'uuid' => Str::uuid(),
            'user_id' => $userDosen->id,
            'nip' => '1987654321',
        ]);

        // dosen kedua
        $user2 = User::create([
            'uuid' => Str::uuid(),
            'nama_lengkap' => 'Siti Dosen',
            'email' => 'dosen2@example.com',
            'password' => bcrypt('password'),
            'peran' => 'dosen',
        ]);

        Dosen::create([
            'uuid' => Str::uuid(),
            'user_id' => $user2->id,
            'nip' => '1234567890',
        ]);

        $jurusan1 = Jurusan::first();
        $jurusan2 = Jurusan::skip(1)->first();

        $user1 = User::where('peran', 'mahasiswa')->first();

        Mahasiswa::create([
            'uuid' => Str::uuid(),
            'user_id' => $user1->id,
            'jurusan_id' => $jurusan1->id,
            'nim' => '20230001',
            'angkatan' => 2023,
            'status' => 'aktif',
            'jenis_program' => 'reguler',
            'status_masuk' => 'normal',
        ]);

        $user2 = User::create([
            'uuid' => Str::uuid(),
            'nama_lengkap' => 'Rina Mahasiswa',
            'email' => 'mahasiswa2@example.com',
            'password' => bcrypt('password'),
            'peran' => 'mahasiswa',
        ]);

        Mahasiswa::create([
            'uuid' => Str::uuid(),
            'user_id' => $user2->id,
            'jurusan_id' => $jurusan1->id,
            'nim' => '20230002',
            'angkatan' => 2023,
            'status' => 'aktif',
            'jenis_program' => 'Reguler',
            'status_masuk' => 'normal',
        ]);
    }
}
