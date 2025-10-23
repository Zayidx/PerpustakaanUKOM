<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('buku', function (Blueprint $table) {
            $table->id();
            $table->string('nama_buku'); 
            $table->foreignId('author_id')->constrained('authors')->onDelete('cascade'); 
            $table->foreignId('kategori_id')->constrained('kategori_buku')->onDelete('cascade'); 
            $table->string('penerbit'); 
            $table->text('deskripsi'); 
            $table->date('tanggal_terbit'); 
            $table->year('tahun_terbit'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buku');
    }
};
