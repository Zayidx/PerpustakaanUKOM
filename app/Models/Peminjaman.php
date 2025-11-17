<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Peminjaman extends Model
{
    use HasFactory;

    protected $table = 'peminjaman_data';

    protected $fillable = [
        'kode',
        'siswa_id',
        'admin_perpus_id',
        'status',
        'accepted_at',
        'due_at',
        'returned_at',
        'metadata',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
        'due_at' => 'datetime',
        'returned_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function adminPerpus(): BelongsTo
    {
        return $this->belongsTo(AdminPerpus::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PeminjamanItem::class);
    }

    public function penalties(): HasMany
    {
        return $this->hasMany(PeminjamanPenalty::class);
    }
}
