{{-- resources/views/livewire/users/includes/last-login.blade.php --}}
<div class="text-sm text-gray-900">
    @if($user->last_login_at)
        <div class="flex items-center">
            <i class="fas fa-clock text-gray-400 text-xs mr-1"></i>
            <span>{{ $user->last_login_at->diffForHumans() }}</span>
        </div>
        <div class="text-xs text-gray-500 mt-1">
            {{ $user->last_login_at->format('M d, Y H:i') }}
        </div>
    @else
        <span class="text-gray-400 text-sm italic">Never logged in</span>
    @endif
</div>
