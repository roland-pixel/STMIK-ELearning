<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RekapNilai extends Model
{
    use HasFactory;

    protected $fillable = [
        'mahasiswa_id',
        'semester_id',
        'mata_kuliah_id',
        'kelas_id',
        'total_tugas',
        'total_uts',
        'total_uas',
        'nilai_akhir_angka',
        'nilai_huruf',
        'nilai_indeks',
    ];

    protected $casts = [
        'total_tugas'        => 'float',
        'total_uts'          => 'float',
        'total_uas'          => 'float',
        'nilai_akhir_angka'  => 'float',
        'nilai_indeks'       => 'float',
    ];


    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }
}
