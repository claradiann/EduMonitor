<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Laporan perkembangan belajar yang dikirim guru ke orang tua, beserta status bacanya.
 * Isi nilai disimpan sebagai salinan (snapshot) saat laporan dikirim.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_perkembangan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_teacher_id')->constrained('subject_teacher')->cascadeOnDelete();
            $table->string('semester');
            $table->unsignedTinyInteger('bab')->nullable(); // null = ringkasan semua bab
            $table->string('judul');
            $table->text('pesan')->nullable();
            $table->foreignId('dikirim_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('laporan_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_perkembangan_id')->constrained('laporan_perkembangan')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->json('isi');
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->unique(['laporan_perkembangan_id', 'student_id']);
        });

        Schema::create('laporan_dibaca', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_siswa_id')->constrained('laporan_siswa')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('dibaca_at');
            $table->unique(['laporan_siswa_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_dibaca');
        Schema::dropIfExists('laporan_siswa');
        Schema::dropIfExists('laporan_perkembangan');
    }
};