<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - EduMonitor</title>
    <meta name="description" content="Masuk ke sistem EduMonitor untuk memantau perkembangan belajar dan evaluasi SMP.">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Auth Styles -->
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body class="auth-body">

    <div class="auth-container">

        <!-- Left Side: Brand Panel -->
        <div class="auth-brand">
            <!-- Decorative blobs -->
            <div class="decor decor-top"></div>
            <div class="decor decor-bottom"></div>

            <!-- Logo -->
            <div class="auth-brand-logo">
                <div class="auth-brand-logo-icon">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
                <span>EduMonitor</span>
            </div>

            <!-- Content -->
            <div class="auth-brand-content">
                <h2>
                    Sistem Terpadu<br>
                    Pemantauan Belajar &<br>
                    Evaluasi SMP
                </h2>
                <p>
                    Akses perkembangan hasil belajar secara real-time dan isi instrumen evaluasi pembelajaran standar dengan mudah.
                </p>

                <!-- Role badges -->
                <div class="auth-brand-roles">
                    <div class="auth-brand-avatars">
                        <div class="avatar">SW</div>
                        <div class="avatar">GR</div>
                        <div class="avatar">OT</div>
                        <div class="avatar">AD</div>
                    </div>
                    <span>Multi-role login dashboard</span>
                </div>
            </div>

            <!-- Footer -->
            <div class="auth-brand-footer">
                &copy; 2026 EduMonitor. Semua Hak Dilindungi.
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="auth-form-panel">
            <div class="auth-form-card">

                <!-- Heading -->
                <div class="form-heading">
                    <h3>Selamat Datang Kembali!</h3>
                    <p>Masuk untuk melihat perkembangan belajar anak atau mengisi evaluasi.</p>
                </div>

                <!-- Error Alert -->
                @if($errors->any())
                    <div class="auth-alert-error">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <!-- Login Form -->
                <form action="{{ url('/login') }}" method="POST" class="auth-form" id="login-form">
                    @csrf

                    <!-- Username / Email -->
                    <div class="auth-input-group">
                        <label for="login_id">Username atau Email</label>
                        <div class="auth-input-wrapper">
                            <input type="text"
                                   id="login_id"
                                   name="login_id"
                                   value="{{ old('login_id') }}"
                                   placeholder="Contoh: clara atau clara@edumonitor.sch.id"
                                   required
                                   autocomplete="username">
                            <i class="fa-regular fa-user input-icon"></i>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="auth-input-group">
                        <label for="password">Password</label>
                        <div class="auth-input-wrapper">
                            <input type="password"
                                   id="password"
                                   name="password"
                                   placeholder="Masukkan password Anda"
                                   required
                                   autocomplete="current-password">
                            <i class="fa-solid fa-lock input-icon"></i>
                            <button type="button" class="toggle-password" id="btn-toggle-password" aria-label="Toggle password visibility">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Remember me -->
                    <div class="auth-options-row">
                        <label class="auth-remember">
                            <input type="checkbox" name="remember">
                            <span>Ingat Saya</span>
                        </label>
                    </div>

                    <!-- Submit -->
                    <button type="submit" id="btn-login-submit" class="auth-submit-btn">
                        <span>MASUK KE SISTEM</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </form>

            </div>
        </div>

    </div>

    <!-- Login Page JS -->
    <script>
        // Toggle password visibility
        const toggleBtn = document.getElementById('btn-toggle-password');
        const passwordInput = document.getElementById('password');

        if (toggleBtn && passwordInput) {
            toggleBtn.addEventListener('click', function () {
                const isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';
                this.querySelector('i').className = isPassword
                    ? 'fa-regular fa-eye-slash'
                    : 'fa-regular fa-eye';
            });
        }

        // Loading state on submit
        const loginForm = document.getElementById('login-form');
        const submitBtn = document.getElementById('btn-login-submit');

        if (loginForm && submitBtn) {
            loginForm.addEventListener('submit', function () {
                submitBtn.classList.add('loading');
                submitBtn.querySelector('span').textContent = 'MEMPROSES...';
            });
        }
    </script>

</body>
</html>