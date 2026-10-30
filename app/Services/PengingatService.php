<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\Notifikasi;
use App\Models\Setting;
use App\Models\User;

/**
 * Pengingat di dalam aplikasi (sekolah tidak memakai email).
 * Dipanggil saat pengguna membuka aplikasi, jadi tidak perlu scheduler di server.
 */
class PengingatService
{
    public static function untukGuru(User $guru): void
    {
        Assessment::where('semester', Setting::semesterAktif())
            ->whereNull('pengingat_terkirim_at')
            ->whereDate('tanggal', '<', today()->subDays(Setting::batasUnggahHari()))
            ->whereHas('subjectTeacher', fn ($q) => $q->where('teacher_id', $guru->id))
            ->with(['subjectTeacher.subject', 'subjectTeacher.kelas', 'scores', 'makeups'])
            ->get()
            ->each(function (Assessment $a) use ($guru) {
                $jumlah = User::where('role', 'siswa')->where('kelas_id', $a->subjectTeacher->kelas_id)->count();
                if ($a->statusKelas($jumlah) !== 'melewati_batas') {
                    return;
                }

                Notifikasi::kirim(
                    [$guru->id],
                    'pengingat',
                    'Nilai melewati batas unggah',
                    "Nilai {$a->judul} ({$a->subjectTeacher->label()}) belum lengkap. Batas unggah " . $a->batasUnggah()->translatedFormat('d F Y') . '. Ajukan buka akses ke admin untuk memasukkan nilai.',
                    route('guru.penilaian.input', $a)
                );
                $a->forceFill(['pengingat_terkirim_at' => now()])->save();
            });
    }

    /**
     * Pengingat H-1 dan hari H untuk siswa dan orang tua: penilaian yang akan dilaksanakan.
     * Setiap pengingat hanya dikirim sekali per pengguna.
     */
    public static function untukSiswa(User $user): void
    {
        $siswa = $user->role === 'orang_tua' ? $user->children : collect([$user]);

        foreach ($siswa as $s) {
            if (!$s->kelas_id) {
                continue;
            }
            Assessment::where('semester', Setting::semesterAktif())
                ->whereHas('subjectTeacher', fn ($q) => $q->where('kelas_id', $s->kelas_id))
                ->whereDate('tanggal', '>=', today()->subDays(14))
                ->with(['subjectTeacher.subject', 'scores', 'makeups'])
                ->get()
                ->each(function (Assessment $a) use ($user, $s) {
                    $tanggal = $a->tanggalUntuk($s->id);
                    if (!$tanggal->isToday() && !$tanggal->isTomorrow()) {
                        return;
                    }
                    if ($a->statusUntukSiswa($s->id) !== 'dijadwalkan') {
                        return;
                    }

                    $kapan = $tanggal->isToday() ? 'Hari ini' : 'Besok';
                    $mapel = $a->subjectTeacher->subject->nama_mapel;
                    $pesan = ($user->role === 'orang_tua' ? strtok($s->name, ' ') . ': ' : '')
                        . "{$kapan}, " . $tanggal->translatedFormat('l d F') . ", ada {$a->judul} {$mapel}"
                        . ($a->bab ? " (Bab {$a->bab})" : '') . '. Semangat belajar!';

                    $sudah = Notifikasi::where('user_id', $user->id)->where('jenis', 'pengingat')->where('pesan', $pesan)->exists();
                    if (!$sudah) {
                        Notifikasi::kirim([$user->id], 'pengingat', $kapan . ' ada penilaian', $pesan, route('jadwal'));
                    }
                });
        }
    }
}