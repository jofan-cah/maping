
{{-- resources/views/livewire/users/includes/checkbox.blade.php --}}
<div class="flex items-center">
    <input type="checkbox"
           name="selected[]"
           value="{{ $user->id }}"
           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
</div>
