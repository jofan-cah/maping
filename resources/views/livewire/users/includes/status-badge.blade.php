{{-- resources/views/livewire/users/includes/status-badge.blade.php --}}
@if($user->is_active)
    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
        <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></span>
        Active
    </span>
@else
    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200">
        <span class="w-1.5 h-1.5 bg-red-500 rounded-full mr-1.5"></span>
        Inactive
    </span>
@endif
