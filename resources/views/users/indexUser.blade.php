@extends('layouts.app')

@section('title', 'User Management')
@section('page-title', 'User Management')

@section('breadcrumb')
    <li><a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-home"></i></a></li>
    <li><span class="text-gray-400">/</span></li>
    <li><span class="text-gray-600 font-medium">User Management</span></li>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4" id="stats-container">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-users text-blue-600"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Users</dt>
                            <dd class="text-lg font-medium text-gray-900" id="total-users">-</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-600"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Active Users</dt>
                            <dd class="text-lg font-medium text-gray-900" id="active-users">-</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-user-clock text-yellow-600"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Inactive Users</dt>
                            <dd class="text-lg font-medium text-gray-900" id="inactive-users">-</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-trash text-red-600"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Deleted Users</dt>
                            <dd class="text-lg font-medium text-gray-900" id="deleted-users">-</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <!-- Header -->
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Daftar User</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">Kelola semua user dalam sistem</p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <button type="button" onclick="openCreateModal()"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        <i class="fas fa-plus -ml-1 mr-2"></i>
                        Tambah User
                    </button>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="px-4 py-4 sm:px-6 bg-gray-50 border-b border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div class="col-span-1 md:col-span-2">
                    <div class="relative">
                        <input type="text" id="search-input" placeholder="Cari nama, email, atau ID..."
                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <!-- Status Filter -->
                <div>
                    <select id="status-filter" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md">
                        <option value="">Semua Status</option>
                        <option value="active">Aktif</option>
                        <option value="inactive">Non-Aktif</option>
                        <option value="deleted">Dihapus</option>
                    </select>
                </div>

                <!-- User Level Filter -->
                <div>
                    <select id="level-filter" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md">
                        <option value="">Semua Level</option>
                        @foreach($userLevels as $level)
                            <option value="{{ $level->user_level_id }}">{{ $level->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Bulk Actions -->
            <div class="flex items-center justify-between mt-4">
                <div class="flex items-center space-x-4">
                    <div class="flex items-center">
                        <input type="checkbox" id="select-all" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                        <label for="select-all" class="ml-2 text-sm text-gray-900">Pilih Semua</label>
                    </div>
                    <div class="hidden" id="bulk-actions">
                        <select id="bulk-action-select" class="text-sm border-gray-300 rounded-md">
                            <option value="">Pilih Aksi</option>
                            <option value="activate">Aktifkan</option>
                            <option value="deactivate">Nonaktifkan</option>
                            <option value="delete">Hapus</option>
                            <option value="restore">Pulihkan</option>
                            <option value="force_delete">Hapus Permanen</option>
                        </select>
                        <button type="button" onclick="executeBulkAction()"
                                class="ml-2 inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50">
                            Jalankan
                        </button>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <button type="button" onclick="exportData('csv')" class="text-sm text-gray-600 hover:text-gray-900">
                        <i class="fas fa-download mr-1"></i>Export CSV
                    </button>
                    <button type="button" onclick="refreshData()" class="text-sm text-gray-600 hover:text-gray-900">
                        <i class="fas fa-refresh mr-1"></i>Refresh
                    </button>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <input type="checkbox" id="header-checkbox" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" onclick="sortBy('user_id')">
                            ID User <i class="fas fa-sort ml-1"></i>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" onclick="sortBy('name')">
                            Nama <i class="fas fa-sort ml-1"></i>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" onclick="sortBy('email')">
                            Email <i class="fas fa-sort ml-1"></i>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Level
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" onclick="sortBy('last_login_at')">
                            Last Login <i class="fas fa-sort ml-1"></i>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" onclick="sortBy('created_at')">
                            Dibuat <i class="fas fa-sort ml-1"></i>
                        </th>
                        <th scope="col" class="relative px-6 py-3">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody id="users-tbody" class="bg-white divide-y divide-gray-200">
                    <!-- Data will be loaded here via AJAX -->
                </tbody>
            </table>
        </div>

        <!-- Loading State -->
        <div id="loading-state" class="px-6 py-8 text-center">
            <div class="inline-flex items-center text-gray-500">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary-600 mr-3"></div>
                Memuat data...
            </div>
        </div>

        <!-- Empty State -->
        <div id="empty-state" class="hidden px-6 py-8 text-center">
            <div class="text-gray-500">
                <i class="fas fa-users text-4xl mb-4"></i>
                <p class="text-lg font-medium">Tidak ada user ditemukan</p>
                <p class="text-sm">Coba ubah filter atau tambah user baru</p>
            </div>
        </div>

        <!-- Pagination -->
        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div class="flex-1 flex justify-between sm:hidden">
                <button id="prev-mobile" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Previous
                </button>
                <button id="next-mobile" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Next
                </button>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700" id="pagination-info">
                        Showing <span class="font-medium" id="showing-from">0</span> to <span class="font-medium" id="showing-to">0</span> of <span class="font-medium" id="total-records">0</span> results
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" id="pagination-nav">
                        <!-- Pagination buttons will be generated here -->
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Modal -->
<div id="user-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between pb-3">
                <h3 class="text-lg font-medium text-gray-900" id="modal-title">Tambah User Baru</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="user-form" enctype="multipart/form-data">
                <input type="hidden" id="user-id" name="user_id">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                        <input type="text" id="name" name="name" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                        <div class="text-red-600 text-sm mt-1 hidden" id="name-error"></div>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="email" name="email" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                        <div class="text-red-600 text-sm mt-1 hidden" id="email-error"></div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" id="password" name="password"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                        <div class="text-red-600 text-sm mt-1 hidden" id="password-error"></div>
                        <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah password</p>
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="user_level_id" class="block text-sm font-medium text-gray-700">User Level</label>
                        <select id="user_level_id" name="user_level_id"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                            <option value="">Pilih Level</option>
                            @foreach($userLevels as $level)
                                <option value="{{ $level->user_level_id }}">{{ $level->name }}</option>
                            @endforeach
                        </select>
                        <div class="text-red-600 text-sm mt-1 hidden" id="user_level_id-error"></div>
                    </div>

                    <div>
                        <label for="profile_picture" class="block text-sm font-medium text-gray-700">Profile Picture</label>
                        <input type="file" id="profile_picture" name="profile_picture" accept="image/*"
                               class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                        <div class="text-red-600 text-sm mt-1 hidden" id="profile_picture-error"></div>
                    </div>
                </div>

                <div class="mt-6">
                    <div class="flex items-center">
                        <input type="checkbox" id="is_active" name="is_active" value="1" checked
                               class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                        <label for="is_active" class="ml-2 block text-sm text-gray-900">
                            User Aktif
                        </label>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-4 mt-6 pt-4 border-t">
                    <button type="button" onclick="closeModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        <span class="loading-text">Simpan</span>
                        <span class="loading-spinner hidden">
                            <i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let currentPage = 1;
let currentSortField = 'created_at';
let currentSortDirection = 'desc';
let selectedUsers = [];

$(document).ready(function() {
    loadUsers();
    loadStatistics();

    // Search with debounce
    let searchTimer;
    $('#search-input').on('keyup', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function() {
            currentPage = 1;
            loadUsers();
        }, 500);
    });

    // Filter changes
    $('#status-filter, #level-filter').on('change', function() {
        currentPage = 1;
        loadUsers();
    });

    // Select all functionality
    $('#select-all').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.user-checkbox').prop('checked', isChecked);
        updateSelectedUsers();
    });

    // Individual checkbox
    $(document).on('change', '.user-checkbox', function() {
        updateSelectedUsers();
    });

    // Form submission
    $('#user-form').on('submit', function(e) {
        e.preventDefault();
        saveUser();
    });
});

