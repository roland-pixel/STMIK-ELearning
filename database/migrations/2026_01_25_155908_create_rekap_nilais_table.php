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
        Schema::create('rekap_nilais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->onDelete('cascade');
            $table->foreignId('semester_id')->constrained('semesters')->onDelete('cascade');
            $table->foreignId('mata_kuliah_id')->constrained('mata_kuliahs')->onDelete('cascade');
            $table->foreignId('kelas_id')->constrained('kelases')->onDelete('cascade');
            $table->decimal('total_tugas', 5, 2);
            $table->decimal('total_uts', 5, 2);
            $table->decimal('total_uas', 5, 2);
            $table->decimal('nilai_akhir_angka', 5, 2);
            $table->string('nilai_huruf', 2);
            $table->decimal('nilai_indeks', 5, 2);
            $table->timestamps();

            $table->unique(['mahasiswa_id', 'kelas_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekap_nilais');
    }
};
