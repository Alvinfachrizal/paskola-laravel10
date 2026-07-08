<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Paskola') }}</title>

        <!-- Fonts: Inter -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        <!-- Bootstrap 5 CSS CDN -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Bootstrap Icons -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

        <style>
            :root {
                --primary-color: #0d6efd; /* Modern blue */
                --primary-bg: #eff6ff; /* Light blue bg for active items */
                --sidebar-width: 260px;
                --bg-color: #f8fafc;
                --text-main: #1e293b;
                --text-muted: #64748b;
                --border-color: #e2e8f0;
            }

            body {
                font-family: 'Inter', sans-serif;
                background-color: var(--bg-color);
                color: var(--text-main);
                overflow-x: hidden;
            }

            /* Layout wrapper */
            #app-wrapper {
                display: flex;
                min-height: 100vh;
                position: relative;
            }

            /* Sidebar */
            #sidebar {
                width: var(--sidebar-width);
                background-color: #ffffff;
                border-right: 1px solid var(--border-color);
                position: fixed;
                top: 0;
                bottom: 0;
                left: 0;
                z-index: 1000;
                overflow-y: auto;
                padding-bottom: 2rem;
                transition: transform 0.3s ease;
            }
            
            /* Sidebar Backdrop for Mobile */
            #sidebar-backdrop {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0,0,0,0.5);
                z-index: 999;
                backdrop-filter: blur(2px);
                opacity: 0;
                transition: opacity 0.3s ease;
            }

            /* Main Content Area */
            #main-content {
                flex-grow: 1;
                margin-left: var(--sidebar-width);
                display: flex;
                flex-direction: column;
                min-width: 0;
                transition: margin-left 0.3s ease;
            }

            /* Topbar */
            .topbar {
                background-color: #ffffff;
                height: 70px;
                border-bottom: 1px solid var(--border-color);
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 0 1.5rem;
                position: sticky;
                top: 0;
                z-index: 900;
            }

            .search-bar {
                background-color: #f1f5f9;
                border: none;
                border-radius: 2rem;
                padding: 0.5rem 1.2rem;
                font-size: 0.875rem;
                width: 300px;
                transition: all 0.3s ease;
            }
            .search-bar:focus {
                outline: none;
                box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.2);
                width: 350px;
            }

            /* Cards */
            .card {
                border: 1px solid var(--border-color);
                border-radius: 12px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.02);
                background-color: #ffffff;
            }
            
            /* Buttons */
            .btn-primary {
                background-color: var(--primary-color);
                border: none;
                border-radius: 8px;
                font-weight: 500;
                padding: 0.5rem 1.25rem;
            }

            /* Tables */
            .table-responsive {
                border: 1px solid var(--border-color);
                border-radius: 12px;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            .table {
                margin-bottom: 0;
                color: var(--text-main);
                white-space: nowrap; /* Prevents table rows from squishing on mobile */
            }
            .table th {
                background-color: #f8fafc;
                color: var(--text-muted);
                font-weight: 600;
                font-size: 0.75rem;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                border-bottom: 1px solid var(--border-color);
                padding: 1rem;
            }
            .table td {
                padding: 1rem;
                vertical-align: middle;
                border-bottom: 1px solid var(--border-color);
                font-size: 0.875rem;
            }
            
            /* Stats Cards */
            .stat-card {
                border-radius: 12px;
                border: 1px solid var(--border-color);
                background: #fff;
                padding: 1.25rem;
                display: flex;
                align-items: center;
                gap: 1rem;
            }
            .stat-icon {
                width: 48px;
                height: 48px;
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.5rem;
            }

            /* Status Badges */
            .badge {
                padding: 0.4em 0.8em;
                border-radius: 6px;
                font-weight: 500;
            }
            .bg-success-subtle { background-color: #dcfce7 !important; color: #166534 !important; }
            .bg-danger-subtle { background-color: #fee2e2 !important; color: #991b1b !important; }
            .bg-warning-subtle { background-color: #fef3c7 !important; color: #92400e !important; }
            
            /* Page Header */
            .page-header h2 {
                font-weight: 700;
                color: var(--text-main);
                font-size: 1.5rem;
                margin-bottom: 0.25rem;
            }
            .page-header p {
                color: var(--text-muted);
                font-size: 0.875rem;
            }

            @media (max-width: 991.98px) {
                #sidebar {
                    transform: translateX(-100%);
                }
                #sidebar.show {
                    transform: translateX(0);
                }
                #sidebar-backdrop.show {
                    display: block;
                    opacity: 1;
                }
                #main-content {
                    margin-left: 0;
                }
                .topbar {
                    padding: 0 1rem;
                }
                main.container-fluid {
                    padding: 1.25rem !important;
                }

                /* Mobile Bottom Nav for All */
                .bottom-nav {
                    display: flex !important;
                }
                #main-content {
                    padding-bottom: 70px; /* Space for bottom nav */
                }
            }

            /* Bottom Nav Styles */
            .bottom-nav {
                display: none;
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                background-color: #ffffff;
                border-top: 1px solid var(--border-color);
                z-index: 1000;
                box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
                padding-bottom: env(safe-area-inset-bottom);
            }
            .bottom-nav-item {
                flex: 1;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 0.5rem 0;
                color: var(--text-muted);
                text-decoration: none;
                font-size: 0.7rem;
                font-weight: 500;
                transition: all 0.2s ease;
            }
            .bottom-nav-item i {
                font-size: 1.25rem;
                margin-bottom: 2px;
            }
            .bottom-nav-item.active {
                color: var(--primary-color);
            }
        </style>
    </head>
    <body>
        <div id="app-wrapper">
            <!-- Sidebar -->
            @include('layouts.navigation-bootstrap')

            <!-- Sidebar Backdrop -->
            <div id="sidebar-backdrop"></div>

            <!-- Main Content -->
            <div id="main-content">
                <!-- Topbar -->
                <div class="topbar">
                    <div class="d-flex align-items-center">
                        <button class="btn btn-light d-lg-none me-3" type="button" id="sidebarToggle">
                            <i class="bi bi-list fs-5"></i>
                        </button>
                        <div class="d-none d-md-block">
                            <input type="text" class="search-bar" placeholder="Cari data...">
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center gap-2 gap-md-3">
                        <button class="btn btn-light rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                            <i class="bi bi-bell"></i>
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-light border-0 d-flex align-items-center gap-2 p-1 p-md-2" type="button" data-bs-toggle="dropdown">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-weight:600;">
                                    {{ substr(Auth::user()->name, 0, 2) }}
                                </div>
                                <div class="text-start d-none d-md-block pe-2">
                                    <div class="fw-semibold" style="font-size:0.875rem;line-height:1;">{{ Auth::user()->name }}</div>
                                    <small class="text-muted" style="font-size:0.75rem;">{{ Auth::user()->role }}</small>
                                </div>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i>Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <a class="dropdown-item text-danger" href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">
                                            <i class="bi bi-box-arrow-right me-2"></i>Log Out
                                        </a>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Page Content -->
                <main class="container-fluid p-4 p-lg-5">
                    @if(session()->has('impersonated_by'))
                        <div class="alert alert-warning d-flex align-items-center justify-content-between rounded-4 shadow-sm mb-4 border-warning" role="alert">
                            <div>
                                <i class="bi bi-exclamation-triangle-fill me-2 text-warning"></i>
                                Anda sedang login sebagai <strong>{{ Auth::user()->name }}</strong>. 
                            </div>
                            <form action="{{ route('admin.users.stop-impersonate') }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-dark rounded-pill px-3">
                                    <i class="bi bi-box-arrow-left me-1"></i> Kembali ke Akun Asli
                                </button>
                            </form>
                        </div>
                    @endif

                    @hasSection('header')
                        <!-- Responsive page header container -->
                        <div class="page-header mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div class="flex-grow-1">
                                @yield('header')
                            </div>
                            <div>
                                @yield('header_action')
                            </div>
                        </div>
                    @endif

                    @yield('content')
                    {{ $slot ?? '' }}
                </main>
            </div>
        </div>

        <!-- Bootstrap 5 JS CDN -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const sidebarToggle = document.getElementById('sidebarToggle');
                const sidebar = document.getElementById('sidebar');
                const backdrop = document.getElementById('sidebar-backdrop');
                
                function toggleSidebar() {
                    sidebar.classList.toggle('show');
                    backdrop.classList.toggle('show');
                    if (sidebar.classList.contains('show')) {
                        document.body.style.overflow = 'hidden'; // Prevent scrolling when sidebar is open on mobile
                    } else {
                        document.body.style.overflow = '';
                    }
                }

                if(sidebarToggle && sidebar && backdrop) {
                    sidebarToggle.addEventListener('click', toggleSidebar);
                    backdrop.addEventListener('click', toggleSidebar);
                }
            });
        </script>
        @if(Auth::check())
        <!-- Bottom Navigation for All Roles (Mobile) -->
        <div class="bottom-nav d-lg-none">
            <a href="{{ route('dashboard') }}" class="bottom-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi {{ request()->routeIs('dashboard') ? 'bi-house-fill' : 'bi-house' }}"></i>
                <span>Beranda</span>
            </a>

            @if(Auth::user()->hasRole(['Super Admin', 'Admin', 'Kepala Sekolah']))
                <a href="{{ route('admin.students.index') }}" class="bottom-nav-item {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
                    <i class="bi {{ request()->routeIs('admin.students.*') ? 'bi-people-fill' : 'bi-people' }}"></i>
                    <span>Siswa</span>
                </a>
                <a href="{{ route('admin.teachers.index') }}" class="bottom-nav-item {{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}">
                    <i class="bi {{ request()->routeIs('admin.teachers.*') ? 'bi-person-badge-fill' : 'bi-person-badge' }}"></i>
                    <span>Guru</span>
                </a>
            @else
                <a href="{{ route('admin.lms-materials.index') }}" class="bottom-nav-item {{ request()->routeIs('admin.lms-*') ? 'active' : '' }}">
                    <i class="bi {{ request()->routeIs('admin.lms-*') ? 'bi-book-fill' : 'bi-book' }}"></i>
                    <span>LMS</span>
                </a>
                <a href="#" class="bottom-nav-item">
                    <i class="bi bi-card-checklist"></i>
                    <span>Tugas</span>
                </a>
            @endif

            <a href="{{ route('profile.edit') }}" class="bottom-nav-item {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
                <i class="bi {{ request()->routeIs('profile.edit') ? 'bi-person-fill' : 'bi-person' }}"></i>
                <span>Profil</span>
            </a>
        </div>
        @endif
    </body>
</html>
