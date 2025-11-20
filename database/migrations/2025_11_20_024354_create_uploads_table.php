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
        Schema::create('uploads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade'); // jika user dihapus, file-nya juga dihapus

            $table->string('nama_file');          // nama asli file saat diupload
            $table->string('path');               // lokasi penyimpanan di storage/public
            $table->string('tipe');               // jenis file (image, video, document)
            $table->string('ekstensi', 10);       // ekstensi file (jpg, mp4, pdf, dll)
            $table->unsignedBigInteger('ukuran'); // ukuran file dalam byte
            $table->string('mime_type')->nullable(); // MIME type (misal: image/png)
            $table->text('keterangan')->nullable(); // deskripsi atau catatan tambahan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uploads');
    }
};
