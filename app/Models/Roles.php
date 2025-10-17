<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Roles extends Model
{
    use HasFactory;

    #Memberikan atribut yang dapat diisi secara massal
    protected $fillable = [
        'nama_role',
        'deskripsi_role',
        'icon_role',
    ];

    #Relasi dengan model User
    function users(){
        return $this->hasMany(User::class);
    }
}
