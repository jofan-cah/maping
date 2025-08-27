{{-- resources/views/livewire/users/includes/email.blade.php --}}
<div class="text-sm text-gray-900">
    <a href="mailto:{{ $user->email }}"
       class="text-blue-600 hover:text-blue-800 transition-colors">
        {{ $user->email }}
    </a>
    @if($user->email_verified_at)
        <div class="flex items-center mt-1">
            <i class="fas fa-check-circle text-green-500 text-xs mr-1"></i>
            <span class="text-xs text-green-600">Verified</span>
        </div>
    @else
        <div class="flex items-center mt-1">
            <i class="fas fa-exclamation-circle text-yellow-500 text-xs mr-1"></i>
            <span class="text-xs text-yellow-600">Unverified</span>
        </div>
    @endif
</div>
