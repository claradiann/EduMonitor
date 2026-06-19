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
        // 1. Kelas Table
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kelas');
            $table->timestamps();
        });

        // 2. Subjects Table
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('nama_mapel');
            $table->string('kode_mapel');
            $table->timestamps();
        });

        // 3. Subject Teacher (Mapping Guru, Mapel, dan Kelas)
        Schema::create('subject_teacher', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
            $table->timestamps();
        });

        // 4. Grades Table (Nilai)
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->integer('nilai_tugas')->default(0);
            $table->integer('nilai_uh')->default(0);
            $table->integer('nilai_uts')->default(0);
            $table->integer('nilai_uas')->default(0);
            $table->string('semester');
            $table->timestamps();
        });

        // 5. Teacher Notes Table (Catatan Guru)
        Schema::create('teacher_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->text('catatan');
            $table->timestamps();
        });

        // 6. Evaluation Indicators Table
        Schema::create('evaluation_indicators', function (Blueprint $table) {
            $table->id();
            $table->string('indicator_name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 7. Evaluation Responses Table
        Schema::create('evaluation_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('subject_teacher_id')->constrained('subject_teacher')->onDelete('cascade');
            $table->text('comments')->nullable();
            $table->string('semester');
            $table->timestamps();
        });

        // 8. Evaluation Scores Table
        Schema::create('evaluation_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_response_id')->constrained('evaluation_responses')->onDelete('cascade');
            $table->foreignId('indicator_id')->constrained('evaluation_indicators')->onDelete('cascade');
            $table->integer('score'); // 1 to 5 scale
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluation_scores');
        Schema::dropIfExists('evaluation_responses');
        Schema::dropIfExists('evaluation_indicators');
        Schema::dropIfExists('teacher_notes');
        Schema::dropIfExists('grades');
        Schema::dropIfExists('subject_teacher');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('kelas');
    }
};
