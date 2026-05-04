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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            // siapa yang melakukan
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');

            // info aksi
            $table->string('action'); // created|updated|deleted
            $table->string('subject_type'); // App\Models\Kelas, App\Models\Mahasiswa, dst
            $table->unsignedBigInteger('subject_id'); // id dari data yang berubah

            // snapshot data (opsional tapi berguna)
            $table->json('before')->nullable();
            $table->json('after')->nullable();

            // metadata request (berguna untuk audit)
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            $table->index(['subject_type', 'subject_id']);
            $table->index(['user_id', 'action']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
