<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Materi extends Model
{
    protected $table = 'materis';
    use HasFactory;

    protected $fillable = [
        'kelas_id',
        'judul',
        'deskripsi',
        'file_path',
        'link_url',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }
}
