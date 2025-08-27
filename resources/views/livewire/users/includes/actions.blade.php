{{-- resources/views/livewire/users/includes/actions.blade.php --}}
<div class="flex items-center space-x-2">
    <!-- Quick Actions -->
    <div class="flex items-center space-x-1">
        <!-- View Button -->
        <a href="{{ route('users.show', $user->user_id) }}"
           class="inline-flex items-center justify-center w-8 h-8 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200"
           title="View User">
            <i class="fas fa-eye text-sm"></i>
        </a>

        <!-- Edit Button -->
        <a href="{{ route('users.edit', $user->user_id) }}"
           class="inline-flex items-center justify-center w-8 h-8 text-gray-500 hover:text-green-600 hover:bg-green-50 rounded-lg transition-all duration-200"
           title="Edit User">
            <i class="fas fa-edit text-sm"></i>
        </a>

        <!-- Toggle Status -->
        <button wire:click="toggleStatus({{ $user->user_id }})"
                class="inline-flex items-center justify-center w-8 h-8 text-gray-500 hover:text-yellow-600 hover:bg-yellow-50 rounded-lg transition-all duration-200"
                title="{{ $user->is_active ? 'Deactivate' : 'Activate' }} User">
            <i class="fas {{ $user->is_active ? 'fa-user-slash' : 'fa-user-check' }} text-sm"></i>
        </button>
    </div>

    <!-- More Actions Dropdown -->
    {{-- <div class="relative" x-data="{ open: false }">
        <button @click="open = !open"
                class="inline-flex items-center justify-center w-8 h-8 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-all duration-200"
                title="More Actions">
            <i class="fas fa-ellipsis-v text-sm"></i>
        </button>

        <div x-show="open"
             @click.away="open = false"
             x-cloak
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="transform opacity-0 scale-95"
             x-transition:enter-end="transform opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="transform opacity-100 scale-100"
             x-transition:leave-end="transform opacity-0 scale-95"
             class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 z-10">

            <div class="py-1">
                <a href="#"
                   class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                    <i class="fas fa-eye mr-3 text-gray-400"></i>
                    View Details
                </a>

                <a href="#"
                   class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                    <i class="fas fa-edit mr-3 text-gray-400"></i>
                    Edit User
                </a>

                @if($user->user_id !== auth()->id())
                    <button wire:click="toggleStatus({{ $user->user_id }})"
                            class="w-full flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                        <i class="fas {{ $user->is_active ? 'fa-user-slash' : 'fa-user-check' }} mr-3 text-gray-400"></i>
                        {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                @endif

                <a href="#"
                   class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                    <i class="fas fa-key mr-3 text-gray-400"></i>
                    Reset Password
                </a>

                <a href="mailto:{{ $user->email }}"
                   class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                    <i class="fas fa-envelope mr-3 text-gray-400"></i>
                    Send Email
                </a>

                @if($user->user_id !== auth()->id())
                    <div class="border-t border-gray-100 my-1"></div>

                    <button wire:click="deleteUser({{ $user->user_id }})"
                            onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')"
                            class="w-full flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                        <i class="fas fa-trash mr-3 text-red-500"></i>
                        Delete User
                    </button>
                @endif
            </div>
        </div>
    </div> --}}
</div>
