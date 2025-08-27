{{-- layouts/header.blade.php --}}
<header class="bg-white shadow-sm border-b border-gray-200 flex-shrink-0 sticky top-0 z-30">
    <div class="flex items-center justify-between px-4 py-4 sm:px-6">

        {{-- Left Section - Menu Toggle & Page Info --}}
        <div class="flex items-center space-x-4">
            {{-- Mobile Menu Toggle --}}
            <button
                @click="toggleSidebar()"
                class="lg:hidden p-2 rounded-md hover:bg-gray-100 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500"
                aria-label="Toggle Sidebar"
            >
                <i data-feather="menu" class="w-6 h-6"></i>
            </button>

            {{-- Page Title & Breadcrumb --}}
            <div>
                <div class="flex items-center space-x-2">
                    <h2 class="text-2xl font-semibold text-gray-900" x-text="pageTitle">
                        @yield('page-title', 'Dashboard')
                    </h2>

                    {{-- Breadcrumb (Optional) --}}
                    @hasSection('breadcrumb')
                        <nav class="hidden sm:flex" aria-label="Breadcrumb">
                            <ol class="flex items-center space-x-1 text-sm text-gray-500">
                                <li>/</li>
                                @yield('breadcrumb')
                            </ol>
                        </nav>
                    @endif
                </div>

                <p class="text-sm text-gray-600 mt-1" x-text="pageSubtitle">
                    @yield('page-subtitle', 'Welcome back!')
                </p>
            </div>
        </div>

        {{-- Right Section - Actions & User Menu --}}
        <div class="flex items-center space-x-4">

            {{-- Search (Optional) --}}
            @hasSection('header-search')
                <div class="hidden md:block">
                    @yield('header-search')
                </div>
            @else
                <div class="hidden md:block relative" x-data="{ searchOpen: false }">
                    <button
                        @click="searchOpen = !searchOpen"
                        class="p-2 rounded-full hover:bg-gray-100 transition-colors"
                        title="Search"
                    >
                        <i data-feather="search" class="w-5 h-5 text-gray-600"></i>
                    </button>

                    {{-- Search Dropdown --}}
                    <div
                        x-show="searchOpen"
                        @click.away="searchOpen = false"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-1 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-1 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl border border-gray-200 z-50"
                        x-cloak
                    >
                        <div class="p-4">
                            <input
                                type="text"
                                placeholder="Search anything..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                x-ref="searchInput"
                                x-init="$watch('searchOpen', value => value && $nextTick(() => $refs.searchInput.focus()))"
                            >
                        </div>
                    </div>
                </div>
            @endif

            {{-- Notifications --}}
            <div class="relative" x-data="notificationDropdown()">
                <button
                    @click="toggleNotifications()"
                    class="p-2 rounded-full hover:bg-gray-100 transition-colors relative focus:outline-none focus:ring-2 focus:ring-blue-500"
                    title="Notifications"
                >
                    <i data-feather="bell" class="w-5 h-5 text-gray-600"></i>

                    {{-- Notification Badge --}}
                    <span
                        x-show="unreadCount > 0"
                        x-text="unreadCount > 99 ? '99+' : unreadCount"
                        class="absolute -top-1 -right-1 min-w-[16px] h-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center px-1"
                    ></span>
                </button>

                {{-- Notifications Dropdown --}}
                <div
                    x-show="isOpen"
                    @click.away="closeNotifications()"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-1 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-1 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-xl border border-gray-200 z-50"
                    x-cloak
                >
                    {{-- Notification Header --}}
                    <div class="flex items-center justify-between p-4 border-b border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900">Notifications</h3>
                        <button
                            @click="markAllAsRead()"
                            class="text-sm text-blue-600 hover:text-blue-800 font-medium"
                        >
                            Mark all read
                        </button>
                    </div>

                    {{-- Notification List --}}
                    <div class="max-h-80 overflow-y-auto">
                        <template x-for="notification in notifications" :key="notification.id">
                            <div
                                @click="markAsRead(notification.id)"
                                :class="notification.read ? 'bg-white' : 'bg-blue-50'"
                                class="p-4 hover:bg-gray-50 border-b border-gray-100 cursor-pointer transition-colors"
                            >
                                <div class="flex items-start space-x-3">
                                    <div
                                        class="w-2 h-2 rounded-full mt-2"
                                        :class="notification.read ? 'bg-gray-300' : 'bg-blue-500'"
                                    ></div>
                                    <div class="flex-1 min-w-0">
                                        <p
                                            class="text-sm font-medium"
                                            :class="notification.read ? 'text-gray-600' : 'text-gray-900'"
                                            x-text="notification.title"
                                        ></p>
                                        <p class="text-sm text-gray-500 mt-1" x-text="notification.message"></p>
                                        <p class="text-xs text-gray-400 mt-2" x-text="notification.time"></p>
                                    </div>
                                </div>
                            </div>
                        </template>

                        {{-- Empty State --}}
                        <div x-show="notifications.length === 0" class="p-8 text-center text-gray-500">
                            <i data-feather="bell" class="w-12 h-12 mx-auto mb-4 text-gray-300"></i>
                            <p>No notifications yet</p>
                        </div>
                    </div>

                    {{-- Notification Footer --}}
                    <div class="p-3 border-t border-gray-100">
                        <a href="#notifications" class="block text-center text-sm text-blue-600 hover:text-blue-800 font-medium">
                            View all notifications
                        </a>
                    </div>
                </div>
            </div>

            {{-- Quick Actions (Optional) --}}
            @hasSection('header-actions')
                <div class="flex items-center space-x-2">
                    @yield('header-actions')
                </div>
            @endif

            {{-- User Profile Dropdown --}}
            <div class="relative" x-data="{ profileOpen: false }">
                <button
                    @click="profileOpen = !profileOpen"
                    class="flex items-center space-x-3 hover:bg-gray-50 rounded-lg p-2 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    {{-- User Avatar --}}
                    <div class="w-8 h-8 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center flex-shrink-0">
                        @auth
                            @if(auth()->user()->avatar)
                                <img src="{{ auth()->user()->avatar }}" alt="Avatar" class="w-full h-full rounded-full object-cover">
                            @else
                                <span class="text-sm font-medium text-white">
                                    {{ substr(auth()->user()->name ?? 'A', 0, 1) }}{{ substr(explode(' ', auth()->user()->name ?? 'A')[1] ?? '', 0, 1) }}
                                </span>
                            @endif
                        @else
                            <i data-feather="user" class="w-4 h-4 text-white"></i>
                        @endauth
                    </div>

                    {{-- User Info --}}
                    <div class="hidden sm:block text-left">
                        <p class="text-sm font-medium text-gray-900">
                            @auth
                                {{ auth()->user()->name ?? 'Admin User' }}
                            @else
                                Admin User
                            @endauth
                        </p>
                        <p class="text-xs text-gray-600">
                            @auth
                                {{ auth()->user()->email ?? 'admin@demo.com' }}
                            @else
                                admin@demo.com
                            @endauth
                        </p>
                    </div>

                    {{-- Dropdown Arrow --}}
                    <i data-feather="chevron-down" class="w-4 h-4 text-gray-400 transition-transform" :class="{ 'rotate-180': profileOpen }"></i>
                </button>

                {{-- Profile Dropdown Menu --}}
                <div
                    x-show="profileOpen"
                    @click.away="profileOpen = false"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-1 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-1 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-xl border border-gray-200 z-50"
                    x-cloak
                >
                    {{-- Profile Info --}}
                    <div class="px-4 py-3 border-b border-gray-100">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
                                @auth
                                    @if(auth()->user()->avatar)
                                        <img src="{{ auth()->user()->avatar }}" alt="Avatar" class="w-full h-full rounded-full object-cover">
                                    @else
                                        <span class="text-lg font-medium text-white">
                                            {{ substr(auth()->user()->name ?? 'A', 0, 1) }}{{ substr(explode(' ', auth()->user()->name ?? 'A')[1] ?? '', 0, 1) }}
                                        </span>
                                    @endif
                                @else
                                    <i data-feather="user" class="w-6 h-6 text-white"></i>
                                @endauth
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900 truncate">
                                    @auth
                                        {{ auth()->user()->name ?? 'Admin User' }}
                                    @else
                                        Admin User
                                    @endauth
                                </p>
                                <p class="text-xs text-gray-600 truncate">
                                    @auth
                                        {{ auth()->user()->email ?? 'admin@demo.com' }}
                                    @else
                                        admin@demo.com
                                    @endauth
                                </p>
                                <p class="text-xs text-blue-600 font-medium">
                                    @auth
                                        {{ auth()->user()->role ?? 'Administrator' }}
                                    @else
                                        Administrator
                                    @endauth
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Profile Menu Items --}}
                    <div class="py-2">
                        <a href="#profile" @click="profileOpen = false" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                            <i data-feather="user" class="w-4 h-4 mr-3 text-gray-400"></i>
                            <span>My Profile</span>
                        </a>

                        <a href="#account-settings" @click="profileOpen = false" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                            <i data-feather="settings" class="w-4 h-4 mr-3 text-gray-400"></i>
                            <span>Account Settings</span>
                        </a>

                        <a href="#notifications" @click="profileOpen = false" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                            <i data-feather="bell" class="w-4 h-4 mr-3 text-gray-400"></i>
                            <span>Notification Settings</span>
                        </a>

                        <hr class="my-2 border-gray-100">

                        <a href="#help" @click="profileOpen = false" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                            <i data-feather="help-circle" class="w-4 h-4 mr-3 text-gray-400"></i>
                            <span>Help Center</span>
                        </a>

                        <a href="#feedback" @click="profileOpen = false" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                            <i data-feather="message-square" class="w-4 h-4 mr-3 text-gray-400"></i>
                            <span>Send Feedback</span>
                        </a>

                        <hr class="my-2 border-gray-100">

                        {{-- Logout --}}
                        @auth
                            <form method="POST" action="#">
                                @csrf
                                <button type="submit" class="w-full flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                    <i data-feather="log-out" class="w-4 h-4 mr-3"></i>
                                    <span>Sign Out</span>
                                </button>
                            </form>
                        @else
                            <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                                <i data-feather="log-in" class="w-4 h-4 mr-3 text-gray-400"></i>
                                <span>Sign In</span>
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Optional: Page Actions Bar --}}
    @hasSection('page-actions')
        <div class="border-t border-gray-200 px-4 py-3 sm:px-6">
            <div class="flex items-center justify-between">
                @yield('page-actions')
            </div>
        </div>
    @endif