function loadUsers() {
    const params = {
        page: currentPage,
        search: $('#search-input').val(),
        status: $('#status-filter').val(),
        user_level: $('#level-filter').val(),
        sort_field: currentSortField,
        sort_direction: currentSortDirection,
        per_page: 10
    };

    $('#loading-state').show();
    $('#empty-state').hide();

    $.get('{{ route("users.index") }}', params)
        .done(function(response) {
            if (response.success) {
                renderUsers(response.data);
                renderPagination(response.pagination);
                $('#loading-state').hide();

                if (response.data.length === 0) {
                    $('#empty-state').show();
                }
            }
        })
        .fail(function(xhr) {
            $('#loading-state').hide();
            showError('Gagal memuat data user');
        });
}

function renderUsers(users) {
    const tbody = $('#users-tbody');
    tbody.empty();

    users.forEach(user => {
        const isDeleted = user.deleted_at !== null;
        const statusBadge = getStatusBadge(user.is_active, isDeleted);
        const levelName = user.user_level ? user.user_level.name : 'No Level';
        const lastLogin = user.last_login_at ? new Date(user.last_login_at).toLocaleDateString('id-ID') : 'Never';
        const createdAt = new Date(user.created_at).toLocaleDateString('id-ID');

        const row = `
            <tr class="${isDeleted ? 'bg-red-50' : ''}">
                <td class="px-6 py-4 whitespace-nowrap">
                    <input type="checkbox" class="user-checkbox h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded" value="${user.user_id}">
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    ${user.user_id}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                            <img class="h-10 w-10 rounded-full object-cover" src="${user.profile_picture_url || 'https://via.placeholder.com/40'}" alt="${user.name}">
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">${user.name}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${user.email}
                    ${user.email_verified_at ? '<i class="fas fa-check-circle text-green-500 ml-1" title="Verified"></i>' : '<i class="fas fa-exclamation-triangle text-yellow-500 ml-1" title="Unverified"></i>'}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${levelName}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    ${statusBadge}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${lastLogin}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${createdAt}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center space-x-2">
                        ${getActionButtons(user, isDeleted)}
                    </div>
                </td>
            </tr>
        `;

        tbody.append(row);
    });
}

