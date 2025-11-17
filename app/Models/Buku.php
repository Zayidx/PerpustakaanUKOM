<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        return $this->resolveCoverUrl($this->cover_depan);
    }

    public function getCoverBelakangUrlAttribute(): ?string
    {
        return $this->resolveCoverUrl($this->cover_belakang);
    }

    private function resolveCoverUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $normalized = ltrim($path, '/');

        if (Str::startsWith($normalized, ['http://', 'https://'])) {
            return $normalized;
        }

        if (Str::startsWith($normalized, 'assets/')) {
            return asset($normalized);
        }

        if (Str::startsWith($normalized, 'storage/')) {
            return asset($normalized);
        }

        $publicPath = public_path($normalized);
        if (is_file($publicPath)) {
            return asset($normalized);
        }

        $storagePath = storage_path('app/public/'.$normalized);
        if (is_file($storagePath)) {
            return asset('storage/'.$normalized);
        }

        if (Storage::disk('public')->exists($normalized)) {
            return Storage::url($normalized);
        }

        return null;
    }
}
