<?php

namespace App\Providers;

use App\Services\PengingatService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Nama hari dan bulan dalam bahasa Indonesia
        Carbon::setLocale('id');

        // Data lonceng notifikasi untuk semua halaman
        View::composer('layouts.app', function ($view) {
            $user = Auth::user();
            if (!$user) {
                return;
            }
            if ($user->role === 'guru') {
                PengingatService::untukGuru($user);
            } elseif (in_array($user->role, ['siswa', 'orang_tua'])) {
                PengingatService::untukSiswa($user);
            }
            $view->with([
                'notifBelumDibaca' => $user->notifikasi()->whereNull('dibaca_at')->count(),
                'notifTerbaru' => $user->notifikasi()->take(6)->get(),
            ]);
        });
    }
}