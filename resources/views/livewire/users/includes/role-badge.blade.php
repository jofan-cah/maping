{{-- resources/views/livewire/users/includes/role-badge.blade.php --}}
@php
$roleColors = [
    'admin' => 'bg-red-100 text-red-800 border-red-200',
    'manager' => 'bg-blue-100 text-blue-800 border-blue-200',
    'user' => 'bg-gray-100 text-gray-800 border-gray-200',
    'editor' => 'bg-purple-100 text-purple-800 border-purple-200',
    'moderator' => 'bg-green-100 text-green-800 border-green-200',
];
$roleIcons = [
    'admin' => 'fas fa-crown',
    'manager' => 'fas fa-user-tie',
    'user' => 'fas fa-user',
    'editor' => 'fas fa-edit',
    'moderator' => 'fas fa-shield-alt',
];
$colorClass = $roleColors[$user->role] ?? $roleColors['user'];
$iconClass = $roleIcons[$user->role] ?? $roleIcons['user'];
@endphp

<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border {{ $colorClass }}">
    <i class="{{ $iconClass }} mr-1.5"></i>
    {{ ucfirst($user->role) }}
</span>

