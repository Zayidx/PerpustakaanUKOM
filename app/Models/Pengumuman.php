<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Pengumuman extends Model
{
    use HasFactory;

    protected $table = 'pengumuman';

    protected $fillable = [
        'judul',
        'slug',
        'kategori_pengumuman_id',
        'owner_id',
        'thumbnail_url',
        'thumbnail_caption',
        'konten',
        'status',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriPengumuman::class, 'kategori_pengumuman_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function getKontenHtmlAttribute(): string
    {
        if (! $this->konten) {
            return '';
        }

        return Str::markdown($this->konten, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }
}
