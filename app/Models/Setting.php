<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

#[Fillable(['key', 'value'])]
class Setting extends Model
{
    public const DEFAULTS = [
        'batas_unggah_hari' => '7',
        'durasi_buka_akses_jam' => '24',
        'semester_aktif' => 'Genap 2025/2026',
        'batas_nilai_akhir' => null,
    ];

    public static function get(string $key, $default = null)
    {
        $value = static::where('key', $key)->value('value');

        return $value ?? $default ?? (self::DEFAULTS[$key] ?? null);
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public static function semesterAktif(): string
    {
        return (string) static::get('semester_aktif');
    }

    /** Lama masa input nilai setelah tanggal penilaian (hari). */
    public static function batasUnggahHari(): int
    {
        return (int) static::get('batas_unggah_hari');
    }

    /** Lama akses unggah yang dibuka admin setelah lewat batas (jam). */
    public static function durasiBukaAksesJam(): int
    {
        return (int) static::get('durasi_buka_akses_jam');
    }

    /** Batas pengumpulan nilai akhir sesuai kalender akademik. */
    public static function batasNilaiAkhir(): ?Carbon
    {
        $v = static::get('batas_nilai_akhir');

        return $v ? Carbon::parse($v)->endOfDay() : null;
    }
}