function getStatusBadge(isActive, isDeleted) {
    if (isDeleted) {
        return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Dihapus</span>';
    }

    if (isActive) {
        return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>';
    }

    return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Non-Aktif</span>';
}

function getActionButtons(user, isDeleted) {
    if (isDeleted) {
        return `
            <button onclick="restoreUser('${user.user_id}')" class="text-green-600 hover:text-green-900" title="Pulihkan">
                <i class="fas fa-undo"></i>
            </button>
            <button onclick="forceDeleteUser('${user.user_id}')" class="text-red-600 hover:text-red-900" title="Hapus Permanen">
                <i class="fas fa-trash-alt"></i>
            </button>
        `;
    }

    return `
        <button onclick="viewUser('${user.user_id}')" class="text-blue-600 hover:text-blue-900" title="Lihat">
            <i class="fas fa-eye"></i>
        </button>
        <button onclick="editUser('${user.user_id}')" class="text-indigo-600 hover:text-indigo-900" title="Edit">
            <i class="fas fa-edit"></i>
        </button>
        <button onclick="toggleUserStatus('${user.user_id}')" class="text-yellow-600 hover:text-yellow-900" title="Toggle Status">
            <i class="fas fa-power-off"></i>
        </button>
        <button onclick="deleteUser('${user.user_id}')" class="text-red-600 hover:text-red-900" title="Hapus">
            <i class="fas fa-trash"></i>
        </button>
    `;
}

function renderPagination(pagination) {
    $('#showing-from').text(pagination.from || 0);
    $('#showing-to').text(pagination.to || 0);
    $('#total-records').text(pagination.total || 0);

    const nav = $('#pagination-nav');
    nav.empty();

    // Previous button
    if (pagination.current_page > 1) {
        nav.append(`<button onclick="changePage(${pagination.current_page - 1})" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">Previous</button>`);
    }

    // Page numbers
    const startPage = Math.max(1, pagination.current_page - 2);
    const endPage = Math.min(pagination.last_page, pagination.current_page + 2);

    for (let i = startPage; i <= endPage; i++) {
        const isActive = i === pagination.current_page;
        const activeClass = isActive ? 'bg-primary-50 border-primary-500 text-primary-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50';

        nav.append(`<button onclick="changePage(${i})" class="relative inline-flex items-center px-4 py-2 border text-sm font-medium ${activeClass}">${i}</button>`);
    }

    // Next button
    if (pagination.current_page < pagination.last_page) {
        nav.append(`<button onclick="changePage(${pagination.current_page + 1})" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">Next</button>`);
    }
}

function changePage(page) {
    currentPage = page;
    loadUsers();
}

function sortBy(field) {
    if (currentSortField === field) {
        currentSortDirection = currentSortDirection === 'asc' ? 'desc' : 'asc';
    } else {
        currentSortField = field;
        currentSortDirection = 'asc';
    }
    currentPage = 1;
    loadUsers();
}

