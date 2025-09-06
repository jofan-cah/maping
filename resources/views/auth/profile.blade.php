@extends('layouts.app')

@section('title', 'Edit Profile')

@section('page-title', 'Edit Profile')

@section('breadcrumb')
    <li class="flex items-center">
        <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700">
            <i class="fas fa-home"></i>
        </a>
        <i class="fas fa-chevron-right mx-2 text-gray-400"></i>
    </li>
    <li class="text-gray-900">Edit Profile</li>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Profile Header Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="p-6">
            <div class="flex items-center space-x-6">
                <div class="relative">
                    <img id="profile-preview"
                         class="w-24 h-24 rounded-full object-cover border-4 border-gray-200"
                         src="{{ $user->profile_picture_url }}"
                         alt="{{ $user->name }}">
                    <div class="absolute -bottom-2 -right-2 bg-primary-600 rounded-full p-2 shadow-lg">
                        <i class="fas fa-camera text-white text-sm"></i>
                    </div>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h2>
                    <p class="text-gray-600">{{ $user->level_name }}</p>
                    <p class="text-sm text-gray-500">{{ $user->email }}</p>
                    <div class="flex items-center mt-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            <i class="fas fa-circle mr-1 text-xs"></i>
                            {{ $user->status_text }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Informasi Profile</h3>
            <p class="text-sm text-gray-600">Update informasi profile dan data pribadi Anda.</p>
        </div>

        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="p-6">
            @csrf
            @method('PUT')

            <!-- Profile Picture Section -->
            <div class="mb-8">
                <label class="block text-sm font-medium text-gray-700 mb-4">Foto Profile</label>
                <div class="flex items-center space-x-6">
                    <div class="shrink-0">
                        <img id="preview-image"
                             class="w-16 h-16 rounded-full object-cover border-2 border-gray-300"
                             src="{{ $user->profile_picture_url }}"
                             alt="Profile preview">
                    </div>
                    <div class="flex-1">
                        <input type="file"
                               id="profile_picture"
                               name="profile_picture"
                               accept="image/*"
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                        <p class="text-xs text-gray-500 mt-1">JPG, PNG, GIF maksimal 2MB</p>
                        @error('profile_picture')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    @if($user->profile_picture)
                    <div>
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="remove_picture" value="1" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            <span class="text-sm text-gray-600">Hapus foto</span>
                        </label>
                    </div>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="name"
                           name="name"
                           value="{{ old('name', $user->name) }}"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email"
                           id="email"
                           name="email"
                           value="{{ old('email', $user->email) }}"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Password Section -->
            <div class="mt-8 pt-8 border-t border-gray-200">
                <h4 class="text-lg font-medium text-gray-900 mb-4">Ubah Password</h4>
                <p class="text-sm text-gray-600 mb-6">Kosongkan jika tidak ingin mengubah password.</p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Current Password -->
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">
                            Password Saat Ini
                        </label>
                        <div class="relative">
                            <input type="password"
                                   id="current_password"
                                   name="current_password"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('current_password') border-red-500 @enderror">
                            <button type="button"
                                    onclick="togglePassword('current_password')"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <i class="fas fa-eye text-gray-400 hover:text-gray-600"></i>
                            </button>
                        </div>
                        @error('current_password')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- New Password -->
                    <div>
                        <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">
                            Password Baru
                        </label>
                        <div class="relative">
                            <input type="password"
                                   id="new_password"
                                   name="new_password"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('new_password') border-red-500 @enderror">
                            <button type="button"
                                    onclick="togglePassword('new_password')"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <i class="fas fa-eye text-gray-400 hover:text-gray-600"></i>
                            </button>
                        </div>
                        @error('new_password')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirm New Password -->
                    <div>
                        <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                            Konfirmasi Password Baru
                        </label>
                        <div class="relative">
                            <input type="password"
                                   id="new_password_confirmation"
                                   name="new_password_confirmation"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <button type="button"
                                    onclick="togglePassword('new_password_confirmation')"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <i class="fas fa-eye text-gray-400 hover:text-gray-600"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account Info -->
            <div class="mt-8 pt-8 border-t border-gray-200">
                <h4 class="text-lg font-medium text-gray-900 mb-4">Informasi Akun</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">User ID</label>
                        <input type="text"
                               value="{{ $user->user_id }}"
                               readonly
                               class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md text-gray-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Level User</label>
                        <input type="text"
                               value="{{ $user->level_name }}"
                               readonly
                               class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md text-gray-500">
                    </div>
                    @if($user->last_login_at)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Login Terakhir</label>
                        <input type="text"
                               value="{{ $user->last_login_at->format('d-m-Y H:i:s') }}"
                               readonly
                               class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md text-gray-500">
                    </div>
                    @endif
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status Akun</label>
                        <input type="text"
                               value="{{ $user->status_text }}"
                               readonly
                               class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md text-gray-500">
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="mt-8 pt-6 border-t border-gray-200 flex items-center justify-between">
                <a href="{{ route('dashboard') }}"
                   class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>

                <div class="flex space-x-3">
                    <button type="button"
                            onclick="resetForm()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        <i class="fas fa-undo mr-2"></i>Reset
                    </button>

                    <button type="submit"
                            class="px-6 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-md shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        <i class="fas fa-save mr-2"></i>Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Preview image when file selected
document.getElementById('profile_picture').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-image').src = e.target.result;
            document.getElementById('profile-preview').src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
});

// Toggle password visibility
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = field.nextElementSibling.querySelector('i');

    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Reset form
function resetForm() {
    if (confirm('Yakin ingin mereset form? Semua perubahan akan hilang.')) {
        document.querySelector('form').reset();
        // Reset preview images
        document.getElementById('preview-image').src = '{{ $user->profile_picture_url }}';
        document.getElementById('profile-preview').src = '{{ $user->profile_picture_url }}';
    }
}

// Auto-hide password fields if not changing password
document.addEventListener('DOMContentLoaded', function() {
    const newPassword = document.getElementById('new_password');
    const currentPassword = document.getElementById('current_password');
    const confirmPassword = document.getElementById('new_password_confirmation');

    newPassword.addEventListener('input', function() {
        if (this.value) {
            currentPassword.setAttribute('required', 'required');
            confirmPassword.setAttribute('required', 'required');
        } else {
            currentPassword.removeAttribute('required');
            confirmPassword.removeAttribute('required');
        }
    });
});
</script>
@endsection
