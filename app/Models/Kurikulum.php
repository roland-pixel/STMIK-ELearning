<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kurikulum extends Model
{
    // Karena tabelnya bernama 'kurikulums' (Laravel default), tidak perlu mendefinisikan $table.

    protected $fillable = [
        'mata_kuliah_id',
        'jurusan_id',
        'kode_mk_jurusan',
    ];

    /**
     * Relasi ke MataKuliah
     */
    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class);
    }

    /**
     * Relasi ke Jurusan
     */
    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class);
    }
}
