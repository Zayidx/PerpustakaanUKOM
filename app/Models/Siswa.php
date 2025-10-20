<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Siswa extends Model
{
    use HasFactory;

    protected $table = 'siswa';

    protected $fillable = [
        'user_id',
        'nisn',
        'nis',
        'alamat',
        'jenis_kelamin',
        'foto',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
