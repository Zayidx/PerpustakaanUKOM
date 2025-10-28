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
            $table->foreignId('author_id')->constrained('authors')->cascadeOnDelete();
            $table->foreignId('kategori_id')->constrained('kategori_buku')->cascadeOnDelete();
            $table->foreignId('penerbit_id')->nullable()->constrained('penerbit')->nullOnDelete();
            $table->text('deskripsi');
            $table->date('tanggal_terbit');
            $table->string('cover_depan')->nullable();
            $table->string('cover_belakang')->nullable();
            $table->unsignedInteger('stok')->default(0);
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
