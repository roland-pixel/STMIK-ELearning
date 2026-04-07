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
        Schema::table('mata_kuliahs', function (Blueprint $table) {
            // Menambah kolom baru setelah kolom 'jenis_mk'
            $table->enum('kategori_mk', ['KPP', 'KIT', 'KAB', 'KPB', 'KBB'])
                ->after('jenis_mk') // Biar rapi posisinya di database
                ->nullable();       // Opsional: boleh kosong atau tidak
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mata_kuliahs', function (Blueprint $table) {
            // Menghapus kolom jika migrasi di-rollback
            $table->dropColumn('kategori_mk');
        });
    }
};
