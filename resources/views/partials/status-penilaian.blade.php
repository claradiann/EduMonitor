@php($s = \App\Models\Assessment::STATUS[$status])
<span class="badge {{ $s['class'] }}"><i class="fa-solid {{ $s['icon'] }} text-[11px]"></i>{{ $s['label'] }}</span>