</header>

{{-- Header Component Styles --}}
@push('styles')
<style>
    /* Header specific styles */
    .header-action-btn {
        @apply p-2 rounded-full hover:bg-gray-100 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500;
    }

    /* Notification badge animation */
    .notification-badge {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
    }

    /* Profile dropdown animation */
    .profile-dropdown-enter {
        transform: translateY(-10px) scale(0.95);
        opacity: 0;
    }

    .profile-dropdown-enter-to {
        transform: translateY(0) scale(1);
        opacity: 1;
    }
</style>
@endpush

{{-- Header Component Scripts --}}
@push('scripts')
<script>
    // Notification Dropdown Component
    function notificationDropdown() {
        return {
            isOpen: false,
            unreadCount: 3, // This would come from backend
            notifications: [
                {
                    id: 1,
                    title: 'New user registered',
                    message: 'John Doe just created an account',
                    time: '2 minutes ago',
                    read: false
                },
                {
                    id: 2,
                    title: 'Order completed',
                    message: 'Order #1234 has been successfully completed',
                    time: '5 minutes ago',
                    read: false
                },
                {
                    id: 3,
                    title: 'Payment pending',
                    message: 'Payment for order #1235 is pending',
                    time: '10 minutes ago',
                    read: false
                },
                {
                    id: 4,
                    title: 'System update',
                    message: 'System maintenance completed successfully',
                    time: '1 hour ago',
                    read: true
                }
            ],

            toggleNotifications() {
                this.isOpen = !this.isOpen;
            },

            closeNotifications() {
                this.isOpen = false;
            },

            markAsRead(notificationId) {
                const notification = this.notifications.find(n => n.id === notificationId);
                if (notification && !notification.read) {
                    notification.read = true;
                    this.updateUnreadCount();
                }
            },

            markAllAsRead() {
                this.notifications.forEach(notification => {
                    notification.read = true;
                });
                this.updateUnreadCount();
            },

            updateUnreadCount() {
                this.unreadCount = this.notifications.filter(n => !n.read).length;
            }
        }
    }

    // Header scroll behavior
    let lastScrollTop = 0;
    const header = document.querySelector('header');

    window.addEventListener('scroll', function() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

        if (scrollTop > lastScrollTop && scrollTop > 100) {
            // Scrolling down
            header.style.transform = 'translateY(-100%)';
        } else {
            // Scrolling up
            header.style.transform = 'translateY(0)';
        }

        lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
    });

    // Real-time notifications (would integrate with WebSocket/Pusher)
    function addNotification(title, message) {
        // This would be called from your WebSocket listener
        const app = document.querySelector('[x-data]').__x.$data;
        // Add notification logic here
    }

    // Initialize header on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize any header-specific functionality
        console.log('Header initialized');
    });
</script>
@endpush
