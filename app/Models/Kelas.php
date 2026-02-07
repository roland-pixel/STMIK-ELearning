<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    protected $table = 'kelases';
    use HasFactory;

    protected $fillable = [
        'uuid',
        'dosen_id',
        'mata_kuliah_id',
        'semester_id',
        'nama_kelas',
        'kode_gabung',
        'deskripsi',
        'persentase_tugas',
        'persentase_uts',
        'persentase_uas',
    ];

    public function dosen()
    {
        return $this->belongsTo(Dosen::class);
    }

    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function anggotaKelases()
    {
        return $this->hasMany(AnggotaKelas::class);
    }

    public function materis()
    {
        return $this->hasMany(Materi::class);
    }

    public function penilaians()
    {
        return $this->hasMany(Penilaian::class);
    }
    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function mahasiswas()
    {
        return $this->belongsToMany(
            Mahasiswa::class,
            'anggota_kelases',   // <-- PENTING: pivot table kamu
            'kelas_id',
            'mahasiswa_id'
        )->withPivot(['tanggal_gabung'])->withTimestamps();
    }
}
