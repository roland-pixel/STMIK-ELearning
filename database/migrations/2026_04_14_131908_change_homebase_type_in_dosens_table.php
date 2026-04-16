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
        Schema::table('dosens', function (Blueprint $table) {
            Schema::table('dosens', function (Blueprint $table) {
                // Kita ubah dari enum ke string agar fleksibel
                $table->string('homebase')->nullable()->change();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dosens', function (Blueprint $table) {
            $table->enum('homebase', ['Sistem Informasi', 'Teknik Informatika', 'Komputerisasi Akuntansi', 'Manajemen Informatika'])->nullable()->change();
        });
    }
};
