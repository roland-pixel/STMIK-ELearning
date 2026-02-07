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
        Schema::create('penilaians', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('kelas_id')->constrained('kelases')->onDelete('cascade');
            $table->string('judul');
            $table->text('instruksi')->nullable();
            $table->enum('kategori', ['tugas', 'uts', 'uas'])->default('tugas');
            $table->enum('mode_penilaian', ['online', 'manual'])->default('online');
            $table->dateTime('tenggat_waktu')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penilaians');
    }
};
