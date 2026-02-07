<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnggotaKelas extends Model
{
    use HasFactory;

    protected $table = 'anggota_kelases';

    protected $fillable = [
        'kelas_id',
        'mahasiswa_id',
        'tanggal_gabung',
    ];

    protected $casts = [
        'tanggal_gabung' => 'datetime',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }
}
