<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - EduMonitor</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tailwind CSS Play CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f5f3ff',
                            100: '#ede9fe',
                            200: '#ddd6fe',
                            300: '#c084fc',
                            400: '#a78bfa',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                        }
                    },
                    fontFamily: {
                        sans: ['Outfit', 'Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Outfit', 'Inter', sans-serif;
            background: linear-gradient(135deg, #f0f4f9 0%, #e0e7ff 100%);
        }
        .glass-container {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.4);
        }
        .login-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.8);
        }
        .hover-lift {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(99, 102, 241, 0.15);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 md:p-8">

    <div class="w-full max-w-6xl glass-container rounded-[2rem] shadow-2xl overflow-hidden grid grid-cols-1 lg:grid-cols-12 min-h-[680px]">
        
        <!-- Left Side: educational branding -->
        <div class="lg:col-span-5 bg-gradient-to-br from-indigo-600 to-indigo-800 p-8 md:p-12 flex flex-col justify-between text-white relative overflow-hidden">
            <!-- Decorative circle shapes -->
            <div class="absolute -top-24 -left-24 w-72 h-72 rounded-full bg-indigo-500 opacity-20 blur-xl"></div>
            <div class="absolute -bottom-24 -right-24 w-72 h-72 rounded-full bg-pink-500 opacity-20 blur-xl"></div>

            <div class="z-10 flex items-center gap-3">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-md">
                    <i class="fa-solid fa-graduation-cap text-lg text-white"></i>
                </div>
                <span class="text-2xl font-extrabold tracking-tight">EduMonitor</span>
            </div>

            <div class="my-auto z-10 space-y-6 py-12 lg:py-0">
                <h2 class="text-4xl font-extrabold leading-tight">
                    Sistem Terpadu<br>
                    Pemantauan Belajar &<br>
                    Evaluasi SMP
                </h2>
                <p class="text-indigo-100 text-base leading-relaxed max-w-sm">
                    Akses perkembangan hasil belajar secara real-time dan isi instrumen evaluasi pembelajaran standar dengan mudah.
                </p>
                
                <div class="flex items-center gap-4 pt-4">
                    <div class="flex -space-x-3">
                        <div class="w-8 h-8 rounded-full bg-white/20 border-2 border-indigo-600 flex items-center justify-center text-[10px] font-bold">SW</div>
                        <div class="w-8 h-8 rounded-full bg-white/20 border-2 border-indigo-600 flex items-center justify-center text-[10px] font-bold">GR</div>
                        <div class="w-8 h-8 rounded-full bg-white/20 border-2 border-indigo-600 flex items-center justify-center text-[10px] font-bold">OT</div>
                        <div class="w-8 h-8 rounded-full bg-white/20 border-2 border-indigo-600 flex items-center justify-center text-[10px] font-bold">AD</div>
                    </div>
                    <span class="text-sm font-semibold text-indigo-100">Multi-role login dashboard</span>
                </div>
            </div>

            <div class="text-xs text-indigo-200 z-10">
                &copy; 2026 EduMonitor. Semua Hak Dilindungi.
            </div>
        </div>

        <!-- Right Side: Login Form + Quick Demo login -->
        <div class="lg:col-span-7 p-8 md:p-12 flex flex-col justify-center bg-white/50">
            
            <div class="max-w-md w-full mx-auto space-y-8">
                <div>
                    <h3 class="text-3xl font-extrabold text-slate-800">Selamat Datang Kembali!</h3>
                    <p class="text-slate-400 mt-2 text-sm font-medium">Masuk untuk melihat perkembangan belajar anak atau mengisi evaluasi.</p>
                </div>

                <!-- Form Login -->
                <form action="{{ url('/login') }}" method="POST" class="space-y-5">
                    @csrf

                    @if($errors->any())
                        <div class="p-4 bg-rose-50 border border-rose-100 text-rose-800 rounded-2xl text-sm flex items-center gap-2.5">
                            <i class="fa-solid fa-circle-exmark text-rose-500"></i>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    @endif

                    <div class="space-y-1.5">
                        <label for="login_id" class="text-sm font-bold text-slate-600">Username atau Email</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">
                                <i class="fa-regular fa-user"></i>
                            </span>
                            <input type="text" 
                                   id="login_id" 
                                   name="login_id" 
                                   value="{{ old('login_id') }}" 
                                   placeholder="Contoh: clara atau clara@edumonitor.sch.id"
                                   required 
                                   class="w-full pl-11 pr-4 py-3.5 bg-white border border-slate-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-medium text-slate-700 placeholder-slate-400 transition-all">
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <div class="flex justify-between items-center">
                            <label for="password" class="text-sm font-bold text-slate-600">Password</label>
                        </div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Masukkan password Anda"
                                   required 
                                   class="w-full pl-11 pr-4 py-3.5 bg-white border border-slate-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-medium text-slate-700 placeholder-slate-400 transition-all">
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded text-indigo-600 border-slate-200 focus:ring-indigo-500">
                            <span class="text-xs font-semibold text-slate-500">Ingat Saya</span>
                        </label>
                    </div>

                    <button type="submit" 
                            id="btn-login-submit"
                            class="w-full py-4 px-6 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-2xl transition-all shadow-lg shadow-indigo-100 hover-lift flex items-center justify-center gap-2">
                        <span>MASUK KE SISTEM</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </form>



            </div>

        </div>

    </div>

</body>
</html>
