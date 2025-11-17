<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeminjamanPenalty extends Model
{
    protected $fillable = [
        'peminjaman_id',
        'admin_perpus_id',
        'late_days',
        'amount',
        'paid_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    public function peminjaman(): BelongsTo
    {
        return $this->belongsTo(Peminjaman::class);
    }

    public function adminPerpus(): BelongsTo
    {
        return $this->belongsTo(AdminPerpus::class);
    }
}
