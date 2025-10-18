<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleData extends Model
{
    use HasFactory;

    protected $table = 'role_data';

    protected $fillable = [
        'nama_role',
        'deskripsi_role',
        'icon_role',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }
}
