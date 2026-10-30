<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Revisi setelah wawancara wakil kepala sekolah:
 * - sekolah tidak memakai RPS, jadi tabel rancangan pengajaran dihapus
 * - penilaian harian dikelompokkan per bab (maksimal 5 bab)
 * - riwayat perubahan jadwal, susulan per siswa, dan buka akses unggah 24 jam
 * - notifikasi di dalam aplikasi (sekolah tidak memakai email)
 * - nilai ekstrakurikuler (predikat A-D + deskripsi)
 * - satu orang tua bisa memiliki lebih dari satu anak
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('lesson_meetings');
        Schema::dropIfExists('lesson_plans');

        Schema::table('assessments', function (Blueprint $table) {
            $table->unsignedTinyInteger('bab')->nullable()->after('jenis');
            $table->foreignId('dibuat_oleh')->nullable()->after('skor_maks')->constrained('users')->nullOnDelete();
        });

        Schema::table('assessment_scores', function (Blueprint $table) {
            $table->boolean('terlambat')->default(false)->after('nilai_konversi');
            $table->unsignedInteger('terlambat_menit')->nullable()->after('terlambat');
        });

        // Riwayat perubahan tanggal penilaian
        Schema::create('assessment_schedule_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->cascadeOnDelete();
            $table->date('tanggal_lama');
            $table->date('tanggal_baru');
            $table->string('alasan');
            $table->foreignId('diubah_oleh')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // Tanggal susulan per siswa
        Schema::create('assessment_makeups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('alasan');
            $table->foreignId('dibuat_oleh')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['assessment_id', 'student_id']);
        });

        // Pengajuan dan pembukaan akses unggah nilai setelah batas
        Schema::create('upload_unlocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->cascadeOnDelete();
            $table->foreignId('diajukan_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->string('alasan');
            $table->string('status')->default('menunggu'); // menunggu, dibuka, ditolak
            $table->foreignId('diputuskan_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('dibuka_at')->nullable();
            $table->timestamp('berakhir_at')->nullable();
            $table->timestamps();
        });

        // Notifikasi di dalam aplikasi
        Schema::create('notifikasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('jenis')->default('info'); // jadwal, perubahan, susulan, nilai, akses, pengingat
            $table->string('judul');
            $table->text('pesan');
            $table->string('url')->nullable();
            $table->timestamp('dibaca_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'dibaca_at']);
        });

        // Ekstrakurikuler dan nilainya
        Schema::create('ekstrakurikuler', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->foreignId('pembina_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('ekskul_anggota', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ekstrakurikuler_id')->constrained('ekstrakurikuler')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('semester');
            $table->string('predikat', 1)->nullable(); // A, B, C, D
            $table->text('deskripsi')->nullable();
            $table->timestamps();
            $table->unique(['ekstrakurikuler_id', 'student_id', 'semester']);
        });

        // Orang tua dengan lebih dari satu anak
        Schema::create('parent_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['parent_id', 'student_id']);
        });

        // Pindahkan relasi lama (users.parent_of_id) ke tabel baru
        DB::table('users')->whereNotNull('parent_of_id')->get(['id', 'parent_of_id'])
            ->each(fn ($u) => DB::table('parent_student')->insert([
                'parent_id' => $u->id,
                'student_id' => $u->parent_of_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_student');
        Schema::dropIfExists('ekskul_anggota');
        Schema::dropIfExists('ekstrakurikuler');
        Schema::dropIfExists('notifikasi');
        Schema::dropIfExists('upload_unlocks');
        Schema::dropIfExists('assessment_makeups');
        Schema::dropIfExists('assessment_schedule_changes');

        Schema::table('assessment_scores', function (Blueprint $table) {
            $table->dropColumn(['terlambat', 'terlambat_menit']);
        });
        Schema::table('assessments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dibuat_oleh');
            $table->dropColumn('bab');
        });
    }
};
