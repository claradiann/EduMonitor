<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'EduMonitor - Pemantauan Hasil Belajar & Evaluasi')</title>
    
    <!-- Google Fonts: Outfit & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Tailwind CSS (Vite or Fallback CDN to guarantee rendering) -->
    @vite(['resources/css/app.css'])
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="{{ asset('js/tailwind-config.js') }}"></script>

    <!-- Custom Styles -->
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">

    @yield('styles')
</head>
<body class="min-h-screen text-slate-800 antialiased flex bg-slate-50">

    <!-- Sidebar Layout -->
    <aside class="w-72 bg-white flex flex-col justify-between p-6 border-r border-slate-100 shrink-0">
        <div>
            <!-- Brand Logo -->
            <div class="flex items-center gap-3 mb-10 px-2">
                <div class="w-10 h-10 bg-brand-500 rounded-xl flex items-center justify-center text-white shadow-md shadow-brand-200">
                    <i class="fa-solid fa-graduation-cap text-lg"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-extrabold tracking-tight text-blue-600">EduMonitor</h1>
                    <span class="text-xs font-semibold text-slate-400 tracking-wider uppercase">SMP System</span>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="space-y-2">
                @php
                    $role = Auth::user()->role;
                    $route = Route::currentRouteName();
                @endphp

                <!-- Beranda (Semua Role) -->
                <a href="{{ route('dashboard') }}" 
                   id="nav-home"
                   class="flex items-center gap-4 px-4 py-3.5 rounded-2xl text-slate-600 font-medium hover:bg-slate-50 transition-all {{ $route == 'dashboard' ? 'sidebar-item-active shadow-md shadow-indigo-100' : '' }}">
                    <i class="fa-solid fa-house text-lg {{ $route == 'dashboard' ? 'text-white' : 'text-slate-400' }}"></i>
                    <span>Beranda</span>
                </a>

                @if($role == 'siswa')
                    <!-- Nilai (Siswa) -->
                    <a href="{{ route('siswa.nilai') }}" 
                    id="nav-nilai"
                    class="flex items-center gap-4 px-4 py-3.5 rounded-2xl text-slate-600 font-medium hover:bg-slate-50 transition-all {{ $route == 'siswa.nilai' ? 'sidebar-item-active shadow-md shadow-indigo-100' : '' }}">
                    <i class="fa-solid fa-chart-simple text-lg {{ $route == 'siswa.nilai' ? 'text-white' : 'text-slate-400' }}"></i>
                    <span>Nilai</span>
                    </a>

                    <!-- Evaluasi (Siswa) -->
                    <a href="{{ route('siswa.evaluasi') }}" 
                    id="nav-evaluasi"
                    class="flex items-center gap-4 px-4 py-3.5 rounded-2xl text-slate-600 font-medium hover:bg-slate-50 transition-all {{ $route == 'siswa.evaluasi' ? 'sidebar-item-active shadow-md shadow-indigo-100' : '' }}">
                    <i class="fa-solid fa-pen-to-square text-lg {{ $route == 'siswa.evaluasi' ? 'text-white' : 'text-slate-400' }}"></i>
                    <span>Evaluasi</span>
                </a>

                @elseif($role == 'orang_tua')
                    <!-- Nilai Anak (Orang Tua) -->
                    <a href="#nilai-section" 
                       id="nav-nilai"
                       class="flex items-center gap-4 px-4 py-3.5 rounded-2xl text-slate-600 font-medium hover:bg-slate-50 transition-all">
                        <i class="fa-solid fa-chart-simple text-lg text-slate-400"></i>
                        <span>Nilai Siswa</span>
                    </a>

                @elseif($role == 'guru')
                    <!-- Evaluasi & Feedback (Guru) -->
                    <a href="{{ route('guru.feedback') }}" 
                       id="nav-feedback"
                       class="flex items-center gap-4 px-4 py-3.5 rounded-2xl text-slate-600 font-medium hover:bg-slate-50 transition-all {{ $route == 'guru.feedback' ? 'sidebar-item-active shadow-md shadow-indigo-100' : '' }}">
                        <i class="fa-solid fa-comments text-lg {{ $route == 'guru.feedback' ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>Feedback Evaluasi</span>
                    </a>

                @elseif($role == 'admin')
                    <!-- User Management (Admin) -->
                    <a href="{{ route('admin.users') }}" 
                       id="nav-users"
                       class="flex items-center gap-4 px-4 py-3.5 rounded-2xl text-slate-600 font-medium hover:bg-slate-50 transition-all {{ $route == 'admin.users' ? 'sidebar-item-active shadow-md shadow-indigo-100' : '' }}">
                        <i class="fa-solid fa-users-gear text-lg {{ $route == 'admin.users' ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>Manajemen User</span>
                    </a>

                    <!-- Rekap Kuesioner (Admin) -->
                    <a href="{{ route('admin.recap') }}" 
                       id="nav-recap"
                       class="flex items-center gap-4 px-4 py-3.5 rounded-2xl text-slate-600 font-medium hover:bg-slate-50 transition-all {{ $route == 'admin.recap' ? 'sidebar-item-active shadow-md shadow-indigo-100' : '' }}">
                        <i class="fa-solid fa-file-invoice text-lg {{ $route == 'admin.recap' ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>Rekap Evaluasi</span>
                    </a>
                @endif

                <!-- Profile (Semua Role) -->
                @php
                    $profileRoute = match($role) {
                        'siswa' => route('siswa.profile'),
                        'guru' => route('guru.profile'),
                        'admin' => route('admin.profile'),
                        'orang_tua' => route('orangtua.profile'),
                        default => '#',
                    };
                    $profileRouteNames = ['siswa.profile', 'guru.profile', 'admin.profile', 'orangtua.profile'];
                @endphp 
                <a href="{{ $profileRoute }}" 
                   id="nav-profile"
                   class="flex items-center gap-4 px-4 py-3.5 rounded-2xl text-slate-600 font-medium hover:bg-slate-50 transition-all {{ in_array($route, $profileRouteNames) ? 'sidebar-item-active shadow-md shadow-indigo-100' : '' }}">
                    <i class="fa-solid fa-user text-lg {{ in_array($route, $profileRouteNames) ? 'text-white' : 'text-slate-400' }}"></i>
                    <span>Profile</span>
                </a>
            </nav>
        </div>

        <!-- User Section Bottom -->
        <div class="space-y-4">
            <!-- User Info Card -->
            <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-2xl border border-slate-100">
                <div class="w-10 h-10 bg-brand-100 rounded-full flex items-center justify-center text-brand-600 font-bold uppercase">
                    {{ substr(Auth::user()->name, 0, 2) }}
                </div>
                <div class="overflow-hidden">
                    <h4 class="font-bold text-sm text-slate-700 truncate">{{ Auth::user()->name }}</h4>
                    <p class="text-xs text-slate-400 font-medium truncate">
                        @if($role == 'siswa')
                            ID Siswa: {{ Auth::user()->nisn }}
                        @elseif($role == 'guru')
                            NIP: {{ Auth::user()->nip }}
                        @elseif($role == 'orang_tua')
                            Orang Tua Siswa
                        @else
                            Administrator
                        @endif
                    </p>
                </div>
            </div>

            <!-- Logout Button -->
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" 
                        id="btn-logout"
                        class="w-full bg-red-500 hover:bg-red-600 text-white font-bold py-3.5 px-6 rounded-2xl transition-all shadow-lg shadow-red-100 flex items-center justify-center gap-2 hover-lift">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>KELUAR</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 p-8 overflow-y-auto w-full">
        <!-- Flash Messages -->
        @if(session('success'))
            <div id="alert-success" class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center gap-3 shadow-sm">
                <i class="fa-solid fa-circle-check text-emerald-500 text-lg"></i>
                <span class="font-medium text-sm">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div id="alert-error" class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl flex items-center gap-3 shadow-sm">
                <i class="fa-solid fa-circle-xmark text-rose-500 text-lg"></i>
                <span class="font-medium text-sm">{{ session('error') }}</span>
            </div>
        @endif

        @yield('content')
    </main>

    @yield('scripts')
</body>
</html>