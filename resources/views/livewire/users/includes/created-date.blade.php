{{-- resources/views/livewire/users/includes/created-date.blade.php --}}
<div class="text-sm text-gray-900">
    <div class="flex items-center">
        <i class="fas fa-calendar text-gray-400 text-xs mr-1"></i>
        <span>{{ $user->created_at->diffForHumans() }}</span>
    </div>
    <div class="text-xs text-gray-500 mt-1">
        {{ $user->created_at->format('M d, Y H:i') }}
    </div>
</div>
