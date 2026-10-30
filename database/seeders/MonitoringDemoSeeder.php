<?php

namespace Database\Seeders;

use App\Models\Assessment;
use App\Models\AssessmentMakeup;
use App\Models\AssessmentScheduleChange;
use App\Models\EkskulAnggota;
use App\Models\Ekstrakurikuler;
use App\Models\IncidentReport;
use App\Models\Kelas;
use App\Models\LaporanDibaca;
use App\Models\LaporanPerkembangan;
use App\Models\LaporanSiswa;
use App\Models\Notifikasi;
use App\Models\Setting;
use App\Models\SubjectTeacher;
use App\Models\UploadUnlock;
use App\Models\User;
use App\Services\NilaiService;
use App\Services\NilaiService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Data contoh untuk demo. Tanggal dibuat relatif terhadap hari ini,
 * sehingga keempat status penilaian selalu tampil kapan pun demo dilakukan.
 */
class MonitoringDemoSeeder extends Seeder
{
    public function run(): void
    {
        Setting::set('batas_unggah_hari', 7);
        Setting::set('durasi_buka_akses_jam', 24);
        Setting::set('semester_aktif', 'Genap 2025/2026');
        Setting::set('batas_nilai_akhir', today()->addDays(45)->toDateString());
        $semester = Setting::semesterAktif();

        $admin = User::where('role', 'admin')->first();
        $thomas = User::where('username', 'thomas')->first();
        $ndari = User::where('username', 'ndari')->first();
        $clara = User::where('username', 'clara')->first();
        $budi = User::where('username', 'budi')->first();
        if (!$thomas || !$ndari || !$clara || !$budi) {
            return;
        }

        // Orang tua dengan dua anak (kakak-adik beda angkatan)
        $kelas8 = Kelas::where('nama_kelas', 'VIII-Tollerance')->first();
        $dewi = User::create([
            'name' => 'Dewi Dian', 'email' => 'dewi@edumonitor.sch.id', 'username' => 'dewi',
            'role' => 'siswa', 'nisn' => 'S250201', 'kelas_id' => $kelas8?->id, 'password' => Hash::make('password'),
        ]);
        $budi->children()->syncWithoutDetaching([$clara->id, $dewi->id]);

        $mtk = SubjectTeacher::where('teacher_id', $thomas->id)->where('kelas_id', $clara->kelas_id)->first();
        $ipa = SubjectTeacher::where('teacher_id', $ndari->id)->where('kelas_id', $clara->kelas_id)->first();
        $ipa8 = SubjectTeacher::where('teacher_id', $ndari->id)->where('kelas_id', $kelas8?->id)->first();

        // [jenis, bab, judul, hari dari sekarang, skor maks, sudah dinilai?]
        $jadwal = [
            ['tugas', 1, 'Tugas 1', -40, 50, true],
            ['kuis', 1, 'Kuis 1', -33, 20, true],
            ['uh', 1, 'Ulangan Harian Bab 1', -26, 100, true],
            ['tugas', 2, 'Tugas 2', -19, 100, true],
            ['kuis', 2, 'Kuis 2', -12, 20, false],   // melewati batas unggah
            ['tugas', 2, 'Tugas 3', -3, 100, false], // menunggu nilai
            ['kuis', 3, 'Kuis 3', 2, 20, false],     // dijadwalkan
        ];

        foreach (array_filter([$mtk, $ipa, $ipa8]) as $st) {
            $guru = $st->teacher_id;
            foreach ($jadwal as $i => [$jenis, $bab, $judul, $hari, $maks, $dinilai]) {
                if ($st === $ipa8 && $i > 2) {
                    break;
                }
                $a = Assessment::create([
                    'subject_teacher_id' => $st->id, 'semester' => $semester, 'jenis' => $jenis, 'bab' => $bab,
                    'judul' => $judul, 'tanggal' => today()->addDays($hari), 'skor_maks' => $maks, 'dibuat_oleh' => $guru,
                ]);
                if ($dinilai) {
                    foreach ($st->students() as $k => $s) {
                        NilaiService::simpanSkor($a, $s->id, round($maks * (0.68 + (($k * 7 + $s->id + $i * 3) % 28) / 100), 1));
                    }
                    
                // anggap nilai diunggah 2 hari setelah penilaian (tepat waktu).
                // Tanda terlambat dihapus karena data contoh ini dibuat hari ini, bukan saat penilaian.
                $a->scores()->update([
                    'created_at' => $a->tanggal->copy()->addDays(2), 'updated_at' => $a->tanggal->copy()->addDays(2),
                    'terlambat' => false, 'terlambat_menit' => null,
                    ]);
                }
            }

            // UTS dari admin, sempat digeser karena bentrok undangan dinas
            $uts = Assessment::create([
                'subject_teacher_id' => $st->id, 'semester' => $semester, 'jenis' => 'uts',
                'judul' => 'UTS ' . $st->subject->nama_mapel, 'tanggal' => today()->addDays(9), 'skor_maks' => 100, 'dibuat_oleh' => $admin->id,
            ]);
            AssessmentScheduleChange::create([
                'assessment_id' => $uts->id, 'tanggal_lama' => today()->addDays(5), 'tanggal_baru' => today()->addDays(9),
                'alasan' => 'Bentrok undangan lomba dari dinas', 'diubah_oleh' => $admin->id,
            ]);
        }

        // Susulan: satu siswa sakit saat Tugas 3 Matematika
        if ($mtk) {
            $tugas3 = Assessment::where('subject_teacher_id', $mtk->id)->where('judul', 'Tugas 3')->first();
            $chandra = User::where('username', 'chandra')->first();
            if ($tugas3 && $chandra) {
                AssessmentMakeup::create([
                    'assessment_id' => $tugas3->id, 'student_id' => $chandra->id,
                    'tanggal' => today()->addDay(), 'alasan' => 'Sakit, ada surat dokter', 'dibuat_oleh' => $thomas->id,
                ]);
            }
        }

        // Pengajuan buka akses dari Bu Ndari untuk Kuis 2 yang melewati batas
        if ($ipa) {
            $kuis2 = Assessment::where('subject_teacher_id', $ipa->id)->where('judul', 'Kuis 2')->first();
            UploadUnlock::create([
                'assessment_id' => $kuis2->id, 'diajukan_oleh' => $ndari->id,
                'alasan' => 'Lembar jawaban baru selesai dikoreksi karena saya mengikuti pelatihan dari dinas.',
            ]);
        }

        // Ekstrakurikuler
        $pramuka = Ekstrakurikuler::create(['nama' => 'Pramuka', 'pembina_id' => $thomas->id]);
        $paduan = Ekstrakurikuler::create(['nama' => 'Paduan Suara', 'pembina_id' => null]);
        foreach (User::where('role', 'siswa')->where('kelas_id', $clara->kelas_id)->get() as $s) {
            EkskulAnggota::create(['ekstrakurikuler_id' => $pramuka->id, 'student_id' => $s->id, 'semester' => $semester]);
        }
        EkskulAnggota::where('ekstrakurikuler_id', $pramuka->id)->where('student_id', User::where('username', 'andi')->value('id'))
            ->update(['predikat' => 'A', 'deskripsi' => 'Aktif dalam kegiatan perkemahan dan mampu memimpin regu.']);
        EkskulAnggota::create(['ekstrakurikuler_id' => $paduan->id, 'student_id' => $clara->id, 'semester' => $semester,
            'predikat' => 'B', 'deskripsi' => 'Rajin berlatih dan mampu menjaga harmoni suara.']);

        if ($mtk) {
            IncidentReport::create([
                'student_id' => $clara->id, 'subject_teacher_id' => $mtk->id, 'tanggal' => today()->subDays(2),
                'jenis' => 'jam_kosong', 'keterangan' => 'Jam ke-3 tidak ada guru dan tidak ada tugas pengganti.',
            ]);
        }

        // Laporan perkembangan dari Pak Thomas (Matematika Bab 1) ke orang tua kelas Clara.
        // Satu orang tua sudah membaca, Pak Budi (orang tua Clara) belum.
        $andi = User::where('username', 'andi')->first();
        $sri = User::create([
            'name' => 'Ibu Sri Lestari', 'email' => 'sri@edumonitor.sch.id', 'username' => 'sri',
            'role' => 'orang_tua', 'password' => Hash::make('password'),
        ]);
        if ($andi) {
            $sri->children()->sync([$andi->id]);
        }
        $laporanMtk = null;
        if ($mtk) {
            $laporanMtk = LaporanPerkembangan::create([
                'subject_teacher_id' => $mtk->id, 'semester' => $semester, 'bab' => 1,
                'judul' => 'Matematika Bab 1', 'dikirim_oleh' => $thomas->id,
                'pesan' => 'Bab 1 tentang bilangan bulat sudah selesai. Mohon dampingi anak berlatih soal operasi hitung campuran di rumah.',
            ]);
            $isi = LaporanService::susunIsi($mtk, 1, $semester);
            $catatan = [
                $clara->id => 'Sudah memahami konsep, tetapi masih sering keliru pada operasi bilangan negatif. Perlu latihan tambahan.',
                $andi?->id => 'Aktif bertanya dan tugas selalu lengkap. Pertahankan.',
            ];
            foreach ($mtk->students() as $s) {
                $ls = LaporanSiswa::create([
                    'laporan_perkembangan_id' => $laporanMtk->id, 'student_id' => $s->id,
                    'isi' => $isi[$s->id], 'catatan' => $catatan[$s->id] ?? null,
                ]);
                if ($andi && $s->id === $andi->id) {
                    LaporanDibaca::create(['laporan_siswa_id' => $ls->id, 'user_id' => $sri->id, 'dibaca_at' => now()->subDays(2)]);
                }
            }
            $laporanMtk->forceFill(['created_at' => now()->subDays(3), 'updated_at' => now()->subDays(3)])->save();
        }

        // Notifikasi contoh (notifikasi otomatis dari data di atas diganti agar rapi)
        Notifikasi::query()->delete();
        $penerima7 = Notifikasi::penerimaKelas($clara->kelas_id);
        $contoh = [
            ['nilai', 'Nilai sudah tersedia', 'Nilai Tugas 2 (Matematika) sudah bisa dilihat.', 'nilai', 17],
            ['perubahan', 'Jadwal penilaian diundur', 'UTS Matematika diundur dari ' . today()->addDays(5)->translatedFormat('d F') . ' ke ' . today()->addDays(9)->translatedFormat('l, d F Y') . '. Alasan: Bentrok undangan lomba dari dinas', 'jadwal', 1],
            ['jadwal', 'Penilaian baru dijadwalkan', 'Kuis 3 (Matematika, Bab 3) pada ' . today()->addDays(2)->translatedFormat('l, d F Y') . '.', 'jadwal', 0],
        ];
        foreach ($contoh as [$jenis, $judul, $pesan, $route, $hariLalu]) {
            Notifikasi::kirim($penerima7, $jenis, $judul, $pesan, route($route));
            Notifikasi::where('judul', $judul)->update(['created_at' => now()->subDays($hariLalu)->subHours(2)]);
        }
        Notifikasi::where('created_at', '<', now()->subDays(3))->update(['dibaca_at' => now()->subDays(3)]);
        Notifikasi::kirim(User::where('role', 'admin')->pluck('id'), 'akses', 'Pengajuan buka akses unggah',
            'Bu Ndari mengajukan buka akses untuk Kuis 2 (IPA · ' . $clara->kelas->nama_kelas . ').', route('admin.akses'));
    }
}