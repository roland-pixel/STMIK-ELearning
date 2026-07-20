<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mahasiswas', function (Blueprint $table) {
            // Ditambahkan setelah kolom status, nullable karena mahasiswa aktif belum punya tanggal lulus
            $table->date('tanggal_masuk')->nullable()->after('status_masuk');
            $table->date('tanggal_lulus')->nullable()->after('tanggal_masuk');
        });
    }

    public function down(): void
    {
        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->dropColumn(['tanggal_masuk', 'tanggal_lulus']);
        });
    }
};
