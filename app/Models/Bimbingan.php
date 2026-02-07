<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bimbingan extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'semester_id',
        'mahasiswa_id',
        'dosen_pembimbing_id',
        'mata_kuliah_id',
        'judul_penelitian',
        'nilai_angka',
        'status',
    ];

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'dosen_pembimbing_id');
    }

    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class);
    }
}
