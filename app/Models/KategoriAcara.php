<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class KategoriAcara extends Model
{
    use HasFactory;

    protected $table = 'kategori_acara';

    protected $fillable = [
        'nama',
        'slug',
        'deskripsi',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $kategori) {
            if (! $kategori->slug) {
                $kategori->slug = self::generateSlug($kategori->nama);
            }
        });

        static::updating(function (self $kategori) {
            if ($kategori->isDirty('nama')) {
                $kategori->slug = self::generateSlug($kategori->nama, $kategori->id);
            }
        });
    }

    private static function generateSlug(string $nama, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($nama) ?: 'kategori-acara';
        $slug = $baseSlug;
        $counter = 1;

        while (self::where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        return $slug;
    }

    public function acara(): HasMany
    {
        return $this->hasMany(Acara::class, 'kategori_acara_id');
    }
}
