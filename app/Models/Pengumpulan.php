<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengumpulan extends Model
{
    protected $table = 'pengumpulans';
    use HasFactory;

    protected $casts = [
        'waktu_mulai'   => 'datetime',
        'waktu_selesai' => 'datetime',
        'nilai_total'   => 'float',
    ];


    protected $fillable = [
        'uuid',
        'penilaian_id',
        'mahasiswa_id',
        'waktu_mulai',
        'waktu_selesai',
        'nilai_total',
    ];

    public function penilaian()
    {
        return $this->belongsTo(Penilaian::class);
    }

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function jawabanDetails()
    {
        return $this->hasMany(JawabanDetail::class);
    }
}
