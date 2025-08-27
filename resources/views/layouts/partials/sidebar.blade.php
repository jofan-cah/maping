<!-- Dashboard -->
<a href="{{ route('dashboard') }}"
   class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('dashboard') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
    <i class="fas fa-home mr-3 flex-shrink-0 h-4 w-4"></i>
    Dashboard
</a>

<!-- Mitra -->
<div x-data="{ open: {{ request()->routeIs('mitra*') ? 'true' : 'false' }} }">
    <button @click="open = !open"
            class="group flex items-center w-full px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('mitra*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
        <i class="fas fa-handshake mr-3 flex-shrink-0 h-4 w-4"></i>
        Mitra
        <i class="fas fa-chevron-right ml-auto h-3 w-3 transition-transform duration-150" :class="{ 'rotate-90': open }"></i>
    </button>
    <div x-show="open"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="space-y-1 mt-1">
        <a href="#"
           class="group flex items-center pl-8 pr-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('mitra.index') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
            <i class="fas fa-list mr-3 flex-shrink-0 h-4 w-4"></i>
            Daftar Mitra
        </a>
        <a href="#"
           class="group flex items-center pl-8 pr-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('mitra.create') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
            <i class="fas fa-plus mr-3 flex-shrink-0 h-4 w-4"></i>
            Tambah Mitra
        </a>
    </div>
</div>

<!-- Mitra Turunan -->
<div x-data="{ open: {{ request()->routeIs('mitra-turunan*') ? 'true' : 'false' }} }">
    <button @click="open = !open"
            class="group flex items-center w-full px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('mitra-turunan*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
        <i class="fas fa-map-marker-alt mr-3 flex-shrink-0 h-4 w-4"></i>
        Mitra Turunan
        <i class="fas fa-chevron-right ml-auto h-3 w-3 transition-transform duration-150" :class="{ 'rotate-90': open }"></i>
    </button>
    <div x-show="open"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="space-y-1 mt-1">
        <a href="#"
           class="group flex items-center pl-8 pr-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('mitra-turunan.index') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
            <i class="fas fa-list mr-3 flex-shrink-0 h-4 w-4"></i>
            Daftar Points
        </a>
        <a href="#"
           class="group flex items-center pl-8 pr-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('mitra-turunan.create') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
            <i class="fas fa-plus mr-3 flex-shrink-0 h-4 w-4"></i>
            Tambah Point
        </a>
    </div>
</div>

<!-- Laporan -->
<a href="#"
   class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('reports*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
    <i class="fas fa-chart-bar mr-3 flex-shrink-0 h-4 w-4"></i>
    Laporan
</a>

<!-- Maps -->
<a href="#"
   class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('maps*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
    <i class="fas fa-map mr-3 flex-shrink-0 h-4 w-4"></i>
    Peta
</a>

<!-- Divider -->
<hr class="my-3 border-gray-700">

<!-- Settings -->
<a href="#"
   class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('settings*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
    <i class="fas fa-cog mr-3 flex-shrink-0 h-4 w-4"></i>
    Pengaturan
</a>

<!-- Users Management -->
<a href="{{ route('users.index') }}"
   class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('users*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
    <i class="fas fa-users mr-3 flex-shrink-0 h-4 w-4"></i>
    Manajemen User
</a>
