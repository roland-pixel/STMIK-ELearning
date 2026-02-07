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
        Schema::create('pertanyaans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penilaian_id')->constrained('penilaians')->onDelete('cascade');
            $table->integer('nomor_urut');
            $table->text('text_pertanyaan');
            $table->enum('jenis_pertanyaan', ['essai', 'pilihan_ganda', 'upload_file']);
            $table->integer('bobot_soal')->default(0);
            $table->timestamps();

            $table->unique(['penilaian_id', 'nomor_urut']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pertanyaans');
    }
};
