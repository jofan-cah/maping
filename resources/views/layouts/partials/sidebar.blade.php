{{-- File: resources/views/layouts/sidebar.blade.php --}}
<div class="sidebar">
    <div class="sidebar-header">
        <h4>{{ config('app.name') }}</h4>
        <small>{{ auth()->user()->level_name ?? 'User' }}</small>
    </div>

    <nav class="sidebar-nav">
        <ul class="nav flex-column">
            {{-- Dashboard - Always visible for authenticated users --}}
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                   href="{{ route('dashboard') }}">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
            </li>

            {{-- Users Management --}}
            @if(auth()->user()->hasPermission('users.view'))\
            @dd(auth()->user()->hasPermission('users.view'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"
                   href="#"
                   data-bs-toggle="collapse"
                   data-bs-target="#usersMenu"
                   aria-expanded="{{ request()->routeIs('users.*') ? 'true' : 'false' }}">
                    <i class="fas fa-users"></i>
                    Users Management
                    <i class="fas fa-chevron-down ms-auto"></i>
                </a>
                <div class="collapse {{ request()->routeIs('users.*') ? 'show' : '' }}" id="usersMenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('users.index') ? 'active' : '' }}"
                               href="{{ route('users.index') }}">
                                <i class="fas fa-list"></i>
                                Daftar Users
                            </a>
                        </li>
                        @if(auth()->user()->hasPermission('users.create'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('users.create') ? 'active' : '' }}"
                               href="{{ route('users.create') }}">
                                <i class="fas fa-plus"></i>
                                Tambah User
                            </a>
                        </li>
                        @endif
                        @if(auth()->user()->hasPermission('users.statistics'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('users.statistics') ? 'active' : '' }}"
                               href="{{ route('users.statistics') }}">
                                <i class="fas fa-chart-bar"></i>
                                Statistik Users
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
            </li>
            @endif

            {{-- User Levels Management --}}
            @if(auth()->user()->hasPermission('user_levels.view'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('user-levels.*') ? 'active' : '' }}"
                   href="#"
                   data-bs-toggle="collapse"
                   data-bs-target="#levelsMenu"
                   aria-expanded="{{ request()->routeIs('user-levels.*') ? 'true' : 'false' }}">
                    <i class="fas fa-user-tag"></i>
                    User Levels
                    <i class="fas fa-chevron-down ms-auto"></i>
                </a>
                <div class="collapse {{ request()->routeIs('user-levels.*') ? 'show' : '' }}" id="levelsMenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('user-levels.index') ? 'active' : '' }}"
                               href="{{ route('user-levels.index') }}">
                                <i class="fas fa-list"></i>
                                Daftar Levels
                            </a>
                        </li>
                        @if(auth()->user()->hasPermission('user_levels.create'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('user-levels.create') ? 'active' : '' }}"
                               href="{{ route('user-levels.create') }}">
                                <i class="fas fa-plus"></i>
                                Tambah Level
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
            </li>
            @endif

            {{-- Mitra Management --}}
            @if(auth()->user()->hasPermission('mitras.view'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('mitras.*') ? 'active' : '' }}"
                   href="#"
                   data-bs-toggle="collapse"
                   data-bs-target="#mitrasMenu"
                   aria-expanded="{{ request()->routeIs('mitras.*') ? 'true' : 'false' }}">
                    <i class="fas fa-handshake"></i>
                    Mitra Management
                    <i class="fas fa-chevron-down ms-auto"></i>
                </a>
                <div class="collapse {{ request()->routeIs('mitras.*') ? 'show' : '' }}" id="mitrasMenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('mitras.index') ? 'active' : '' }}"
                               href="{{ route('mitras.index') }}">
                                <i class="fas fa-list"></i>
                                Daftar Mitra
                            </a>
                        </li>
                        @if(auth()->user()->hasPermission('mitras.create'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('mitras.create') ? 'active' : '' }}"
                               href="{{ route('mitras.create') }}">
                                <i class="fas fa-plus"></i>
                                Tambah Mitra
                            </a>
                        </li>
                        @endif
                        @if(auth()->user()->hasPermission('mitras.statistics'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('mitras.statistics') ? 'active' : '' }}"
                               href="{{ route('mitras.statistics') }}">
                                <i class="fas fa-chart-pie"></i>
                                Statistik Mitra
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
            </li>
            @endif

            {{-- Points Management --}}
            @if(auth()->user()->hasPermission('points.view'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('mitra-turunans.*') ? 'active' : '' }}"
                   href="#"
                   data-bs-toggle="collapse"
                   data-bs-target="#pointsMenu"
                   aria-expanded="{{ request()->routeIs('mitra-turunans.*') ? 'true' : 'false' }}">
                    <i class="fas fa-map-marker-alt"></i>
                    Points Management
                    <i class="fas fa-chevron-down ms-auto"></i>
                </a>
                <div class="collapse {{ request()->routeIs('mitra-turunans.*') ? 'show' : '' }}" id="pointsMenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('mitra-turunans.index') ? 'active' : '' }}"
                               href="{{ route('mitra-turunans.index') }}">
                                <i class="fas fa-list"></i>
                                Daftar Points
                            </a>
                        </li>
                        @if(auth()->user()->hasPermission('points.create'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('mitra-turunans.create') ? 'active' : '' }}"
                               href="{{ route('mitra-turunans.create') }}">
                                <i class="fas fa-plus"></i>
                                Tambah Point
                            </a>
                        </li>
                        @endif
                        @if(auth()->user()->hasPermission('points.upload_kmz'))
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="$('#uploadKmzModal').modal('show')">
                                <i class="fas fa-upload"></i>
                                Upload KMZ
                            </a>
                        </li>
                        @endif
                        @if(auth()->user()->hasPermission('points.statistics'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('mitra-turunans.statistics') ? 'active' : '' }}"
                               href="{{ route('mitra-turunans.statistics') }}">
                                <i class="fas fa-chart-line"></i>
                                Statistik Points
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
            </li>
            @endif

            {{-- Maps --}}
            @if(auth()->user()->hasPermission('maps.view'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('maps.*') ? 'active' : '' }}"
                   href="{{ route('maps.index') }}">
                    <i class="fas fa-map"></i>
                    Maps
                </a>
            </li>
            @endif

            {{-- Coverage Analysis --}}
            @if(auth()->user()->hasPermission('coverage.view'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('coverage.*') ? 'active' : '' }}"
                   href="{{ route('coverage.index') }}">
                    <i class="fas fa-crosshairs"></i>
                    Coverage Analysis
                </a>
            </li>
            @endif

            {{-- Reports --}}
            @if(auth()->user()->hasAnyPermission(['reports.view', 'reports.analytics']))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}"
                   href="#"
                   data-bs-toggle="collapse"
                   data-bs-target="#reportsMenu"
                   aria-expanded="{{ request()->routeIs('reports.*') ? 'true' : 'false' }}">
                    <i class="fas fa-chart-line"></i>
                    Reports
                    <i class="fas fa-chevron-down ms-auto"></i>
                </a>
                <div class="collapse {{ request()->routeIs('reports.*') ? 'show' : '' }}" id="reportsMenu">
                    <ul class="nav flex-column ms-3">
                        @if(auth()->user()->hasPermission('reports.view'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('reports.dashboard') ? 'active' : '' }}"
                               href="{{ route('reports.dashboard') }}">
                                <i class="fas fa-tachometer-alt"></i>
                                Dashboard
                            </a>
                        </li>
                        @endif
                        @if(auth()->user()->hasPermission('reports.analytics'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('reports.analytics') ? 'active' : '' }}"
                               href="{{ route('reports.analytics') }}">
                                <i class="fas fa-chart-bar"></i>
                                Analytics
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
            </li>
            @endif

            {{-- System Settings (Super Admin & Admin only) --}}
            @if(auth()->user()->hasAnyPermission(['settings.view', 'settings.edit', 'system.logs']))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('settings.*', 'system.*') ? 'active' : '' }}"
                   href="#"
                   data-bs-toggle="collapse"
                   data-bs-target="#settingsMenu"
                   aria-expanded="{{ request()->routeIs('settings.*', 'system.*') ? 'true' : 'false' }}">
                    <i class="fas fa-cog"></i>
                    System Settings
                    <i class="fas fa-chevron-down ms-auto"></i>
                </a>
                <div class="collapse {{ request()->routeIs('settings.*', 'system.*') ? 'show' : '' }}" id="settingsMenu">
                    <ul class="nav flex-column ms-3">
                        @if(auth()->user()->hasPermission('settings.view'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('settings.general') ? 'active' : '' }}"
                               href="{{ route('settings.general') }}">
                                <i class="fas fa-wrench"></i>
                                General Settings
                            </a>
                        </li>
                        @endif
                        @if(auth()->user()->hasPermission('settings.system'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('settings.system') ? 'active' : '' }}"
                               href="{{ route('settings.system') }}">
                                <i class="fas fa-server"></i>
                                System Settings
                            </a>
                        </li>
                        @endif
                        @if(auth()->user()->hasPermission('system.logs'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('system.logs') ? 'active' : '' }}"
                               href="{{ route('system.logs') }}">
                                <i class="fas fa-file-alt"></i>
                                System Logs
                            </a>
                        </li>
                        @endif
                        @if(auth()->user()->hasPermission('system.monitoring'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('system.monitoring') ? 'active' : '' }}"
                               href="{{ route('system.monitoring') }}">
                                <i class="fas fa-heartbeat"></i>
                                System Monitoring
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
            </li>
            @endif

            {{-- Divider --}}
            <li class="nav-item">
                <hr class="sidebar-divider my-3">
            </li>

            {{-- User Profile Info --}}
            <li class="nav-item">
                <div class="sidebar-user-info p-3">
                    <div class="d-flex align-items-center">
                        <img src="{{ auth()->user()->profile_picture_url }}"
                             alt="Profile"
                             class="rounded-circle me-2"
                             width="32" height="32">
                        <div class="flex-grow-1">
                            <div class="fw-bold text-truncate">{{ auth()->user()->name }}</div>
                            <small class="text-muted">{{ auth()->user()->level_name }}</small>
                        </div>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted d-block">
                            <i class="fas fa-circle text-success me-1"></i>
                            {{ auth()->user()->status_text }}
                        </small>
                        @if(auth()->user()->last_login_at)
                        <small class="text-muted d-block">
                            Last Login: {{ auth()->user()->last_login_at->diffForHumans() }}
                        </small>
                        @endif
                    </div>
                </div>
            </li>

            {{-- Logout --}}
            <li class="nav-item">
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="nav-link btn btn-link text-start w-100 border-0">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </button>
                </form>
            </li>
        </ul>
    </nav>
