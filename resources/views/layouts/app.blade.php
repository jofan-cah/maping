<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - @yield('title', 'Dashboard')</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Tailwind Config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Additional Styles -->
    @stack('styles')
    @livewireStyles
</head>

<body class="bg-gray-100 font-sans antialiased" x-data="{ sidebarOpen: false }">

    <!-- Loading Overlay -->
    <div id="loading-overlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg p-6 flex items-center space-x-4">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
            <span class="text-gray-700">Loading...</span>
        </div>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 z-30 bg-black bg-opacity-50 lg:hidden"
        @click="sidebarOpen = false">
    </div>

    <div class="flex h-screen bg-gray-100">
        <!-- Sidebar -->
        <div class="fixed inset-y-0 left-0 z-40 w-64 overflow-y-auto transition duration-300 transform bg-white border-r border-gray-200 lg:translate-x-0 lg:static lg:inset-0"
            :class="{ 'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen }">

            <!-- Logo -->
            <div class="flex items-center justify-center h-24 bg-white border-b border-gray-200">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-primary-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-crown text-white"></i>
                    </div>
                    <span
                        class="ml-3 text-xl font-semibold text-gray-800">{{ config('app.name', 'Admin Panel') }}</span>
                </div>
            </div>

            <!-- Navigation Menu -->
            <nav class="mt-8 px-4">
                <div class="space-y-2">

                    <!-- Dashboard -->
                    <a href="{{ route('dashboard') }}"
                        class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('dashboard') ? 'text-primary-600 bg-primary-50' : 'text-gray-700 hover:bg-gray-100' }}">
                        <i
                            class="fas fa-home mr-3 {{ request()->routeIs('dashboard') ? 'text-primary-600' : 'text-gray-500 group-hover:text-gray-700' }}"></i>
                        Dashboard
                    </a>

                    <!-- User Management -->
                    <div x-data="{ open: {{ request()->routeIs('users.*') || request()->routeIs('user-levels.*') ? 'true' : 'false' }} }">
                        <button @click="open = !open"
                            class="group flex items-center w-full px-4 py-3 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-100 focus:outline-none">
                            <i class="fas fa-users mr-3 text-gray-500 group-hover:text-gray-700"></i>
                            User Management
                            <i class="fas fa-chevron-down ml-auto transition-transform duration-200"
                                :class="{ 'rotate-180': open }"></i>
                        </button>
                        <div x-show="open" x-transition class="ml-8 mt-2 space-y-2">
                            <a href="{{ route('users.index') }}"
                                class="block px-4 py-2 text-sm rounded {{ request()->routeIs('users.*') ? 'text-primary-600 bg-primary-50' : 'text-gray-600 hover:bg-gray-100' }}">
                                <i class="fas fa-list mr-2"></i>Daftar User
                            </a>
                            <a href="{{ route('user-levels.index') }}"
                                class="block px-4 py-2 text-sm rounded {{ request()->routeIs('user-levels.*') ? 'text-primary-600 bg-primary-50' : 'text-gray-600 hover:bg-gray-100' }}">
                                <i class="fas fa-user-shield mr-2"></i>User Levels
                            </a>
                        </div>
                    </div>

                    <!-- Mitra Management -->
                    <div x-data="{ open: {{ request()->routeIs('mitras.*') ? 'true' : 'false' }} }">
                        <button @click="open = !open"
                            class="group flex items-center w-full px-4 py-3 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-100 focus:outline-none">
                            <i class="fas fa-building mr-3 text-gray-500 group-hover:text-gray-700"></i>
                            Mitra Management
                            <i class="fas fa-chevron-down ml-auto transition-transform duration-200"
                                :class="{ 'rotate-180': open }"></i>
                        </button>
                        <div x-show="open" x-transition class="ml-8 mt-2 space-y-2">
                            <a href="{{ route('mitras.index') }}"
                                class="block px-4 py-2 text-sm rounded {{ request()->routeIs('mitras.index') ? 'text-primary-600 bg-primary-50' : 'text-gray-600 hover:bg-gray-100' }}">
                                <i class="fas fa-list mr-2"></i>Daftar Mitra
                            </a>
                            <a href="{{ route('mitra-turunans.index') }}"
                                class="block px-4 py-2 text-sm rounded {{ request()->routeIs('mitra-turunans.*') ? 'text-primary-600 bg-primary-50' : 'text-gray-600 hover:bg-gray-100' }}">
                                <i class="fas fa-map-marker-alt mr-2"></i>Point Mapping
                            </a>
                        </div>
                    </div>
                    <!-- Map -->
                    <a href="{{ route('maps.index') }}"
                        class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('map.*') ? 'text-primary-600 bg-primary-50' : 'text-gray-700 hover:bg-gray-100' }}">
                        <i
                            class="fas fa-map-marked-alt mr-3 {{ request()->routeIs('map.*') ? 'text-primary-600' : 'text-gray-500 group-hover:text-gray-700' }}"></i>
                        Map
                    </a>

                    <!-- Coverage -->
                    <a href="{{ route('coverage.index') }}"
                        class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('coverage.*') ? 'text-primary-600 bg-primary-50' : 'text-gray-700 hover:bg-gray-100' }}">
                        <i
                            class="fas fa-signal mr-3 {{ request()->routeIs('coverage.*') ? 'text-primary-600' : 'text-gray-500 group-hover:text-gray-700' }}"></i>
                        Coverage
                    </a>


                    <!-- Settings -->
                    <a href="#"
                        class="group flex items-center px-4 py-3 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-100">
                        <i class="fas fa-cog mr-3 text-gray-500 group-hover:text-gray-700"></i>
                        Pengaturan
                    </a>

                    <!-- Reports -->
                    <a href="#"
                        class="group flex items-center px-4 py-3 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-100">
                        <i class="fas fa-chart-bar mr-3 text-gray-500 group-hover:text-gray-700"></i>
                        Laporan
                    </a>

                </div>
            </nav>

            <!-- User Profile di Sidebar -->

        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden lg:ml-0">

            <!-- Top Navigation Bar -->
            <header class="bg-white shadow-sm border-b border-gray-200 z-20">
                <div class="flex items-center justify-between px-4 py-4">

                    <!-- Mobile Menu Button -->
                    <button @click="sidebarOpen = true"
                        class="lg:hidden p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100">
                        <i class="fas fa-bars text-xl"></i>
                    </button>

                    <!-- Page Title -->
                    <div class="flex-1">
                        <h1 class="text-2xl font-semibold text-gray-900">@yield('page-title', 'Dashboard')</h1>
                        @hasSection('breadcrumb')
                            <nav class="flex mt-2" aria-label="Breadcrumb">
                                <ol class="flex items-center space-x-2 text-sm">
                                    @yield('breadcrumb')
                                </ol>
                            </nav>
                        @endif
                    </div>

                    <!-- Top Navigation Items -->
                    <div class="flex items-center space-x-4">

                        <!-- Search -->
                        <div class="hidden md:block relative">
                            <input type="text" id="global-search" placeholder="Cari..."
                                class="w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                        </div>

                        <!-- Notifications -->
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="p-2 text-gray-400 hover:text-gray-500 relative">
                                <i class="fas fa-bell text-xl"></i>
                                <span
                                    class="absolute -top-1 -right-1 h-4 w-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">3</span>
                            </button>

                            <div x-show="open" @click.away="open = false" x-transition
                                class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                                <div class="p-4 border-b border-gray-200">
                                    <h3 class="text-lg font-semibold text-gray-900">Notifikasi</h3>
                                </div>
                                <div class="max-h-64 overflow-y-auto">
                                    <a href="#" class="block p-4 hover:bg-gray-50 border-b border-gray-100">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <div
                                                    class="w-8 h-8 bg-primary-500 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-user text-white text-sm"></i>
                                                </div>
                                            </div>
                                            <div class="ml-3 flex-1">
                                                <p class="text-sm text-gray-900">User baru mendaftar</p>
                                                <p class="text-xs text-gray-500">5 menit yang lalu</p>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- User Menu -->
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open"
                                class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-100">
                                <img class="w-8 h-8 rounded-full object-cover"
                                    src="{{ auth()->user()->profile_picture_url ?? 'https://via.placeholder.com/32' }}"
                                    alt="{{ auth()->user()->name ?? 'User' }}">
                                <i class="fas fa-chevron-down text-sm text-gray-400"></i>
                            </button>

                            <div x-show="open" @click.away="open = false" x-transition
                                class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                                <a href="#"
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-t-lg">
                                    <i class="fas fa-user mr-2"></i>Profil
                                </a>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-cog mr-2"></i>Pengaturan
                                </a>
                                <hr class="my-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="w-full text-left block px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded-b-lg">
                                        <i class="fas fa-sign-out-alt mr-2"></i>Keluar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100">

                <!-- Flash Messages -->
                @if (session('success'))
                    <div class="mx-6 mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg"
                        id="success-alert">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle mr-2"></i>
                            {{ session('success') }}
                        </div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mx-6 mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg"
                        id="error-alert">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            {{ session('error') }}
                        </div>
                    </div>
                @endif

                @if (session('warning'))
                    <div class="mx-6 mt-4 p-4 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded-lg"
                        id="warning-alert">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            {{ session('warning') }}
                        </div>
                    </div>
                @endif

                @if (session('info'))
                    <div class="mx-6 mt-4 p-4 bg-blue-100 border border-blue-400 text-blue-700 rounded-lg"
                        id="info-alert">
                        <div class="flex items-center">
                            <i class="fas fa-info-circle mr-2"></i>
                            {{ session('info') }}
                        </div>
                    </div>
                @endif

                <!-- Main Content Area -->
                <div class="p-6">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- Global JavaScript -->
    <script>
        // CSRF Token Setup
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Global AJAX Setup
        $(document).ajaxStart(function() {
            $('#loading-overlay').removeClass('hidden');
        }).ajaxStop(function() {
            $('#loading-overlay').addClass('hidden');
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('#success-alert, #error-alert, #warning-alert, #info-alert').fadeOut();
        }, 5000);

        // Global Success Handler
        window.showSuccess = function(message) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: message,
                timer: 3000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        };

        // Global Error Handler
        window.showError = function(message) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: message,
                confirmButtonColor: '#3b82f6'
            });
        };

        // Global Confirm Handler
        window.showConfirm = function(message, callback) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#ef4444',
                confirmButtonText: 'Ya, lanjutkan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed && callback) {
                    callback();
                }
            });
        };

        // Global Search Handler
        $('#global-search').on('keyup', function(e) {
            if (e.key === 'Enter') {
                // Implement global search functionality
                console.log('Global search:', $(this).val());
            }
        });

        // Auto-close sidebar on mobile after navigation
        $(document).on('click', '.lg\\:hidden a[href]', function() {
            Alpine.store('sidebarOpen', false);
        });
    </script>

    @stack('scripts')
    @livewireScripts
</body>

</html>
