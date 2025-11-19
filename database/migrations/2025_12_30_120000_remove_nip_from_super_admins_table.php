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
        if (Schema::hasColumn('super_admins', 'nip')) {
            Schema::table('super_admins', function (Blueprint $table) {
                $table->dropColumn('nip');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('super_admins', 'nip')) {
            Schema::table('super_admins', function (Blueprint $table) {
                $table->string('nip')->unique();
            });
        }
    }
};
