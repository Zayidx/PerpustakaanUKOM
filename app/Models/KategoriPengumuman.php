<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriPengumuman extends Model
{
    use HasFactory;

    protected $table = 'kategori_pengumuman';

    protected $fillable = [
        'nama',
        'deskripsi',
    ];

    public function pengumuman(): HasMany
    {
        return $this->hasMany(Pengumuman::class, 'kategori_pengumuman_id');
    }
}
