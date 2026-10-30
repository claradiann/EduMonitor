<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

#[Fillable(['user_id', 'jenis', 'judul', 'pesan', 'url', 'dibaca_at'])]
class Notifikasi extends Model
{
    protected $table = 'notifikasi';

    public const IKON = [
        'jadwal' => ['fa-calendar-plus', 'text-sky-600 bg-sky-50'],
        'perubahan' => ['fa-calendar-days', 'text-amber-600 bg-amber-50'],
        'susulan' => ['fa-user-clock', 'text-violet-600 bg-violet-50'],
        'nilai' => ['fa-square-poll-vertical', 'text-emerald-600 bg-emerald-50'],
        'akses' => ['fa-lock-open', 'text-brand-600 bg-brand-50'],
        'pengingat' => ['fa-bell', 'text-rose-600 bg-rose-50'],
        'info' => ['fa-circle-info', 'text-slate-600 bg-slate-100'],
    ];

    protected function casts(): array
    {
        return ['dibaca_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ikon(): array
    {
        return self::IKON[$this->jenis] ?? self::IKON['info'];
    }

    /** Kirim notifikasi yang sama ke sekumpulan pengguna (id atau model). */
    public static function kirim(iterable $users, string $jenis, string $judul, string $pesan, ?string $url = null): void
    {
        $now = now();
        // Simpan alamat relatif agar tautan tetap benar di alamat server mana pun
        if ($url) {
            $p = parse_url($url);
            $url = ($p['path'] ?? '/') . (isset($p['query']) ? '?' . $p['query'] : '');
        }
        $rows = collect($users)
            ->map(fn ($u) => is_object($u) ? $u->id : $u)
            ->unique()
            ->map(fn ($id) => [
                'user_id' => $id, 'jenis' => $jenis, 'judul' => $judul, 'pesan' => $pesan,
                'url' => $url, 'created_at' => $now, 'updated_at' => $now,
            ]);

        if ($rows->isNotEmpty()) {
            static::insert($rows->values()->all());
        }
    }

    /** Siswa beserta orang tuanya. */
    public static function penerimaSiswa(iterable $studentIds): Collection
    {
        $ids = collect($studentIds)->map(fn ($s) => is_object($s) ? $s->id : $s);
        $ortu = \DB::table('parent_student')->whereIn('student_id', $ids)->pluck('parent_id');

        return $ids->merge($ortu)->unique()->values();
    }

    /** Seluruh siswa di satu kelas beserta orang tuanya. */
    public static function penerimaKelas(int $kelasId): Collection
    {
        return static::penerimaSiswa(User::where('role', 'siswa')->where('kelas_id', $kelasId)->pluck('id'));
    }
}