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
        Schema::table('siswa', function (Blueprint $table) {
            $table->foreignId('kelas_id')
                ->nullable()
                ->constrained('kelas')
                ->nullOnDelete()
                ->after('user_id');

            $table->foreignId('jurusan_id')
                ->nullable()
                ->constrained('jurusan')
                ->nullOnDelete()
                ->after('kelas_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kelas_id');
            $table->dropConstrainedForeignId('jurusan_id');
        });
    }
};
