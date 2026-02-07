<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_semester',
        'status_aktif',
        'tanggal_mulai',
        'tanggal_selesai',
    ];

    public function kelases()
    {
        return $this->hasMany(Kelas::class);
    }

    public function bimbingans()
    {
        return $this->hasMany(Bimbingan::class);
    }
}
