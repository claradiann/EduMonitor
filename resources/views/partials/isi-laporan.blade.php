{{-- Isi laporan perkembangan satu siswa. Variabel: $isi (array dari kolom laporan_siswa.isi), $nama (opsional) --}}
@php
    $fmt = fn ($v) => $v === null ? '-' : rtrim(rtrim(number_format($v, 1, ',', ''), '0'), ',');
    $u = $isi['utama'];
    $selisih = ($u['nilai'] !== null && $u['kelas'] !== null) ? round($u['nilai'] - $u['kelas'], 1) : null;
@endphp
<div class="flex flex-wrap items-end gap-x-8 gap-y-3">
    <div>
        <p class="text-sm font-semibold text-slate-600">{{ $u['label'] }}</p>
        <p class="nilai text-[40px] leading-none mt-1">{{ $fmt($u['nilai']) }}</p>
        @if($u['sementara'] && $u['nilai'] !== null)
            <span class="badge badge-amber mt-2">sementara, UTS/UAS belum lengkap</span>
        @endif
    </div>
    <div class="pb-1">
        <p class="text-sm font-semibold text-slate-600">Rata-rata kelas</p>
        <p class="text-xl font-extrabold tabular text-slate-700 mt-1">{{ $fmt($u['kelas']) }}</p>
    </div>
    @if($selisih !== null)
        <p class="pb-1.5 text-sm font-bold {{ $selisih >= 0 ? 'text-nila-700' : 'text-soga-700' }}">
            <i class="fa-solid {{ $selisih >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }} mr-1"></i>{{ $fmt(abs($selisih)) }} {{ $selisih >= 0 ? 'di atas' : 'di bawah' }} rata-rata kelas
        </p>
    @endif
</div>

@if(count($isi['baris']))
    <div class="mt-5 rows border-t border-slate-200">
        @foreach($isi['baris'] as $b)
            @php($lebar = $b['nilai'] !== null ? max(2, min(100, $b['nilai'])) : 0)
            <div class="py-3">
                <div class="flex items-baseline justify-between gap-3">
                    <p class="min-w-0"><span class="font-bold text-slate-900">{{ $b['label'] }}</span> <span class="text-sm text-slate-500">&middot; {{ $b['keterangan'] }}</span></p>
                    <p class="shrink-0 text-right">
                        <span class="nilai text-lg">{{ $fmt($b['nilai']) }}</span>
                        <span class="text-[13px] text-slate-500 tabular ml-1">kelas {{ $fmt($b['kelas']) }}</span>
                    </p>
                </div>
                {{-- Batang nilai dengan penanda rata-rata kelas --}}
                <div class="relative h-2 mt-2 rounded-full bg-slate-100" aria-hidden="true">
                    <div class="absolute inset-y-0 left-0 rounded-full {{ $b['nilai'] === null ? '' : 'bg-nila-500' }}" style="width: {{ $lebar }}%"></div>
                    @if($b['kelas'] !== null)
                        <div class="absolute -top-1 -bottom-1 w-[3px] rounded bg-soga-500" style="left: calc({{ min(100, $b['kelas']) }}% - 1.5px)" title="Rata-rata kelas"></div>
                    @endif
                </div>
                @if($b['nilai'] === null)
                    <p class="text-[13px] text-slate-500 mt-1">Belum ada nilai (tidak ikut atau susulan).</p>
                @endif
            </div>
        @endforeach
    </div>
    <p class="hint flex items-center gap-2 mt-1"><span class="inline-block w-[3px] h-3 rounded bg-soga-500"></span>Garis coklat: rata-rata kelas.</p>
@endif