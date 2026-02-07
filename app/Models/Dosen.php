<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dosen extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'nip',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kelases()
    {
        return $this->hasMany(Kelas::class);
    }

    public function bimbingans()
    {
        return $this->hasMany(Bimbingan::class, 'dosen_pembimbing_id');
    }
}
