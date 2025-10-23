<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('buku', function (Blueprint $table) {
            $table->foreignId('penerbit_id')
                ->nullable()
                ->after('kategori_id')
                ->constrained('penerbit')
                ->nullOnDelete();
            $table->string('cover_depan')->nullable()->after('deskripsi');
            $table->string('cover_belakang')->nullable()->after('cover_depan');
        });

        DB::table('buku')
            ->select(['id', 'penerbit'])
            ->orderBy('id')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    if (! $row->penerbit) {
                        continue;
                    }

                    $penerbitId = DB::table('penerbit')
                        ->where('nama_penerbit', $row->penerbit)
                        ->value('id');

                    if ($penerbitId) {
                        DB::table('buku')
                            ->where('id', $row->id)
                            ->update(['penerbit_id' => $penerbitId]);
                    }
                }
            });

        Schema::table('buku', function (Blueprint $table) {
            $table->dropColumn(['penerbit', 'tahun_terbit']);
        });
    }

    public function down(): void
    {
        Schema::table('buku', function (Blueprint $table) {
            $table->string('penerbit')->nullable()->after('kategori_id');
            $table->year('tahun_terbit')->nullable()->after('tanggal_terbit');
        });

        DB::table('buku')
            ->select(['id', 'penerbit_id'])
            ->whereNotNull('penerbit_id')
            ->orderBy('id')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    $namaPenerbit = DB::table('penerbit')
                        ->where('id', $row->penerbit_id)
                        ->value('nama_penerbit');

                    if ($namaPenerbit) {
                        DB::table('buku')
                            ->where('id', $row->id)
                            ->update(['penerbit' => $namaPenerbit]);
                    }
                }
            });

        Schema::table('buku', function (Blueprint $table) {
            $table->dropForeign(['penerbit_id']);
            $table->dropColumn(['penerbit_id', 'cover_depan', 'cover_belakang']);
        });
    }
};
