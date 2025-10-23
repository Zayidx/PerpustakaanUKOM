<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Author extends Model
{
    use HasFactory;

    protected $table = 'authors';

    protected $fillable = [
        'nama_author',
        'email_author',
        'no_telp',
        'alamat',
        'foto',
    ];

    public function buku(): HasMany
    {
        return $this->hasMany(Buku::class);
    }
}
