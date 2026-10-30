<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>@yield('title', 'EduMonitor')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="{{ asset('js/grafik.js') }}"></script>
    <script src="https://cdn.tailwindcss.com/3.4.17"></script>
    <script src="{{ asset('js/tailwind-config.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    @yield('styles')
</head>
@php
    $user = Auth::user();
    $role = $user->role;
    $semesterAktif = \App\Models\Setting::semesterAktif();

    // Menu per peran: [route, ikon, label, pola route aktif, angka penanda]
    $menu = match ($role) {
        'siswa' => [
            'Pemantauan' => [
                ['dashboard', 'fa-house', 'Beranda', 'dashboard'],
                ['jadwal', 'fa-calendar-days', 'Jadwal Penilaian', 'jadwal'],
                ['nilai', 'fa-chart-simple', 'Nilai', 'nilai'],
            ],
            'Lainnya' => [
                ['siswa.evaluasi', 'fa-star-half-stroke', 'Evaluasi Guru', 'siswa.evaluasi|evaluation.*'],
                ['siswa.lapor', 'fa-flag', 'Lapor Kejadian', 'siswa.lapor'],
            ],
        ],
        'orang_tua' => [
            'Pemantauan' => [
                ['dashboard', 'fa-house', 'Beranda', 'dashboard'],
                ['jadwal', 'fa-calendar-days', 'Jadwal Penilaian', 'jadwal'],
                ['nilai', 'fa-chart-simple', 'Nilai Anak', 'nilai'],
            ],
        ],
        'guru' => array_filter([
            'Mengajar' => [
                ['dashboard', 'fa-house', 'Beranda', 'dashboard'],
                ['guru.penilaian', 'fa-clipboard-check', 'Penilaian', 'guru.penilaian*|guru.rekap-bab'],
            ],
            'Ekstrakurikuler' => \App\Models\Ekstrakurikuler::where('pembina_id', $user->id)->exists()
                ? [['guru.ekskul', 'fa-people-group', 'Ekskul Binaan', 'guru.ekskul|ekskul.*']] : null,
            'Evaluasi' => [
                ['guru.feedback', 'fa-comments', 'Feedback Siswa', 'guru.feedback'],
            ],
        ]),
        'admin' => [
            'Ringkasan' => [
                ['dashboard', 'fa-house', 'Beranda', 'dashboard'],
            ],
            'Penilaian' => [
                ['admin.jadwal', 'fa-calendar-days', 'Jadwal & UTS/UAS', 'admin.jadwal*'],
                ['admin.akses', 'fa-lock-open', 'Buka Akses Nilai', 'admin.akses', \App\Models\UploadUnlock::where('status', 'menunggu')->count()],
                ['admin.ekskul', 'fa-people-group', 'Ekstrakurikuler', 'admin.ekskul|ekskul.*'],
            ],
            'Kinerja Guru' => [
                ['admin.recap', 'fa-chart-column', 'Rekap Kinerja', 'admin.recap'],
                ['admin.laporan', 'fa-flag', 'Laporan Insidental', 'admin.laporan'],
            ],
            'Sistem' => [
                ['admin.users', 'fa-users-gear', 'Pengguna', 'admin.users'],
                ['admin.pengaturan', 'fa-gear', 'Pengaturan', 'admin.pengaturan'],
            ],
        ],
        default => [],
    };

    $profileRoute = match ($role) {
        'siswa' => 'siswa.profile', 'guru' => 'guru.profile', 'admin' => 'admin.profile', default => 'orangtua.profile',
    };
    $roleLabel = ['siswa' => 'Siswa', 'guru' => 'Guru', 'admin' => 'Admin Sekolah', 'orang_tua' => 'Orang Tua'][$role] ?? '';
    $pakaiTabbar = in_array($role, ['siswa', 'orang_tua']);
    $isActive = fn ($pola) => collect(explode('|', $pola))->contains(fn ($p) => request()->routeIs($p));
