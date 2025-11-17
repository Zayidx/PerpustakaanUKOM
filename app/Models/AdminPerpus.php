<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminPerpus extends Model
{
    use HasFactory;

    protected $table = 'admin_perpus';

    protected $fillable = [
        'user_id',
        'nip',
        'mata_pelajaran',
        'jenis_kelamin',
        'alamat', 
        'foto',
    ];

    // Relasi ke tabel users
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
