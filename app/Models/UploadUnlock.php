<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Pengajuan dan pembukaan akses unggah nilai setelah melewati batas. */
#[Fillable(['assessment_id', 'diajukan_oleh', 'alasan', 'status', 'diputuskan_oleh', 'dibuka_at', 'berakhir_at'])]
class UploadUnlock extends Model
{
    protected function casts(): array
    {
        return ['dibuka_at' => 'datetime', 'berakhir_at' => 'datetime'];
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function pengaju(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diajukan_oleh');
    }

    public function pemutus(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diputuskan_oleh');
    }

    public function aktif(): bool
    {
        return $this->status === 'dibuka' && $this->berakhir_at && now()->between($this->dibuka_at, $this->berakhir_at);
    }

    public function statusLabel(): string
    {
        return match (true) {
            $this->status === 'menunggu' => 'Menunggu persetujuan',
            $this->status === 'ditolak' => 'Ditolak',
            $this->aktif() => 'Sedang dibuka',
            default => 'Selesai',
        };
    }
}