<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JawabanDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'pengumpulan_id',
        'pertanyaan_id',
        'opsi_jawaban_id',
        'text_jawaban',
        'file_jawaban',
        'nilai_per_soal',
    ];
    protected $casts = [
        'nilai_per_soal' => 'float',
    ];


    public function pengumpulan()
    {
        return $this->belongsTo(Pengumpulan::class);
    }

    public function pertanyaan()
    {
        return $this->belongsTo(Pertanyaan::class);
    }

    public function opsiJawaban()
    {
        return $this->belongsTo(OpsiJawaban::class);
    }
}