function updateSelectedUsers() {
    selectedUsers = [];
    $('.user-checkbox:checked').each(function() {
        selectedUsers.push($(this).val());
    });

    if (selectedUsers.length > 0) {
        $('#bulk-actions').removeClass('hidden');
    } else {
        $('#bulk-actions').addClass('hidden');
    }

    // Update select all checkbox state
    const totalCheckboxes = $('.user-checkbox').length;
    const checkedCheckboxes = $('.user-checkbox:checked').length;

    if (checkedCheckboxes === 0) {
        $('#select-all').prop('indeterminate', false).prop('checked', false);
    } else if (checkedCheckboxes === totalCheckboxes) {
        $('#select-all').prop('indeterminate', false).prop('checked', true);
    } else {
        $('#select-all').prop('indeterminate', true);
    }
}

function loadStatistics() {
    $.get('{{ route("users.statistics") }}')
        .done(function(response) {
            if (response.success) {
                const stats = response.data.statistics;
                $('#total-users').text(stats.total_users);
                $('#active-users').text(stats.active_users);
                $('#inactive-users').text(stats.inactive_users);
                $('#deleted-users').text(stats.deleted_users);
            }
        });
}

function openCreateModal() {
    $('#modal-title').text('Tambah User Baru');
    $('#user-form')[0].reset();
    $('#user-id').val('');
    $('#is_active').prop('checked', true);
    clearErrors();
    $('#user-modal').removeClass('hidden');
}

function editUser(userId) {
    $.get(`{{ url('users') }}/${userId}`)
        .done(function(response) {
            if (response.success) {
                const user = response.data;
                $('#modal-title').text('Edit User');
                $('#user-id').val(user.user_id);
                $('#name').val(user.name);
                $('#email').val(user.email);
                $('#user_level_id').val(user.user_level_id);
                $('#is_active').prop('checked', user.is_active);
                clearErrors();
                $('#user-modal').removeClass('hidden');
            }
        })
        .fail(function(xhr) {
            showError('Gagal memuat data user');
        });
}

function saveUser() {
    const formData = new FormData($('#user-form')[0]);
    const userId = $('#user-id').val();
    const url = userId ? `{{ url('users') }}/${userId}` : '{{ route("users.store") }}';
    const method = userId ? 'PUT' : 'POST';

    if (userId && method === 'PUT') {
        formData.append('_method', 'PUT');
    }

    $('.loading-text').addClass('hidden');
    $('.loading-spinner').removeClass('hidden');

    $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-HTTP-Method-Override': method
        }
    })
    .done(function(response) {
        if (response.success) {
            closeModal();
            loadUsers();
            loadStatistics();
            showSuccess(response.message);
        }
    })
    .fail(function(xhr) {
        if (xhr.status === 422) {
            displayValidationErrors(xhr.responseJSON.errors);
        } else {
            showError(xhr.responseJSON.message || 'Terjadi kesalahan saat menyimpan user');
        }
    })
    .always(function() {
        $('.loading-text').removeClass('hidden');
        $('.loading-spinner').addClass('hidden');
    });
}

function deleteUser(userId) {
    showConfirm('User akan dipindahkan ke tempat sampah dan dapat dipulihkan kembali.', function() {
        $.ajax({
            url: `{{ url('users') }}/${userId}`,
            type: 'DELETE'
        })
        .done(function(response) {
            if (response.success) {
                loadUsers();
                loadStatistics();
                showSuccess(response.message);
            }
        })
        .fail(function(xhr) {
            showError(xhr.responseJSON.message || 'Gagal menghapus user');
        });
    });
}

function restoreUser(userId) {
    showConfirm('Pulihkan user ini?', function() {
        $.ajax({
            url: `{{ url('users') }}/${userId}/restore`,
            type: 'POST'
        })
        .done(function(response) {
            if (response.success) {
                loadUsers();
                loadStatistics();
                showSuccess(response.message);
            }
        })
        .fail(function(xhr) {
            showError(xhr.responseJSON.message || 'Gagal memulihkan user');
        });
    });
}

function forceDeleteUser(userId) {
    showConfirm('User akan dihapus permanen dan tidak dapat dipulihkan. Apakah Anda yakin?', function() {
        $.ajax({
            url: `{{ url('users') }}/${userId}/force-delete`,
            type: 'DELETE'
        })
        .done(function(response) {
            if (response.success) {
                loadUsers();
                loadStatistics();
                showSuccess(response.message);
            }
        })
        .fail(function(xhr) {
            showError(xhr.responseJSON.message || 'Gagal menghapus user permanen');
        });
    });
}

