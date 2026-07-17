<aside id="sidebar">
    <div class="p-4 d-flex align-items-center gap-3 border-bottom">
        <div class="bg-primary text-white rounded d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
            <i class="bi bi-shield-lock-fill fs-5"></i>
        </div>
        <div>
            <h6 class="mb-0 fw-bold" style="font-size:0.9rem;">SMA N 1 Contoh</h6>
            <small class="text-muted" style="font-size:0.75rem;">Sistem Informasi</small>
        </div>
    </div>
    
    <div class="p-3">
        <!-- Sidebar Profile info is in Topbar now, but let's add a small pill to match the design if needed -->
        <div class="d-flex align-items-center gap-2 p-2 mb-4 rounded-3" style="background-color: #eff6ff; border: 1px solid #dbeafe;">
            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-weight:600; font-size:0.75rem;">
                {{ substr(Auth::user()->name, 0, 2) }}
            </div>
            <div class="text-start text-truncate">
                <div class="fw-semibold text-primary text-truncate" style="font-size:0.8rem;line-height:1;max-width:140px;" title="{{ Auth::user()->name }}">{{ explode(' ', Auth::user()->name)[0] }} {{ count(explode(' ', Auth::user()->name)) > 1 ? substr(explode(' ', Auth::user()->name)[1], 0, 1) . '.' : '' }}</div>
                <small class="text-primary opacity-75 text-truncate" style="font-size:0.7rem;max-width:140px;">{{ Auth::user()->roles->first()?->name ?? 'User' }}</small>
            </div>
        </div>

        <style>
            .nav-group-title {
                font-size: 0.7rem;
                text-transform: uppercase;
                letter-spacing: 0.1em;
                color: #94a3b8;
                font-weight: 600;
                margin-top: 1.5rem;
                margin-bottom: 0.5rem;
                padding-left: 0.75rem;
            }
            .sidebar-link {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                padding: 0.75rem 1rem;
                color: #64748b;
                text-decoration: none;
                border-radius: 0.5rem;
                font-size: 0.875rem;
                font-weight: 500;
                transition: all 0.2s;
                margin-bottom: 0.25rem;
            }
            .sidebar-link:hover {
                background-color: #f1f5f9;
                color: #0f172a;
            }
            .sidebar-link.active {
                background-color: var(--primary-bg);
                color: var(--primary-color);
            }
            .sidebar-link i {
                font-size: 1.1rem;
            }
        </style>

        <div class="nav-group-title">Utama</div>
        <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2"></i> Dashboard
        </a>

        @if (Auth::user()->hasRole(['Super Admin', 'Admin']))
            <div class="nav-group-title">Administrasi</div>
            <a href="{{ route('admin.students.index') }}" class="sidebar-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
                <i class="bi bi-person-badge"></i> Data Siswa
            </a>
            <a href="{{ route('admin.teachers.index') }}" class="sidebar-link {{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}">
                <i class="bi bi-person-workspace"></i> Data Guru
            </a>
            <a href="{{ route('admin.classes.index') }}" class="sidebar-link {{ request()->routeIs('admin.classes.*') ? 'active' : '' }}">
                <i class="bi bi-building"></i> Kelas & Mapel
            </a>
            <a href="{{ route('admin.school-years.index') }}" class="sidebar-link {{ request()->routeIs('admin.school-years.*') ? 'active' : '' }}">
                <i class="bi bi-calendar3"></i> Tahun Ajaran
            </a>
            <a href="{{ route('admin.users.index') }}" class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> Pengguna
            </a>
            
            <div class="nav-group-title">Akademik</div>
            <a href="{{ route('admin.lms-materials.index') }}" class="sidebar-link {{ request()->routeIs('admin.lms-materials.*') ? 'active' : '' }}">
                <i class="bi bi-book"></i> Materi & LMS
            </a>
            <a href="{{ route('admin.lms-assignments.index') }}" class="sidebar-link {{ request()->routeIs('admin.lms-assignments.*') ? 'active' : '' }}">
                <i class="bi bi-journal-check"></i> Tugas & Ujian
            </a>
            <a href="#" class="sidebar-link">
                <i class="bi bi-calendar-check"></i> Absensi
            </a>
            <a href="{{ route('grades.input.index') }}" class="sidebar-link {{ request()->routeIs('grades.input.*', 'grades.weights.*') ? 'active' : '' }}">
                <i class="bi bi-pencil-square"></i> Input Nilai
            </a>
            <a href="{{ route('grades.report-cards.index') }}" class="sidebar-link {{ request()->routeIs('grades.report-cards.*') ? 'active' : '' }}">
                <i class="bi bi-star"></i> Nilai &amp; Rapor
            </a>

            <div class="nav-group-title">Lainnya</div>
            <a href="{{ route('admin.ppdb.index') }}" class="sidebar-link {{ request()->routeIs('admin.ppdb.*') ? 'active' : '' }}">
                <i class="bi bi-clipboard-check"></i> PPDB Online
            </a>
            <a href="#" class="sidebar-link">
                <i class="bi bi-wallet2"></i> Keuangan
            </a>
            <a href="#" class="sidebar-link">
                <i class="bi bi-megaphone"></i> Pengumuman
            </a>
            <a href="#" class="sidebar-link">
                <i class="bi bi-gear"></i> Pengaturan
            </a>
        @endif

        @if (Auth::user()->hasRole('Kepala Sekolah'))
            <div class="nav-group-title">Administrasi</div>
            <a href="{{ route('admin.students.index') }}" class="sidebar-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
                <i class="bi bi-person-badge"></i> Data Siswa
            </a>
            <a href="{{ route('admin.teachers.index') }}" class="sidebar-link {{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}">
                <i class="bi bi-person-workspace"></i> Data Guru
            </a>
            <a href="{{ route('admin.classes.index') }}" class="sidebar-link {{ request()->routeIs('admin.classes.*') ? 'active' : '' }}">
                <i class="bi bi-building"></i> Kelas & Mapel
            </a>
            
            <div class="nav-group-title">Akademik</div>
            <a href="#" class="sidebar-link">
                <i class="bi bi-calendar-check"></i> Absensi
            </a>
            <a href="{{ route('grades.input.index') }}" class="sidebar-link {{ request()->routeIs('grades.input.*', 'grades.weights.*') ? 'active' : '' }}">
                <i class="bi bi-pencil-square"></i> Input Nilai
            </a>
            <a href="{{ route('grades.report-cards.index') }}" class="sidebar-link {{ request()->routeIs('grades.report-cards.*') ? 'active' : '' }}">
                <i class="bi bi-star"></i> Nilai &amp; Rapor
            </a>

            <div class="nav-group-title">Lainnya</div>
            <a href="#" class="sidebar-link">
                <i class="bi bi-megaphone"></i> Pengumuman
            </a>
            <a href="#" class="sidebar-link">
                <i class="bi bi-gear"></i> Pengaturan
            </a>
        @endif

        @if (Auth::user()->hasRole('Guru'))
            <div class="nav-group-title">Administrasi</div>
            <a href="{{ route('admin.students.index') }}" class="sidebar-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
                <i class="bi bi-person-badge"></i> Data Siswa
            </a>

            <div class="nav-group-title">Akademik</div>
            <a href="{{ route('admin.lms-materials.index') }}" class="sidebar-link {{ request()->routeIs('admin.lms-materials.*') ? 'active' : '' }}">
                <i class="bi bi-book"></i> Materi Pembelajaran
            </a>
            <a href="{{ route('admin.lms-assignments.index') }}" class="sidebar-link {{ request()->routeIs('admin.lms-assignments.*') ? 'active' : '' }}">
                <i class="bi bi-journal-check"></i> Tugas & Ujian
            </a>
            <a href="#" class="sidebar-link">
                <i class="bi bi-calendar-check"></i> Absensi
            </a>
            <a href="#" class="sidebar-link">
                <i class="bi bi-star"></i> Nilai & Rapor
            </a>

            <div class="nav-group-title">Lainnya</div>
            <a href="#" class="sidebar-link">
                <i class="bi bi-megaphone"></i> Pengumuman
            </a>
        @endif

        @if (Auth::user()->hasRole('Siswa'))
            <div class="nav-group-title">Akademik</div>
            <a href="{{ route('admin.lms-materials.index') }}" class="sidebar-link {{ request()->routeIs('admin.lms-materials.*') ? 'active' : '' }}">
                <i class="bi bi-book"></i> Materi Pembelajaran
            </a>
            <a href="{{ route('admin.lms-assignments.index') }}" class="sidebar-link {{ request()->routeIs('admin.lms-assignments.*') ? 'active' : '' }}">
                <i class="bi bi-journal-check"></i> Tugas & Ujian
            </a>
            <a href="#" class="sidebar-link">
                <i class="bi bi-calendar-check"></i> Absensi
            </a>
            <a href="#" class="sidebar-link">
                <i class="bi bi-star"></i> Nilai & Rapor
            </a>

            <div class="nav-group-title">Lainnya</div>
            <a href="#" class="sidebar-link">
                <i class="bi bi-megaphone"></i> Pengumuman
            </a>
        @endif

        @if (Auth::user()->hasRole('Ortu'))
            <div class="nav-group-title">Akademik</div>
            <a href="#" class="sidebar-link">
                <i class="bi bi-calendar-check"></i> Absensi
            </a>
            <a href="#" class="sidebar-link">
                <i class="bi bi-star"></i> Nilai & Rapor
            </a>

            <div class="nav-group-title">Lainnya</div>
            <a href="#" class="sidebar-link">
                <i class="bi bi-megaphone"></i> Pengumuman
            </a>
        @endif
    </div>
</aside>
