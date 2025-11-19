<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('guru') && Schema::hasTable('admin_perpus')) {
            $rows = DB::table('guru')->get();

            foreach ($rows as $row) {
                if (! DB::table('admin_perpus')->where('user_id', $row->user_id)->exists()) {
                    DB::table('admin_perpus')->insert([
                        'user_id' => $row->user_id,
                        'nip' => $row->nip,
                        'mata_pelajaran' => $row->mata_pelajaran,
                        'jenis_kelamin' => $row->jenis_kelamin,
                        'alamat' => $row->alamat,
                        'foto' => $row->foto,
                        'created_at' => $row->created_at,
                        'updated_at' => $row->updated_at,
                    ]);
                }
            }
        }

        if (Schema::hasTable('petugas') && Schema::hasTable('super_admins')) {
            $rows = DB::table('petugas')->get();

            foreach ($rows as $row) {
                if (! DB::table('super_admins')->where('user_id', $row->user_id)->exists()) {
                    DB::table('super_admins')->insert([
                        'user_id' => $row->user_id,
                        'alamat' => $row->alamat,
                        'jenis_kelamin' => $row->jenis_kelamin,
                        'foto' => $row->foto,
                        'created_at' => $row->created_at,
                        'updated_at' => $row->updated_at,
                    ]);
                }
            }
        }

        if (Schema::hasColumn('peminjaman_data', 'guru_id') && Schema::hasColumn('peminjaman_data', 'admin_perpus_id')) {
            DB::table('peminjaman_data')
                ->whereNull('admin_perpus_id')
                ->whereNotNull('guru_id')
                ->update(['admin_perpus_id' => DB::raw('guru_id')]);

            Schema::table('peminjaman_data', function (Blueprint $table) {
                if (Schema::hasColumn('peminjaman_data', 'guru_id')) {
                    $table->dropForeign(['guru_id']);
                    $table->dropColumn('guru_id');
                }
            });
        }

        if (Schema::hasColumn('peminjaman_penalties', 'guru_id') && Schema::hasColumn('peminjaman_penalties', 'admin_perpus_id')) {
            DB::table('peminjaman_penalties')
                ->whereNull('admin_perpus_id')
                ->whereNotNull('guru_id')
                ->update(['admin_perpus_id' => DB::raw('guru_id')]);

            Schema::table('peminjaman_penalties', function (Blueprint $table) {
                if (Schema::hasColumn('peminjaman_penalties', 'guru_id')) {
                    $table->dropForeign(['guru_id']);
                    $table->dropColumn('guru_id');
                }
            });
        }
    }

    public function down(): void
    {
        //
    }
};
