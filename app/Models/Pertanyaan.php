<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pertanyaan extends Model
{
    protected $table = 'pertanyaans';
    use HasFactory;

    protected $fillable = [
        'penilaian_id',
        'nomor_urut',
        'text_pertanyaan',
        'jenis_pertanyaan',
        'bobot_soal',
    ];

    public function penilaian()
    {
        return $this->belongsTo(Penilaian::class);
    }

    public function opsiJawabans()
    {
        return $this->hasMany(OpsiJawaban::class);
    }

    public function images()
    {
        return $this->hasMany(PertanyaanImage::class);
    }
}
