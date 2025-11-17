<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_perpus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nip')->nullable();
            $table->string('mata_pelajaran')->nullable();
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan'])->nullable(); // **tanpa ->change()**
            $table->string('foto')->nullable();
            $table->timestamps();
            $table->string('alamat')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_perpus');
    }
};
