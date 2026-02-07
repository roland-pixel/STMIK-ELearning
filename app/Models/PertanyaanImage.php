<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PertanyaanImage extends Model
{
    use HasFactory;

    protected $table = 'pertanyaan_images';

    protected $fillable = [
        'pertanyaan_id',
        'path',
    ];

    public function pertanyaan()
    {
        return $this->belongsTo(Pertanyaan::class);
    }
}