</div>

{{-- CSS untuk styling sidebar --}}
<style>
.sidebar {
    min-height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    box-shadow: 2px 0 10px rgba(0,0,0,0.1);
}

.sidebar-header {
    padding: 1.5rem 1rem;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    text-align: center;
    color: white;
}

.sidebar-header h4 {
    margin: 0;
    font-weight: 600;
}

.sidebar-nav {
    padding: 1rem 0;
}

.sidebar .nav-link {
    color: rgba(255,255,255,0.9);
    padding: 0.75rem 1.5rem;
    border: none;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    text-decoration: none;
}

.sidebar .nav-link:hover {
    background: rgba(255,255,255,0.1);
    color: white;
    transform: translateX(3px);
}

.sidebar .nav-link.active {
    background: rgba(255,255,255,0.2);
    color: white;
    border-left: 3px solid #ffd700;
}

.sidebar .nav-link i {
    width: 20px;
    margin-right: 10px;
}

.sidebar .collapse .nav-link {
    padding-left: 3rem;
    font-size: 0.9rem;
    color: rgba(255,255,255,0.8);
}

.sidebar .collapse .nav-link:hover {
    color: white;
    background: rgba(255,255,255,0.05);
}

.sidebar .collapse .nav-link.active {
    background: rgba(255,255,255,0.15);
    color: white;
}

