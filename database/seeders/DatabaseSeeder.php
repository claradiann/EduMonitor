<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Kelas;
use App\Models\Subject;
use App\Models\SubjectTeacher;
use App\Models\Grade;
use App\Models\TeacherNote;
use App\Models\EvaluationIndicator;
use App\Models\EvaluationResponse;
use App\Models\EvaluationScore;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Kelas
        $kelas7A = Kelas::create(['nama_kelas' => 'VII-A']);
        $kelas7B = Kelas::create(['nama_kelas' => 'VII-B']);
        $kelas8A = Kelas::create(['nama_kelas' => 'VIII-A']);
        $kelas9A = Kelas::create(['nama_kelas' => 'IX-A']);

        // 2. Seed Subjects (Mata Pelajaran)
        $subjectsData = [
            ['nama_mapel' => 'Bahasa Indonesia', 'kode_mapel' => 'B INDO'],
            ['nama_mapel' => 'Bahasa Inggris', 'kode_mapel' => 'B ING'],
            ['nama_mapel' => 'Bahasa Jawa', 'kode_mapel' => 'B JAWA'],
            ['nama_mapel' => 'Matematika', 'kode_mapel' => 'MTK'],
            ['nama_mapel' => 'IPA', 'kode_mapel' => 'IPA'],
            ['nama_mapel' => 'IPS', 'kode_mapel' => 'IPS'],
            ['nama_mapel' => 'TIK', 'kode_mapel' => 'TIK'],
            ['nama_mapel' => 'PJOK', 'kode_mapel' => 'PJOK'],
            ['nama_mapel' => 'Seni Musik', 'kode_mapel' => 'MUSIK'],
        ];

        $subjects = [];
        foreach ($subjectsData as $sub) {
            $subjects[$sub['kode_mapel']] = Subject::create($sub);
        }

        // 3. Seed Users
        // Admin
        User::create([
            'name' => 'Admin Sistem',
            'email' => 'admin@edumonitor.sch.id',
            'username' => 'admin',
            'role' => 'admin',
            'password' => Hash::make('password'),
        ]);

        // Teachers (Guru) - 1 guru = 1 mapel saja, boleh ngajar di banyak kelas
        $thomas = User::create([
            'name' => 'Pak Thomas', 'email' => 'thomas@edumonitor.sch.id', 'username' => 'thomas',
            'role' => 'guru', 'nip' => 'NIP19820512', 'password' => Hash::make('password'),
        ]);
        $ndari = User::create([
            'name' => 'Bu Ndari', 'email' => 'ndari@edumonitor.sch.id', 'username' => 'ndari',
            'role' => 'guru', 'nip' => 'NIP19850918', 'password' => Hash::make('password'),
        ]);
        $retno = User::create([
            'name' => 'Bu Retno', 'email' => 'retno@edumonitor.sch.id', 'username' => 'retno',
            'role' => 'guru', 'nip' => 'NIP19890422', 'password' => Hash::make('password'),
        ]);
        $ahmad = User::create([
            'name' => 'Pak Ahmad', 'email' => 'ahmad@edumonitor.sch.id', 'username' => 'ahmad',
            'role' => 'guru', 'nip' => 'NIP19770802', 'password' => Hash::make('password'),
        ]);
        $siti = User::create([
            'name' => 'Bu Siti', 'email' => 'siti@edumonitor.sch.id', 'username' => 'siti',
            'role' => 'guru', 'nip' => 'NIP19840711', 'password' => Hash::make('password'),
        ]);
        $bambang = User::create([
            'name' => 'Pak Bambang', 'email' => 'bambang@edumonitor.sch.id', 'username' => 'bambang',
            'role' => 'guru', 'nip' => 'NIP19790325', 'password' => Hash::make('password'),
        ]);
        $rini = User::create([
            'name' => 'Bu Rini', 'email' => 'rini@edumonitor.sch.id', 'username' => 'rini',
            'role' => 'guru', 'nip' => 'NIP19861203', 'password' => Hash::make('password'),
        ]);
        $hadi = User::create([
            'name' => 'Pak Hadi', 'email' => 'hadi@edumonitor.sch.id', 'username' => 'hadi',
            'role' => 'guru', 'nip' => 'NIP19751119', 'password' => Hash::make('password'),
        ]);
        $wati = User::create([
            'name' => 'Bu Wati', 'email' => 'wati@edumonitor.sch.id', 'username' => 'wati',
            'role' => 'guru', 'nip' => 'NIP19830804', 'password' => Hash::make('password'),
        ]);

        // Students (Siswa)
        $clara = User::create([
            'name' => 'Clara Dian',
            'email' => 'clara@edumonitor.sch.id',
            'username' => 'clara',
            'role' => 'siswa',
            'nisn' => 'S260315',
            'kelas_id' => $kelas7A->id,
            'password' => Hash::make('password'),
        ]);

        $andi = User::create([
            'name' => 'Andi Saputra',
            'email' => 'andi@edumonitor.sch.id',
            'username' => 'andi',
            'role' => 'siswa',
            'nisn' => 'S260316',
            'kelas_id' => $kelas7A->id,
            'password' => Hash::make('password'),
        ]);

        $budis = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budis@edumonitor.sch.id',
            'username' => 'budis',
            'role' => 'siswa',
            'nisn' => 'S260317',
            'kelas_id' => $kelas7A->id,
            'password' => Hash::make('password'),
        ]);

        $chandra = User::create([
            'name' => 'Chandra Wijaya',
            'email' => 'chandra@edumonitor.sch.id',
            'username' => 'chandra',
            'role' => 'siswa',
            'nisn' => 'S260318',
            'kelas_id' => $kelas7A->id,
            'password' => Hash::make('password'),
        ]);

        // Parents (Orang Tua)
        User::create([
            'name' => 'Budi Dian',
            'email' => 'budi@edumonitor.sch.id',
            'username' => 'budi',
            'role' => 'orang_tua',
            'parent_of_id' => $clara->id,
            'password' => Hash::make('password'),
        ]);

        // 4. Seed Subject Teacher (Mapping) - tiap guru cuma 1 mapel, boleh banyak kelas
        $mappings = [
            // Thomas -> Matematika (kelas 7A & 7B)
            ['teacher_id' => $thomas->id, 'subject_id' => $subjects['MTK']->id, 'kelas_id' => $kelas7A->id],
            ['teacher_id' => $thomas->id, 'subject_id' => $subjects['MTK']->id, 'kelas_id' => $kelas7B->id],

            // Ndari -> IPA (kelas 7A, 8A)
            ['teacher_id' => $ndari->id, 'subject_id' => $subjects['IPA']->id, 'kelas_id' => $kelas7A->id],
            ['teacher_id' => $ndari->id, 'subject_id' => $subjects['IPA']->id, 'kelas_id' => $kelas8A->id],

            // Retno -> Bahasa Inggris (kelas 7A, 9A)
            ['teacher_id' => $retno->id, 'subject_id' => $subjects['B ING']->id, 'kelas_id' => $kelas7A->id],
            ['teacher_id' => $retno->id, 'subject_id' => $subjects['B ING']->id, 'kelas_id' => $kelas9A->id],

            // Ahmad -> Bahasa Indonesia (kelas 7A, 7B)
            ['teacher_id' => $ahmad->id, 'subject_id' => $subjects['B INDO']->id, 'kelas_id' => $kelas7A->id],
            ['teacher_id' => $ahmad->id, 'subject_id' => $subjects['B INDO']->id, 'kelas_id' => $kelas7B->id],

            // Siti -> Bahasa Jawa (kelas 7A)
            ['teacher_id' => $siti->id, 'subject_id' => $subjects['B JAWA']->id, 'kelas_id' => $kelas7A->id],

            // Bambang -> IPS (kelas 7A, 8A, 9A)
            ['teacher_id' => $bambang->id, 'subject_id' => $subjects['IPS']->id, 'kelas_id' => $kelas7A->id],
            ['teacher_id' => $bambang->id, 'subject_id' => $subjects['IPS']->id, 'kelas_id' => $kelas8A->id],

            // Rini -> TIK (kelas 7A)
            ['teacher_id' => $rini->id, 'subject_id' => $subjects['TIK']->id, 'kelas_id' => $kelas7A->id],

            // Hadi -> PJOK (kelas 7A, 7B)
            ['teacher_id' => $hadi->id, 'subject_id' => $subjects['PJOK']->id, 'kelas_id' => $kelas7A->id],
            ['teacher_id' => $hadi->id, 'subject_id' => $subjects['PJOK']->id, 'kelas_id' => $kelas7B->id],

            // Wati -> Seni Musik (kelas 7A)
            ['teacher_id' => $wati->id, 'subject_id' => $subjects['MUSIK']->id, 'kelas_id' => $kelas7A->id],
        ];

        $subTeachers = [];
        foreach ($mappings as $map) {
            $key = $map['kelas_id'] . '-' . $map['subject_id'];
            $subTeachers[$key] = SubjectTeacher::create($map);
        }

        // 5. Seed Grades for Clara Dian
        // Gasal 2025/2026
        $claraGradesGasal = [
            'B INDO' => [88, 90, 85, 87],
            'B ING' => [85, 88, 86, 90],
            'B JAWA' => [80, 82, 85, 83],
            'MTK' => [75, 78, 80, 78],
            'IPA' => [80, 82, 85, 84],
            'IPS' => [82, 84, 80, 85],
            'TIK' => [90, 88, 92, 90],
            'PJOK' => [85, 85, 86, 88],
            'MUSIK' => [88, 85, 90, 87],
        ];

        foreach ($claraGradesGasal as $kode => $vals) {
            Grade::create([
                'student_id' => $clara->id,
                'subject_id' => $subjects[$kode]->id,
                'nilai_tugas' => $vals[0],
                'nilai_uh' => $vals[1],
                'nilai_uts' => $vals[2],
                'nilai_uas' => $vals[3],
                'semester' => 'Gasal 2025/2026',
            ]);
        }

        // Genap 2025/2026 (Active Semester)
        $claraGradesGenap = [
            'B INDO' => [90, 88, 92, 92],
            'B ING' => [92, 94, 90, 95],
            'B JAWA' => [84, 85, 88, 85],
            'MTK' => [80, 78, 82, 85],
            'IPA' => [88, 86, 90, 92],
            'IPS' => [86, 88, 85, 90],
            'TIK' => [92, 90, 95, 96],
            'PJOK' => [88, 88, 85, 89],
            'MUSIK' => [92, 90, 95, 92],
        ];

        foreach ($claraGradesGenap as $kode => $vals) {
            Grade::create([
                'student_id' => $clara->id,
                'subject_id' => $subjects[$kode]->id,
                'nilai_tugas' => $vals[0],
                'nilai_uh' => $vals[1],
                'nilai_uts' => $vals[2],
                'nilai_uas' => $vals[3],
                'semester' => 'Genap 2025/2026',
            ]);
        }

        // Also seed some grades for other students to generate class/average stats if needed
        $otherStudents = [$andi, $budis, $chandra];
        foreach ($otherStudents as $stud) {
            foreach ($subjects as $sub) {
                Grade::create([
                    'student_id' => $stud->id,
                    'subject_id' => $sub->id,
                    'nilai_tugas' => rand(70, 95),
                    'nilai_uh' => rand(70, 95),
                    'nilai_uts' => rand(70, 95),
                    'nilai_uas' => rand(70, 95),
                    'semester' => 'Genap 2025/2026',
                ]);
            }
        }

        // 6. Seed Teacher Notes for Clara Dian
        TeacherNote::create([
            'student_id' => $clara->id,
            'teacher_id' => $thomas->id,
            'subject_id' => $subjects['MTK']->id,
            'catatan' => 'Clara menunjukkan pemahaman konsep yang baik, perlu lebih teliti pada perhitungan rumus.',
        ]);

        TeacherNote::create([
            'student_id' => $clara->id,
            'teacher_id' => $ndari->id,
            'subject_id' => $subjects['IPA']->id,
            'catatan' => 'Clara menunjukkan pemahaman konsep IPA yang baik serta aktif dalam mengikuti pembelajaran.',
        ]);

        TeacherNote::create([
            'student_id' => $clara->id,
            'teacher_id' => $retno->id,
            'subject_id' => $subjects['B ING']->id,
            'catatan' => 'Clara sangat aktif dalam berbicara bahasa Inggris dan menunjukkan kemajuan pesat dalam kosa kata.',
        ]);

        // 7. Seed Evaluation Indicators (Standardized)
        $indicators = [
            [
                'indicator_name' => 'Kejelasan Penyampaian',
                'description' => 'Guru menjelaskan materi dengan bahasa yang mudah dipahami dan memberikan contoh yang jelas.',
            ],
            [
                'indicator_name' => 'Kesesuaian Metode Mengajar',
                'description' => 'Guru menggunakan variasi metode pembelajaran atau media mengajar yang menarik agar tidak membosankan.',
            ],
            [
                'indicator_name' => 'Sikap & Dukungan Guru',
                'description' => 'Guru bersikap ramah, peduli, sabar, dan memberikan motivasi yang baik kepada siswa.',
            ],
            [
                'indicator_name' => 'Keadilan Penilaian',
                'description' => 'Guru bersikap objektif dan transparan dalam memberikan penilaian tugas maupun ujian.',
            ],
            [
                'indicator_name' => 'Kedisiplinan & Kehadiran',
                'description' => 'Guru hadir tepat waktu di kelas dan memanfaatkan waktu belajar dengan efektif.',
            ],
        ];

        $indModels = [];
        foreach ($indicators as $ind) {
            $indModels[] = EvaluationIndicator::create($ind);
        }

        // 8. Seed Evaluation Responses (Feedback from OTHER students to make dashboard rich)
        $commentsAndi = [
            'MTK' => 'Pak Thomas mengajarnya seru sekali, tapi kadang jalannya penjelasan rumus terlalu cepat.',
            'IPA' => 'Bu Ndari menjelaskan IPA dengan praktikum dan eksperimen yang seru!',
        ];
        $commentsBudis = [
            'MTK' => 'Pembahasan latihan soal dari Pak Thomas sangat membantu persiapan ujian.',
            'IPA' => 'Suka sekali dengan tugas proyek IPA kemarin, kami membuat miniatur sel.',
        ];
        $commentsChandra = [
            'MTK' => 'Kadang tugasnya agak banyak, tapi Pak Thomas baik dan sabar menjelaskan.',
            'IPA' => 'Bu Ndari selalu memberikan kuis interaktif di akhir kelas yang menyenangkan.',
        ];

        $feedbacks = [
            ['student' => $andi, 'data' => $commentsAndi],
            ['student' => $budis, 'data' => $commentsBudis],
            ['student' => $chandra, 'data' => $commentsChandra],
        ];

        foreach ($feedbacks as $fb) {
            $student = $fb['student'];
            foreach ($fb['data'] as $subCode => $comment) {
                $subject = $subjects[$subCode];
                $st = SubjectTeacher::where('subject_id', $subject->id)
                    ->where('kelas_id', $student->kelas_id)
                    ->first();

                if ($st) {
                    $response = EvaluationResponse::create([
                        'student_id' => $student->id,
                        'subject_teacher_id' => $st->id,
                        'comments' => $comment,
                        'semester' => 'Genap 2025/2026',
                    ]);

                    foreach ($indModels as $ind) {
                        EvaluationScore::create([
                            'evaluation_response_id' => $response->id,
                            'indicator_id' => $ind->id,
                            'score' => rand(4, 5),
                        ]);
                    }
                }
            }
        }
    }
}