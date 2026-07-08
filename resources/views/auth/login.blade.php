<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | SIMS Sekolah</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
        }
        .bg-login-left {
            background-color: #3b82f6; /* Base blue */
            background-image: 
                radial-gradient(circle at 15% 50%, rgba(255,255,255,0.08) 0%, transparent 50%),
                radial-gradient(circle at 85% 80%, rgba(255,255,255,0.08) 0%, transparent 50%);
        }
        .feature-card {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            color: white;
            margin-bottom: 0.75rem;
        }
        .feature-icon {
            background: rgba(255, 255, 255, 0.2);
            width: 40px; height: 40px;
            display: flex; justify-content: center; align-items: center;
            border-radius: 8px;
            font-size: 1.25rem;
        }
    </style>
</head>
<body class="flex flex-col md:flex-row h-screen w-full bg-[#f8fafc] overflow-hidden">
    
    <!-- Left Side (Blue Branding) -->
    <div class="hidden md:flex flex-col justify-between w-5/12 lg:w-4/12 bg-login-left text-white p-10 lg:p-12 relative">
        <!-- Logo -->
        <div class="flex items-center gap-3 z-10">
            <div class="bg-white/20 p-2 rounded-lg backdrop-blur-sm">
                <i class="bi bi-shield-lock-fill text-2xl"></i>
            </div>
            <div>
                <h1 class="font-bold text-lg leading-tight">SMA Negeri 1 Contoh</h1>
                <p class="text-white/80 text-xs">Sistem Informasi Sekolah</p>
            </div>
        </div>

        <!-- Middle Content -->
        <div class="z-10 mt-12 mb-8">
            <h2 class="text-4xl lg:text-5xl font-bold mb-4 leading-tight">Platform Digital<br>Sekolah Modern</h2>
            <p class="text-white/80 mb-8 text-sm leading-relaxed max-w-sm">
                Kelola akademik dan administrasi sekolah secara efisien dalam satu platform terpadu.
            </p>

            <div class="space-y-3 pr-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="bi bi-laptop"></i></div>
                    <div>
                        <h4 class="font-semibold text-sm">LMS & E-Learning</h4>
                        <p class="text-white/70 text-xs">Materi, tugas & ujian online</p>
                    </div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="bi bi-people"></i></div>
                    <div>
                        <h4 class="font-semibold text-sm">Manajemen Terpusat</h4>
                        <p class="text-white/70 text-xs">Siswa, guru & kelas dalam satu sistem</p>
                    </div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="bi bi-bar-chart"></i></div>
                    <div>
                        <h4 class="font-semibold text-sm">Laporan Real-time</h4>
                        <p class="text-white/70 text-xs">Nilai, kehadiran & keuangan</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Stats -->
        <div class="z-10 flex gap-8 border-t border-white/20 pt-6">
            <div>
                <div class="text-2xl font-bold">6</div>
                <div class="text-white/70 text-xs">Role Pengguna</div>
            </div>
            <div>
                <div class="text-2xl font-bold">9+</div>
                <div class="text-white/70 text-xs">Modul Fitur</div>
            </div>
            <div>
                <div class="text-2xl font-bold">100%</div>
                <div class="text-white/70 text-xs">Data Aman</div>
            </div>
        </div>

        <!-- Decorative circles -->
        <div class="absolute bottom-10 right-10 w-32 h-32 bg-white/5 rounded-full blur-2xl"></div>
        <div class="absolute top-1/4 right-0 w-48 h-48 bg-blue-400/20 rounded-full blur-3xl"></div>
    </div>

    <!-- Right Side (Login Form) -->
    <div class="flex-1 flex flex-col justify-center items-center p-6 relative">
        <div class="w-full max-w-md bg-white p-8 md:p-10 rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 relative z-10">
            
            <!-- Mobile Logo (Visible only on small screens) -->
            <div class="md:hidden flex items-center gap-3 mb-8 justify-center">
                <div class="bg-blue-500 text-white p-2 rounded-lg">
                    <i class="bi bi-shield-lock-fill text-xl"></i>
                </div>
                <div>
                    <h1 class="font-bold text-gray-800">SMA N 1 Contoh</h1>
                </div>
            </div>

            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-1">Masuk ke Akun</h2>
                <p class="text-sm text-gray-500">Gunakan email dan password yang terdaftar</p>
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email -->
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" 
                           class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-gray-50 text-gray-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors"
                           placeholder="contoh@sekolah.id">
                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm text-red-500" />
                </div>

                <!-- Password -->
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-1">
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-xs text-blue-600 hover:text-blue-800 hover:underline">
                                Lupa password?
                            </a>
                        @endif
                    </div>
                    <div class="relative">
                        <input id="password" type="password" name="password" required autocomplete="current-password"
                               class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-gray-50 text-gray-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors"
                               placeholder="••••••••">
                        <button type="button" onclick="togglePassword()" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i id="toggleIcon" class="bi bi-eye"></i>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm text-red-500" />
                </div>
                
                <!-- Remember me -->
                <div class="mb-6 flex items-center">
                    <input id="remember_me" type="checkbox" name="remember" class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                    <label for="remember_me" class="ml-2 text-sm text-gray-600">Ingat saya</label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-3 rounded-lg transition-colors flex justify-center items-center gap-2 shadow-sm shadow-blue-500/30">
                    Masuk Sekarang <i class="bi bi-arrow-right"></i>
                </button>
            </form>

            <!-- Demo Accounts Section -->
            <div class="mt-8">
                <div class="relative flex items-center justify-center mb-4">
                    <div class="border-t border-gray-200 w-full absolute"></div>
                    <div class="bg-white px-4 text-xs text-gray-400 relative z-10">Demo Akun (klik untuk isi otomatis)</div>
                </div>
                <div class="grid grid-cols-3 gap-2 mb-2">
                    <button type="button" onclick="fillLogin('superadmin@paskola.com', 'password')" class="py-1.5 px-2 bg-blue-50 text-blue-600 border border-blue-100 rounded-md text-xs font-medium hover:bg-blue-100 transition-colors">Admin</button>
                    <button type="button" onclick="fillLogin('kepsek@paskola.com', 'password')" class="py-1.5 px-2 bg-blue-50 text-blue-600 border border-blue-100 rounded-md text-xs font-medium hover:bg-blue-100 transition-colors">Kepsek</button>
                    <button type="button" onclick="fillLogin('guru@paskola.com', 'password')" class="py-1.5 px-2 bg-blue-50 text-blue-600 border border-blue-100 rounded-md text-xs font-medium hover:bg-blue-100 transition-colors">Guru</button>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" onclick="fillLogin('siswa@paskola.com', 'password')" class="py-1.5 px-2 bg-blue-50 text-blue-600 border border-blue-100 rounded-md text-xs font-medium hover:bg-blue-100 transition-colors">Siswa</button>
                    <button type="button" onclick="fillLogin('ortu@paskola.com', 'password')" class="py-1.5 px-2 bg-blue-50 text-blue-600 border border-blue-100 rounded-md text-xs font-medium hover:bg-blue-100 transition-colors">Ortu</button>
                </div>
            </div>
        </div>

        <!-- Footer text -->
        <div class="absolute bottom-6 text-center w-full text-xs text-gray-400">
            &copy; 2026 SIMS - SMA Negeri 1 Contoh
        </div>
    </div>

    <!-- Script for demo accounts & show password -->
    <script>
        function fillLogin(email, password) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
        }

        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            }
        }
    </script>
</body>
</html>