.sidebar-divider {
    border-color: rgba(255,255,255,0.2);
    margin: 1rem 0;
}

.sidebar-user-info {
    background: rgba(255,255,255,0.1);
    border-radius: 10px;
    margin: 0 1rem;
    color: white;
}

.sidebar .btn-link {
    color: rgba(255,255,255,0.9) !important;
    text-decoration: none;
}

.sidebar .btn-link:hover {
    background: rgba(255,255,255,0.1);
    color: white !important;
}

/* Responsive */
@media (max-width: 768px) {
    .sidebar {
        position: fixed;
        top: 0;
        left: -250px;
        width: 250px;
        height: 100vh;
        z-index: 1050;
        transition: left 0.3s ease;
    }

    .sidebar.show {
        left: 0;
    }
}
</style>

{{-- JavaScript untuk collapse menu --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto expand active menu
    const activeLinks = document.querySelectorAll('.sidebar .nav-link.active');
    activeLinks.forEach(link => {
        const collapseTarget = link.getAttribute('data-bs-target');
        if (collapseTarget) {
            const collapseElement = document.querySelector(collapseTarget);
            if (collapseElement) {
                collapseElement.classList.add('show');
                link.setAttribute('aria-expanded', 'true');
            }
        }
    });

    // Handle collapse toggle
    const toggles = document.querySelectorAll('[data-bs-toggle="collapse"]');
    toggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const target = this.getAttribute('data-bs-target');
            const collapseElement = document.querySelector(target);

            if (collapseElement) {
                const isExpanded = collapseElement.classList.contains('show');

                // Close all other collapsed menus
                document.querySelectorAll('.sidebar .collapse.show').forEach(el => {
                    if (el !== collapseElement) {
                        el.classList.remove('show');
                    }
                });

                // Toggle current menu
                if (isExpanded) {
                    collapseElement.classList.remove('show');
                    this.setAttribute('aria-expanded', 'false');
                } else {
                    collapseElement.classList.add('show');
                    this.setAttribute('aria-expanded', 'true');
                }
            }
        });
    });
});
</script>
