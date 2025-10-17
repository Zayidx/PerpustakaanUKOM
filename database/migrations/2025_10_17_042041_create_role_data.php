<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    #Membuat tabel role_data
    public function up(): void
    {
        Schema::create('role_data', function (Blueprint $table) {
            $table->id();
            $table->string('nama_role');
            $table->string('deskripsi_role')->nullable();
            $table->string('icon_role');
            $table->timestamps();
        });
    }

 
     
    public function down(): void
    {
        Schema::dropIfExists('role_data');
    }
};
