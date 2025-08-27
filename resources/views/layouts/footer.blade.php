{{-- layouts/footer.blade.php --}}
<footer class="bg-white border-t border-gray-200 mt-auto">
    <div class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row items-center justify-between">

            {{-- Left Section - Copyright --}}
            <div class="flex items-center space-x-4 mb-4 sm:mb-0">
                <div class="flex items-center space-x-2">
                    <div class="w-6 h-6 bg-gradient-to-r from-blue-500 to-purple-600 rounded flex items-center justify-center">
                        <i data-feather="zap" class="w-3 h-3 text-white"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-900">
                        {{ config('app.name', 'AdminPanel') }}
                    </span>
                </div>
                <span class="text-sm text-gray-500">
                    © {{ date('Y') }} All rights reserved.
                </span>
            </div>

            {{-- Center Section - Links (Optional) --}}
            <div class="hidden md:flex items-center space-x-6 mb-4 sm:mb-0">
                <a href="#privacy" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">
                    Privacy Policy
                </a>
                <a href="#terms" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">
                    Terms of Service
                </a>
                <a href="#support" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">
                    Support
                </a>
                <a href="#docs" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">
                    Documentation
                </a>
            </div>

            {{-- Right Section - Version & Status --}}
            <div class="flex items-center space-x-4 text-sm text-gray-500">
                {{-- App Version --}}
                <div class="flex items-center space-x-2">
                    <span>v{{ config('app.version', '1.0.0') }}</span>
                </div>

                {{-- System Status --}}
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                    <span class="text-xs">System Online</span>
                </div>

                {{-- Current Time --}}
                <div class="hidden lg:block" x-data="{ time: new Date().toLocaleTimeString() }" x-init="setInterval(() => time = new Date().toLocaleTimeString(), 1000)">
                    <span x-text="time" class="font-mono"></span>
                </div>
            </div>
        </div>

        {{-- Additional Footer Content (Optional) --}}
        @hasSection('footer-content')
            <div class="mt-4 pt-4 border-t border-gray-100">
                @yield('footer-content')
            </div>
        @endif

        {{-- Development Info (Only in non-production) --}}
        @if(config('app.env') !== 'production')
            <div class="mt-4 pt-4 border-t border-gray-100">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between text-xs text-gray-400">
                    <div class="mb-2 sm:mb-0">
                        <span class="font-medium">Development Mode:</span>
                        Laravel {{ app()->version() }} | PHP {{ PHP_VERSION }}
                    </div>
                    <div class="flex items-center space-x-4">
                        <span>Memory: {{ round(memory_get_peak_usage(true) / 1024 / 1024, 2) }}MB</span>
                        <span>Time: {{ round((microtime(true) - LARAVEL_START) * 1000, 2) }}ms</span>
                    </div>
                </div>
            </div>
        @endif
    </div>
</footer>

{{-- Footer Styles --}}
@push('styles')
<style>
    /* Footer specific styles */
    footer {
        flex-shrink: 0;
    }

    /* Status indicator animation */
    .status-online {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: .5;
        }
    }

    /* Responsive text scaling */
    @media (max-width: 640px) {
        footer {
            font-size: 0.875rem;
        }
    }
</style>
@endpush

{{-- Footer Scripts --}}
@push('scripts')
<script>
    // Footer specific JavaScript
    document.addEventListener('DOMContentLoaded', function() {
        // System status check (example)
        function checkSystemStatus() {
            // This would make an actual API call to check system health
            const statusIndicator = document.querySelector('.status-indicator');
            if (statusIndicator) {
                // Update status based on API response
                statusIndicator.className = 'w-2 h-2 bg-green-400 rounded-full animate-pulse';
            }
        }

        // Check status every 30 seconds
        setInterval(checkSystemStatus, 30000);

        console.log('Footer initialized');
    });
</script>
@endpush
