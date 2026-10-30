<?php

use App\Models\Assessment;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Pengingat otomatis ke guru lewat email untuk penilaian yang nilainya
 * belum lengkap dan sudah melewati batas unggah. Pengingat di dalam
 * aplikasi tampil langsung di beranda guru tanpa perlu perintah ini.
 */
Artisan::command('penilaian:kirim-pengingat', function () {
    $terkirim = 0;

    Assessment::where('semester', Setting::semesterAktif())
        ->whereNull('pengingat_terkirim_at')
        ->with(['scores', 'subjectTeacher.teacher', 'subjectTeacher.subject', 'subjectTeacher.kelas'])
        ->get()
        ->each(function (Assessment $a) use (&$terkirim) {
            $st = $a->subjectTeacher;
            $jumlahSiswa = User::where('role', 'siswa')->where('kelas_id', $st->kelas_id)->count();

            if ($a->statusKelas($jumlahSiswa) !== 'melewati_batas' || !$st->teacher?->email) {
                return;
            }

            $pesan = "Yth. {$st->teacher->name},\n\n"
                . "Nilai {$a->judul} ({$a->jenisLabel()}) untuk {$st->subject->nama_mapel} kelas {$st->kelas->nama_kelas} "
                . "belum lengkap. Penilaian dilaksanakan {$a->tanggal->format('d/m/Y')} dengan batas unggah "
                . "{$a->batasUnggah()->format('d/m/Y')}.\n\n"
                . "Mohon segera memasukkan nilai melalui menu Penilaian di EduMonitor.\n";

            Mail::raw($pesan, function ($m) use ($st, $a) {
                $m->to($st->teacher->email)->subject("Pengingat unggah nilai: {$a->judul}");
            });

            $a->forceFill(['pengingat_terkirim_at' => now()])->save();
            $terkirim++;
        });

    $this->info("Pengingat terkirim: {$terkirim}");
})->purpose('Kirim email pengingat untuk penilaian yang melewati batas unggah');

Schedule::command('penilaian:kirim-pengingat')->dailyAt('07:00');
