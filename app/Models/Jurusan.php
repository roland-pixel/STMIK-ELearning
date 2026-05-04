<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jurusan extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_jurusan',
        'nama_jurusan',
        'jenjang',
    ];

    public function mahasiswas()
    {
        return $this->hasMany(Mahasiswa::class);
    }

    public function kurikulums()
    {
        return $this->hasMany(Kurikulum::class);
    }

    public function mataKuliahs()
    {
        return $this->belongsToMany(MataKuliah::class, 'kurikulums', 'jurusan_id', 'mata_kuliah_id')
            ->withPivot('kode_mk_jurusan');
    }
}
