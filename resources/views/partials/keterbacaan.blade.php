{{-- Ringkasan status baca: $baca dari $total orang tua --}}
@if($total > 0)
    @php($persen = round($baca / $total * 100))
    <span class="shrink-0 w-28 text-right">
        <span class="block text-sm font-bold {{ $baca === $total ? 'text-nila-700' : 'text-slate-700' }} tabular">{{ $baca }}/{{ $total }} dibaca</span>
        <span class="block h-1.5 mt-1.5 rounded-full bg-slate-100 overflow-hidden"><span class="block h-full rounded-full {{ $baca === $total ? 'bg-nila-600' : 'bg-soga-400' }}" style="width: {{ $persen }}%"></span></span>
    </span>
@else
    <span class="shrink-0 text-sm text-slate-500">Tidak ada data orang tua</span>
@endif