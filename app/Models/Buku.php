<?php

namespace App\Models;

use App\Support\CoverUrlResolver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Buku extends Model
{
    use HasFactory;

    protected $table = 'buku';

    protected $fillable = [
        'nama_buku',
        'author_id',
        'kategori_id',
        'penerbit_id',
        'deskripsi',
        'tanggal_terbit',
        'cover_depan',
        'cover_belakang',
        'stok',
    ];

    protected $appends = [
        'cover_depan_url',
        'cover_belakang_url',
    ];

    protected $casts = [
        'tanggal_terbit' => 'date',
        'stok' => 'integer',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriBuku::class, 'kategori_id');
    }

    public function penerbit(): BelongsTo
    {
        return $this->belongsTo(Penerbit::class);
    }

    public function getCoverDepanUrlAttribute(): ?string
    {
        return CoverUrlResolver::resolve($this->cover_depan);
    }

    public function getCoverBelakangUrlAttribute(): ?string
    {
        return CoverUrlResolver::resolve($this->cover_belakang);
    }
}
