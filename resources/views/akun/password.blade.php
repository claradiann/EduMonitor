@extends('layouts.app')

@section('title', 'Ganti Password - EduMonitor')

@section('content')
<div class="space-y-6 max-w-xl">
    <div>
        <h1 class="page-title">Ganti Password</h1>
        <p class="page-sub">Gunakan minimal 6 karakter. Setelah diganti, akun ini otomatis keluar dari perangkat lain.</p>
    </div>

    <form method="POST" action="{{ route('akun.password.ubah') }}" class="card card-pad space-y-5">
        @csrf @method('PUT')

        <div>
            <label class="label" for="password_lama">Password lama</label>
            <input id="password_lama" type="password" name="password_lama" class="input" required autocomplete="current-password">
            @error('password_lama')<p class="text-sm text-rose-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="label" for="password">Password baru</label>
            <input id="password" type="password" name="password" class="input" required minlength="6" autocomplete="new-password">
            @error('password')<p class="text-sm text-rose-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="label" for="password_confirmation">Ulangi password baru</label>
            <input id="password_confirmation" type="password" name="password_confirmation" class="input" required minlength="6" autocomplete="new-password">
        </div>

        <button class="btn btn-primary"><i class="fa-solid fa-key"></i>Simpan password</button>
    </form>
</div>
@endsection