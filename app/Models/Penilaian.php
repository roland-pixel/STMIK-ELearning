<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penilaian extends Model
{
    protected $table = 'penilaians';
    use HasFactory;

    protected $fillable = [
        'uuid',
        'kelas_id',
        'judul',
        'instruksi',
        'kategori',
        'mode_penilaian',
        'tenggat_waktu',
    ];

    protected $casts = [
        'kategori'        => 'string',
        'mode_penilaian'  => 'string',
        'tenggat_waktu'   => 'datetime',
    ];


    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function pertanyaans()
    {
        return $this->hasMany(Pertanyaan::class);
    }

    public function getRouteKeyName()
    {
        return 'uuid'; // ✅ IMPORTANT!
    }
}
