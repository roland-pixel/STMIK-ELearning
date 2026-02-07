<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'jurusan_id',
        'nim',
        'angkatan',
        'status',
        'jenis_program',
        'status_masuk',
    ];

    protected $casts = [
        'status'         => 'string',
        'jenis_program'  => 'string',
        'status_masuk'   => 'string',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class);
    }

    public function anggotaKelases()
    {
        return $this->hasMany(AnggotaKelas::class);
    }

    public function bimbingans()
    {
        return $this->hasMany(Bimbingan::class);
    }
    public function kelases()
    {
        return $this->belongsToMany(
            Kelas::class,
            'anggota_kelases',   // <-- PENTING
            'mahasiswa_id',
            'kelas_id'
        )->withPivot(['tanggal_gabung'])->withTimestamps();
    }
}
