<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peminjaman_data', function (Blueprint $table) {
            $table->foreign('siswa_id')
                ->references('id')
                ->on('siswa')
                ->cascadeOnDelete();

            $table->foreign('guru_id')
                ->references('id')
                ->on('guru')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('peminjaman_data', function (Blueprint $table) {
            $table->dropForeign(['siswa_id']);
            $table->dropForeign(['guru_id']);
        });
    }
};

