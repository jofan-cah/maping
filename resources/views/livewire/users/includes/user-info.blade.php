{{-- resources/views/livewire/users/includes/user-info.blade.php --}}
<div class="flex items-center space-x-3">
    <div class="flex-shrink-0">
        <img class="h-10 w-10 rounded-full object-cover"
             src="{{ $user->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&color=7F9CF5&background=EBF4FF' }}"
             alt="{{ $user->name }}"
             onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&color=7F9CF5&background=EBF4FF'">
    </div>
    <div class="min-w-0 flex-1">
        <p class="text-sm font-medium text-gray-900 truncate">
            {{ $user->name }}
        </p>
        <p class="text-xs text-gray-500 truncate">
            ID: #{{ $user->id }}
        </p>
    </div>
</div>
