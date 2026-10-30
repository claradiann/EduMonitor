@extends('layouts.app')

@section('title', 'Notifikasi - EduMonitor')

@section('content')
<div class="space-y-6 max-w-3xl">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="page-title">Notifikasi</h1>
            <p class="page-sub">Jadwal penilaian, perubahan jadwal, dan nilai yang sudah keluar.</p>
        </div>
        <form method="POST" action="{{ route('notifikasi.baca-semua') }}">
            @csrf
            <button class="btn btn-light btn-sm"><i class="fa-solid fa-check-double"></i>Tandai semua dibaca</button>
        </form>
    </div>

    <div class="card divide-y divide-slate-100 overflow-hidden">
        @forelse($notifikasi as $n)
            @php([$ikon, $warna] = $n->ikon())
            <a href="{{ route('notifikasi.buka', $n) }}" class="flex gap-3 p-4 hover:bg-slate-50 {{ $n->dibaca_at ? '' : 'bg-brand-50/40' }}">
                <span class="w-10 h-10 shrink-0 rounded-xl flex items-center justify-center {{ $warna }}"><i class="fa-solid {{ $ikon }}"></i></span>
                <span class="min-w-0 flex-1">
                    <span class="flex items-center gap-2">
                        <span class="font-bold text-slate-800">{{ $n->judul }}</span>
                        @unless($n->dibaca_at)<span class="w-2 h-2 rounded-full bg-rose-600"></span>@endunless
                    </span>
                    <span class="block text-sm text-slate-600 mt-0.5">{{ $n->pesan }}</span>
                    <span class="block text-xs text-slate-400 mt-1">{{ $n->created_at->translatedFormat('d F Y, H:i') }}</span>
                </span>
            </a>
        @empty
            <div class="empty"><i class="fa-regular fa-bell"></i>Belum ada notifikasi.</div>
        @endforelse
    </div>

    {{ $notifikasi->links() }}
</div>
@endsection