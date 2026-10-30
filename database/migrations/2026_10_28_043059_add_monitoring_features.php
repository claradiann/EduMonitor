<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pengaturan sekolah (batas unggah nilai, semester aktif)
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('value')->nullable();
            $table->timestamps();
        });

        // Rancangan pengajaran (RPS) per guru-mapel-kelas per semester
        Schema::create('lesson_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_teacher_id')->constrained('subject_teacher')->onDelete('cascade');
            $table->string('semester');
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->timestamps();
        });

        // Rencana pertemuan dari jadwal yang diunggah + realisasinya
        Schema::create('lesson_meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_plan_id')->constrained('lesson_plans')->onDelete('cascade');
            $table->unsignedInteger('pertemuan_ke');
            $table->date('tanggal');
            $table->string('materi');
            $table->boolean('terlaksana')->default(false);
            $table->timestamp('terlaksana_at')->nullable();
            $table->timestamps();
        });

        // Rencana penilaian (tugas, kuis, UH, UTS, UAS) beserta tanggalnya
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_teacher_id')->constrained('subject_teacher')->onDelete('cascade');
            $table->string('semester');
            $table->string('jenis'); // tugas, kuis, uh, uts, uas
            $table->string('judul');
            $table->date('tanggal');
            $table->decimal('skor_maks', 8, 2)->default(100);
            $table->timestamp('pengingat_terkirim_at')->nullable();
            $table->timestamps();
        });

        // Skor siswa per penilaian beserta hasil konversinya (0-100)
        Schema::create('assessment_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->decimal('skor', 8, 2);
            $table->decimal('nilai_konversi', 5, 2);
            $table->timestamps();
            $table->unique(['assessment_id', 'student_id']);
        });

        // Pelaporan insidental oleh siswa
        Schema::create('incident_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('subject_teacher_id')->constrained('subject_teacher')->onDelete('cascade');
            $table->date('tanggal');
            $table->string('jenis');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // Nilai harian (rata-rata seluruh tugas, kuis, dan UH hasil konversi)
        Schema::table('grades', function (Blueprint $table) {
            $table->decimal('nilai_harian', 5, 2)->nullable()->after('nilai_uh');
        });

        // Status baca laporan oleh orang tua
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('report_read_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('report_read_at');
        });
        Schema::table('grades', function (Blueprint $table) {
            $table->dropColumn('nilai_harian');
        });
        Schema::dropIfExists('incident_reports');
        Schema::dropIfExists('assessment_scores');
        Schema::dropIfExists('assessments');
        Schema::dropIfExists('lesson_meetings');
        Schema::dropIfExists('lesson_plans');
        Schema::dropIfExists('settings');
    }
};