function toggleUserStatus(userId) {
    $.ajax({
        url: `{{ url('users') }}/${userId}/toggle-status`,
        type: 'POST'
    })
    .done(function(response) {
        if (response.success) {
            loadUsers();
            loadStatistics();
            showSuccess(response.message);
        }
    })
    .fail(function(xhr) {
        showError(xhr.responseJSON.message || 'Gagal mengubah status user');
    });
}

function viewUser(userId) {
    $.get(`{{ url('users') }}/${userId}`)
        .done(function(response) {
            if (response.success) {
                const user = response.data;
                const levelName = user.user_level ? user.user_level.name : 'No Level';
                const emailVerified = user.email_verified_at ? 'Terverifikasi' : 'Belum Terverifikasi';
                const lastLogin = user.last_login_at ? new Date(user.last_login_at).toLocaleString('id-ID') : 'Belum pernah login';
                const createdAt = new Date(user.created_at).toLocaleString('id-ID');

                Swal.fire({
                    title: user.name,
                    html: `
                        <div class="text-left space-y-3">
                            <div class="flex justify-center mb-4">
                                <img src="${user.profile_picture_url}" alt="${user.name}" class="w-20 h-20 rounded-full object-cover">
                            </div>
                            <div><strong>ID:</strong> ${user.user_id}</div>
                            <div><strong>Email:</strong> ${user.email}</div>
                            <div><strong>Status Email:</strong> ${emailVerified}</div>
                            <div><strong>Level:</strong> ${levelName}</div>
                            <div><strong>Status:</strong> ${user.is_active ? 'Aktif' : 'Non-Aktif'}</div>
                            <div><strong>Last Login:</strong> ${lastLogin}</div>
                            <div><strong>Dibuat:</strong> ${createdAt}</div>
                        </div>
                    `,
                    confirmButtonColor: '#3b82f6',
                    confirmButtonText: 'Tutup',
                    width: '500px'
                });
            }
        })
        .fail(function(xhr) {
            showError('Gagal memuat detail user');
        });
}

function executeBulkAction() {
    const action = $('#bulk-action-select').val();
    if (!action || selectedUsers.length === 0) {
        showError('Pilih aksi dan minimal satu user');
        return;
    }

    const actionText = {
        'activate': 'mengaktifkan',
        'deactivate': 'menonaktifkan',
        'delete': 'menghapus',
        'restore': 'memulihkan',
        'force_delete': 'menghapus permanen'
    };

    showConfirm(`${actionText[action]} ${selectedUsers.length} user yang dipilih?`, function() {
        $.ajax({
            url: '{{ route("users.bulk-action") }}',
            type: 'POST',
            data: {
                action: action,
                user_ids: selectedUsers
            }
        })
        .done(function(response) {
            if (response.success) {
                loadUsers();
                loadStatistics();
                selectedUsers = [];
                $('#bulk-actions').addClass('hidden');
                $('#bulk-action-select').val('');
                showSuccess(response.message);
            }
        })
        .fail(function(xhr) {
            showError(xhr.responseJSON.message || 'Gagal melakukan aksi bulk');
        });
    });
}

function exportData(format) {
    const params = {
        format: format,
        search: $('#search-input').val(),
        status: $('#status-filter').val(),
        user_level: $('#level-filter').val()
    };

    const queryString = $.param(params);
    window.open(`{{ route("users.export") }}?${queryString}`, '_blank');
}

function refreshData() {
    loadUsers();
    loadStatistics();
    showSuccess('Data berhasil diperbarui');
}

function closeModal() {
    $('#user-modal').addClass('hidden');
    clearErrors();
}

function clearErrors() {
    $('.text-red-600').addClass('hidden').text('');
    $('.border-red-500').removeClass('border-red-500');
}

function displayValidationErrors(errors) {
    clearErrors();

    for (const field in errors) {
        const errorElement = $(`#${field}-error`);
        const inputElement = $(`#${field}`);

        if (errorElement.length) {
            errorElement.removeClass('hidden').text(errors[field][0]);
            inputElement.addClass('border-red-500');
        }
    }
}

// Close modal when clicking outside
$(document).on('click', '#user-modal', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>
@endpush
