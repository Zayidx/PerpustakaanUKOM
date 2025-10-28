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
        Schema::create('peminjaman_data', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->foreignId('siswa_id')
                ->constrained('siswa')
                ->cascadeOnDelete();
            $table->foreignId('guru_id')
                ->nullable()
                ->constrained('guru')
                ->nullOnDelete();
            $table->enum('status', ['pending', 'accepted', 'returned', 'cancelled'])
                ->default('pending');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peminjaman_data');
    }
};