@endphp
<body class="min-h-screen antialiased">

    <!-- Lapisan gelap saat menu dibuka di HP -->
    <div id="nav-overlay" class="fixed inset-0 z-30 bg-slate-900/40 hidden lg:hidden" onclick="toggleNav(false)"></div>

    <!-- Sidebar -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-[264px] bg-white border-r border-[color:var(--line)] flex flex-col -translate-x-full lg:translate-x-0 transition-transform duration-200">
        <div class="flex items-center gap-3 px-5 h-16 border-b border-[color:var(--line)] shrink-0">
            <div class="w-9 h-9 rounded-xl bg-brand-600 text-white flex items-center justify-center"><i class="fa-solid fa-graduation-cap"></i></div>
            <div class="leading-tight">
                <p class="font-extrabold text-[17px] text-slate-800">EduMonitor</p>
                <p class="text-[11px] font-semibold text-slate-400">SMP BOPKRI 1 Yogyakarta</p>
            </div>
            <button class="ml-auto lg:hidden w-9 h-9 rounded-lg hover:bg-slate-100 text-slate-500" onclick="toggleNav(false)" aria-label="Tutup menu"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <nav class="flex-1 overflow-y-auto px-3 pb-4">
            @foreach($menu as $grup => $items)
                <p class="nav-label">{{ $grup }}</p>
                @foreach($items as $item)
                    <a href="{{ route($item[0]) }}" class="nav-link {{ $isActive($item[3]) ? 'active' : '' }}">
                        <i class="fa-solid {{ $item[1] }}"></i><span>{{ $item[2] }}</span>
                        @if(!empty($item[4]))<span class="nav-count">{{ $item[4] }}</span>@endif
                    </a>
                @endforeach
            @endforeach
            <p class="nav-label">Akun</p>
            <a href="{{ route('notifikasi') }}" class="nav-link {{ $isActive('notifikasi*') ? 'active' : '' }}">
                <i class="fa-solid fa-bell"></i><span>Notifikasi</span>
                @if($notifBelumDibaca)<span class="nav-count">{{ $notifBelumDibaca }}</span>@endif
            </a>
            <a href="{{ route($profileRoute) }}" class="nav-link {{ $isActive($profileRoute) ? 'active' : '' }}">
                <i class="fa-solid fa-user"></i><span>Profil</span>
            </a>
        </nav>

        <div class="p-3 border-t border-[color:var(--line)]">
            <div class="flex items-center gap-3 p-2">
                <div class="w-9 h-9 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-sm uppercase">{{ mb_substr($user->name, 0, 1) }}</div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-bold text-slate-700 truncate">{{ $user->name }}</p>
                    <p class="text-xs text-slate-400">{{ $roleLabel }}</p>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-9 h-9 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50" title="Keluar" aria-label="Keluar"><i class="fa-solid fa-right-from-bracket"></i></button>
                </form>
            </div>
        </div>
    </aside>

    <div class="lg:pl-[264px] min-h-screen flex flex-col">
        <!-- Bilah atas -->
        <header class="sticky top-0 z-20 bg-[color:var(--bg)]/90 backdrop-blur border-b border-[color:var(--line)]">
            <div class="h-16 px-4 sm:px-6 lg:px-8 flex items-center gap-3">
                <button class="lg:hidden w-10 h-10 -ml-2 rounded-lg hover:bg-white text-slate-600" onclick="toggleNav(true)" aria-label="Buka menu"><i class="fa-solid fa-bars text-lg"></i></button>
                <p class="sm:hidden font-extrabold text-slate-800">EduMonitor</p>
                <div class="min-w-0 hidden sm:block">
                    <p class="text-[13px] font-semibold text-slate-400 leading-tight">Semester {{ $semesterAktif }}</p>
                    <p class="text-[15px] font-bold text-slate-700 truncate leading-tight">{{ today()->translatedFormat('l, d F Y') }}</p>
                </div>

                <div class="ml-auto flex items-center gap-2">
                    @if($role === 'orang_tua' && $user->children->count() > 1)
                        <form method="POST" action="{{ route('anak.pilih') }}">
                            @csrf
                            <label for="pilih-anak" class="sr-only">Pilih anak</label>
                            <select id="pilih-anak" name="anak_id" onchange="this.form.submit()" class="input !py-2 !w-auto max-w-[160px] text-sm font-semibold">
                                @foreach($user->children as $anak)
                                    <option value="{{ $anak->id }}" @selected($user->anakAktif()?->id === $anak->id)>{{ $anak->name }}</option>
                                @endforeach
                            </select>
                        </form>
                    @endif

                    <!-- Lonceng notifikasi -->
                    <div class="relative">
                        <button id="bell-btn" onclick="toggleBell()" class="relative w-10 h-10 rounded-xl bg-white border border-[color:var(--line)] text-slate-600 hover:text-brand-700" aria-label="Notifikasi">
                            <i class="fa-solid fa-bell"></i>
                            @if($notifBelumDibaca)
                                <span class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-rose-600 text-white text-[10px] font-bold flex items-center justify-center">{{ $notifBelumDibaca > 9 ? '9+' : $notifBelumDibaca }}</span>
                            @endif
                        </button>
                        <div id="bell-panel" class="hidden absolute right-0 mt-2 w-[min(360px,calc(100vw-32px))] card shadow-xl overflow-hidden z-30">
                            <div class="flex items-center justify-between px-4 py-3 border-b border-[color:var(--line)]">
                                <p class="font-bold text-slate-700">Notifikasi</p>
                                <a href="{{ route('notifikasi') }}" class="text-sm font-bold text-brand-700">Lihat semua</a>
                            </div>
                            <div class="max-h-[360px] overflow-y-auto">
                                @forelse($notifTerbaru as $n)
                                    @php([$ikon, $warna] = $n->ikon())
                                    <a href="{{ route('notifikasi.buka', $n) }}" class="flex gap-3 px-4 py-3 hover:bg-slate-50 border-b border-slate-100 last:border-0 {{ $n->dibaca_at ? '' : 'bg-brand-50/50' }}">
                                        <span class="w-9 h-9 shrink-0 rounded-lg flex items-center justify-center {{ $warna }}"><i class="fa-solid {{ $ikon }}"></i></span>
                                        <span class="min-w-0">
                                            <span class="block text-sm font-bold text-slate-700">{{ $n->judul }}</span>
                                            <span class="block text-[13px] text-slate-500 line-clamp-2">{{ $n->pesan }}</span>
                                            <span class="block text-[11px] text-slate-400 mt-0.5">{{ $n->created_at->diffForHumans() }}</span>
                                        </span>
                                    </a>
                                @empty
                                    <div class="empty"><i class="fa-regular fa-bell"></i>Belum ada notifikasi.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 {{ $pakaiTabbar ? 'pb-28 lg:pb-10' : 'pb-10' }}">
            @if(session('success'))
                <div class="mb-5 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                    <i class="fa-solid fa-circle-check mt-0.5"></i><span>{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-5 flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800">
                    <i class="fa-solid fa-circle-exclamation mt-0.5"></i><span>{{ session('error') }}</span>
                </div>
            @endif
            @if($errors->any())
                <div class="mb-5 flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800">
                    <i class="fa-solid fa-circle-exclamation mt-0.5"></i><span>{{ $errors->first() }}</span>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @if($pakaiTabbar)
        <!-- Navigasi bawah untuk HP (siswa dan orang tua) -->
        <nav class="tabbar lg:hidden fixed bottom-0 inset-x-0 z-20 bg-white border-t border-[color:var(--line)] grid grid-cols-4" style="padding-bottom: env(safe-area-inset-bottom)">
            <a href="{{ route('dashboard') }}" class="{{ $isActive('dashboard') ? 'active' : '' }}"><i class="fa-solid fa-house"></i>Beranda</a>
            <a href="{{ route('jadwal') }}" class="{{ $isActive('jadwal') ? 'active' : '' }}"><i class="fa-solid fa-calendar-days"></i>Jadwal</a>
            <a href="{{ route('nilai') }}" class="{{ $isActive('nilai') ? 'active' : '' }}"><i class="fa-solid fa-chart-simple"></i>Nilai</a>
            <a href="{{ route('notifikasi') }}" class="{{ $isActive('notifikasi*') ? 'active' : '' }}">
                <i class="fa-solid fa-bell"></i>Notifikasi
                @if($notifBelumDibaca)<span class="absolute top-1 left-1/2 ml-2 w-2 h-2 rounded-full bg-rose-600"></span>@endif
            </a>
        </nav>
    @endif

    <script>
        function toggleNav(open) {
            document.getElementById('sidebar').classList.toggle('-translate-x-full', !open);
            document.getElementById('nav-overlay').classList.toggle('hidden', !open);
        }
        function toggleBell() {
            document.getElementById('bell-panel').classList.toggle('hidden');
        }
        document.addEventListener('click', function (e) {
            const panel = document.getElementById('bell-panel');
            if (!panel.contains(e.target) && !document.getElementById('bell-btn').contains(e.target)) {
                panel.classList.add('hidden');
            }
        });
    </script>
    @yield('scripts')
</body>
</html>