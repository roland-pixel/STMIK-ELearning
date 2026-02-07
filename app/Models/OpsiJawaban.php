<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpsiJawaban extends Model
{
    use HasFactory;

    protected $fillable = [
        'pertanyaan_id',
        'teks_opsi',
        'is_benar',
    ];

    public function pertanyaan()
    {
        return $this->belongsTo(Pertanyaan::class);
    }
}
