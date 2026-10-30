@extends('layouts.app')

@section('title', 'Ekstrakurikuler - EduMonitor')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="page-title">Ekstrakurikuler</h1>
        <p class="page-sub">Atur daftar ekskul, pembina, dan anggota. Pembina mengisi predikat dan deskripsi pada akhir semester {{ $semester }}.</p>
    </div>

    <div class="grid lg:grid-cols-3 gap-6 items-start">
        <section class="card overflow-hidden lg:col-span-2">
            <div class="tbl-wrap">
                <table class="tbl">
                    <thead><tr><th>Ekstrakurikuler</th><th>Pembina</th><th class="num">Anggota</th><th class="num">Nilai terisi</th><th></th></tr></thead>
                    <tbody>
                        @forelse($ekskul as $e)
                            <tr>
                                <td class="font-bold text-slate-800">{{ $e->nama }}</td>
                                <td>{{ $e->pembina->name ?? '-' }}</td>
                                <td class="num">{{ $e->anggota->count() }}</td>
                                <td class="num">{{ $e->anggota->whereNotNull('predikat')->count() }}</td>
                                <td class="text-right whitespace-nowrap">
                                    <a href="{{ route('ekskul.show', $e) }}" class="btn btn-light btn-sm">Kelola</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><div class="empty"><i class="fa-solid fa-people-group"></i>Belum ada ekstrakurikuler.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="card card-pad">
            <h2 class="card-title mb-3">Tambah ekstrakurikuler</h2>
            <form method="POST" action="{{ route('admin.ekskul.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="label" for="e-nama">Nama</label>
                    <input id="e-nama" name="nama" class="input" required maxlength="100" placeholder="Contoh: Pramuka">
                </div>
                <div>
                    <label class="label" for="e-pembina">Pembina</label>
                    <select id="e-pembina" name="pembina_id" class="input">
                        <option value="">Belum ditetapkan</option>
                        @foreach($guru as $g)<option value="{{ $g->id }}">{{ $g->name }}</option>@endforeach
                    </select>
                    <p class="hint">Bila pembina dari luar sekolah, kosongkan dan nilai diisi oleh admin.</p>
                </div>
                <button class="btn btn-primary w-full"><i class="fa-solid fa-plus"></i>Tambah</button>
            </form>
        </section>
    </div>
</div>
@endsection