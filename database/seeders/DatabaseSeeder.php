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
        // 1. MASTER USERS (ADMIN & UTAMA)
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
        // 5. MASTER DATA DOSEN (BERDASARKAN FOTO)
        // ==========================================
        $listDosen = [
            ['nama' => 'Siti Cholifah, S.Kom, M.Kom', 'nip' => '19680508 200501 2 003'],
            ['nama' => 'Husnul Ma\'ad Junaidi, S.Kom, M.Kom', 'nip' => '19691110 200501 1 003'],
            ['nama' => 'Dr. Muhammad Syaukani, ST, M.Cs, M.Kom', 'nip' => '19730417 200501 1 003'],
            ['nama' => 'Yeffriansjah Salim, S.Kom, M.Kom', 'nip' => '19750828 200501 1 002'],
            ['nama' => 'Ahmad Shalludin, S.Kom, M.Kom, MM', 'nip' => '19740718 200501 1 003'],
            ['nama' => 'Johan Wahyudi, S.Kom, M.Kom', 'nip' => '19770921 200501 1 003'],
            ['nama' => 'Seradi Angkasa, SE, M.Kom', 'nip' => '01.1507.061'],
            ['nama' => 'Endi Gunawan, SE, M.Kom', 'nip' => '01.1409.060'],
            ['nama' => 'Feiliana Tan, ST, MT', 'nip' => '01.9608.016'],
            ['nama' => 'Liliana Swastina, S.Kom, M.Kom', 'nip' => '01.9803.019'],
            ['nama' => 'Iwan Fitriady Mukhlis, S.Kom, M.Kom', 'nip' => '01.9910.021'],
            ['nama' => 'Indra Pranata, S.Kom, M.Kom', 'nip' => '01.0904.038'],
            ['nama' => 'Rahmat Hidayat, S.Kom, M.Kom', 'nip' => '01.1009.042'],
            ['nama' => 'Amrul Hadiyanoor, S.Kom, M.Kom', 'nip' => '01.1109.046'],
            ['nama' => 'Bambang Lareno, ST, M.Kom', 'nip' => '01.1205.051'],
            ['nama' => 'Helda Yunita, S.Kom, M.Kom', 'nip' => '01.1407.059'],
            ['nama' => 'Ihdalhubbi Maulida, S.Kom, M.Kom', 'nip' => '01.1601.063'],
            ['nama' => 'SRI MELATI, S.Kom, M.Kom', 'nip' => '01.1909.067'],
            ['nama' => 'RAFIE, S.Kom, M.Kom', 'nip' => '01.1909.066'],
        ];

        foreach ($listDosen as $dsn) {
            // Potong gelar belakang untuk prefix email biar rapi (opsional, menggunakan slug dasar)
            $cleanDsnName = Str::slug(explode(',', $dsn['nama'])[0], '');
            $emailDosen = $cleanDsnName . '@stmik.id';

            $userDsn = User::create([
                'uuid' => Str::uuid(),
                'nama_lengkap' => $dsn['nama'],
                'email' => $emailDosen,
                'password' => Hash::make('password'),
                'peran' => 'dosen',
            ]);

            Dosen::create([
                'uuid' => Str::uuid(),
                'user_id' => $userDsn->id,
                'nip' => $dsn['nip'],
            ]);
        }

        // ==========================================
        // 6 & 7. DATA MAHASISWA (REVISI + KELAS MALAM)
        // ==========================================
        $listMahasiswa = [
            // Sistem Informasi (2203...) - Reguler
            ['nama' => 'HIJRINA HALISA', 'nim' => '22031014'],
            ['nama' => 'M.Arifin', 'nim' => '22031015'],
            ['nama' => 'Muhammad Rafiqi', 'nim' => '22031026'],
            ['nama' => 'putri', 'nim' => '22031018'],
            ['nama' => 'Noor afifah', 'nim' => '22031003'],
            ['nama' => 'Chary Amalia.K.', 'nim' => '22031001'],
            ['nama' => 'Norliani', 'nim' => '22031004'],
            ['nama' => 'Muhammad Alfianor Fitri', 'nim' => '22031024'],
            ['nama' => 'Emmy Wahyuni', 'nim' => '22031012'],
            ['nama' => 'Anthony Eka Sanur', 'nim' => '22031016'],
            ['nama' => 'Kharis Raihan', 'nim' => '22031013'],

            // Teknik Informatika (2204...) - Reguler
            ['nama' => 'Pasha', 'nim' => '22041036'],
            ['nama' => 'Khoirunnisa', 'nim' => '22041009'],
            ['nama' => 'Raudhatul Inayah', 'nim' => '22041007'],
            ['nama' => 'Felinta Galiana', 'nim' => '22041012'],
            ['nama' => 'Nor Siffa A.', 'nim' => '22041016'],
            ['nama' => 'Yeni Widia S.', 'nim' => '22041038'],
            ['nama' => 'Susi Mardalena', 'nim' => '22041041'],
            ['nama' => 'Budi Indrawan', 'nim' => '22041002'],
            ['nama' => 'Panji Saputra', 'nim' => '22041004'],
            ['nama' => 'Abdul Rasyid', 'nim' => '22041005'],
            ['nama' => 'Januar Saputra', 'nim' => '22041010'],
            ['nama' => 'Danda', 'nim' => '22041011'],
            ['nama' => 'Andilwan', 'nim' => '22041013'],
            ['nama' => 'M.Dicky Iman S.', 'nim' => '22041017'],
            ['nama' => 'M. Irvan Sauqi', 'nim' => '22041018'],
            ['nama' => 'M. Reza Anwar', 'nim' => '22041020'],
            ['nama' => 'M. Samman', 'nim' => '22041021'],
            ['nama' => 'Ewa Rise Pasifik', 'nim' => '22041024'],
            ['nama' => 'Irfan Maulana', 'nim' => '22041026'],
            ['nama' => 'Bima Setyawan', 'nim' => '22041029'],
            ['nama' => 'M. Fadhil Akbar', 'nim' => '22041031'],
            ['nama' => 'Ferdi Reo T.', 'nim' => '22041034'],
            ['nama' => 'M. Sukma Atmaja', 'nim' => '22041035'],
            ['nama' => 'M. Syabani', 'nim' => '22042028'],

            // DATA KELAS MALAM (jenis_program = malam)
            ['nama' => 'ARIYANTO', 'nim' => '22042014', 'program' => 'malam'],
            ['nama' => 'PUJI NUGROHO', 'nim' => '22042037', 'program' => 'malam'],
            ['nama' => 'YAHYA UBAI\'D', 'nim' => '22042015', 'program' => 'malam'],
            ['nama' => 'SHOFIE LADONNA', 'nim' => '22041023', 'program' => 'malam'],
        ];

        foreach ($listMahasiswa as $mhs) {
            $cleanName = Str::slug($mhs['nama'], '');
            $randomDigits = rand(1000, 9999);
            $emailCustom = $cleanName . $randomDigits . '@stmik.id';

            $jurusanId = Str::startsWith($mhs['nim'], '2203') ? $jurusan1->id : $jurusan2->id;
            $jenisProgram = isset($mhs['program']) && $mhs['program'] === 'malam' ? 'malam' : 'reguler';

            $user = User::create([
                'uuid' => Str::uuid(),
                'nama_lengkap' => $mhs['nama'],
                'email' => $emailCustom,
                'password' => bcrypt('password'),
                'peran' => 'mahasiswa',
            ]);

            Mahasiswa::create([
                'uuid' => Str::uuid(),
                'user_id' => $user->id,
                'jurusan_id' => $jurusanId,
                'nim' => $mhs['nim'],
                'angkatan' => 2022,
                'status' => 'aktif',
                'jenis_program' => $jenisProgram,
                'status_masuk' => 'normal',
                'tanggal_masuk' => '2022-09-01',
            ]);
        }

        // ==========================================
        // 8. JALANKAN SEEDER GENERATE KELAS & ROMBEL
        // ==========================================
        $this->call([
            KelasSeeder::class,
            KelasMalamSeeder::class,
            NilaiBulkSeeder::class,
            BimbinganSeeder::class,
        ]);
    }
}
