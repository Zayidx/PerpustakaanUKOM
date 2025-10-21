<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\KategoriAcara;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Carbon;

class Acara extends Model
{
    use HasFactory;

    protected $table = 'acara';

    protected $fillable = [
        'judul',
        'slug',
        'admin_id',
        'kategori_acara_id',
        'lokasi',
        'poster_url',
        'deskripsi',
        'mulai_at',
        'selesai_at',
    ];

    protected $casts = [
        'mulai_at' => 'datetime',
        'selesai_at' => 'datetime',
    ];

    protected function ringkasDeskripsi(): Attribute
    {
        return Attribute::get(function () {
            if (! $this->deskripsi) {
                return null;
            }

            return str($this->deskripsi)->stripTags()->limit(140);
        });
    }

    public function scopeSearch($query, string $term = null)
    {
        if ($term === null || $term === '') {
            return $query;
        }

        $term = '%' . $term . '%';

        return $query->where(function ($sub) use ($term) {
            $sub->where('judul', 'like', $term)
                ->orWhere('lokasi', 'like', $term)
                ->orWhere('deskripsi', 'like', $term);
        });
    }

    public function isUpcoming(): bool
    {
        return $this->mulai_at?->isFuture() ?? false;
    }

    public function durasiLabel(): string
    {
        $mulai = $this->mulai_at?->translatedFormat('H.i') ?? '-';
        $selesai = $this->selesai_at?->translatedFormat('H.i');

        return $selesai ? "{$mulai} - {$selesai}" : $mulai;
    }

    public function tanggalLabel(): string
    {
        return $this->mulai_at?->translatedFormat('d F Y') ?? '-';
    }

    public function hari(): string
    {
        return $this->mulai_at?->translatedFormat('d') ?? '--';
    }

    public function bulan(): string
    {
        return $this->mulai_at?->translatedFormat('M') ?? '--';
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriAcara::class, 'kategori_acara_id');
    }
}
