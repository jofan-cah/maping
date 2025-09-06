@extends('layouts.app')

@section('title', 'User Level Management')
@section('page-title', 'User Level Management')

@section('breadcrumb')
    <li><a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-home"></i></a></li>
    <li><span class="text-gray-400">/</span></li>
    <li><span class="text-gray-600 font-medium">User Level Management</span></li>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4" id="stats-container">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-layer-group text-purple-600"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Levels</dt>
                            <dd class="text-lg font-medium text-gray-900" id="total-levels">-</dd>
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
                            <dt class="text-sm font-medium text-gray-500 truncate">Active Levels</dt>
                            <dd class="text-lg font-medium text-gray-900" id="active-levels">-</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-shield-alt text-blue-600"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">System Levels</dt>
                            <dd class="text-lg font-medium text-gray-900" id="system-levels">-</dd>
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
                            <dt class="text-sm font-medium text-gray-500 truncate">Deleted Levels</dt>
                            <dd class="text-lg font-medium text-gray-900" id="deleted-levels">-</dd>
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
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Daftar User Level</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">Kelola semua level user dalam sistem</p>
                </div>
                <div class="mt-4 sm:mt-0 space-x-2">
                    <button type="button" onclick="createDefaults()"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        <i class="fas fa-magic -ml-1 mr-2"></i>
                        Create Defaults
                    </button>
                    <button type="button" onclick="openCreateModal()"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        <i class="fas fa-plus -ml-1 mr-2"></i>
                        Tambah Level
                    </button>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="px-4 py-4 sm:px-6 bg-gray-50 border-b border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Search -->
                <div class="col-span-1 md:col-span-1">
                    <div class="relative">
                        <input type="text" id="search-input" placeholder="Cari nama atau deskripsi..."
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

                <!-- System Filter -->
                <div>
                    <select id="system-filter" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md">
                        <option value="">Semua Jenis</option>
                        <option value="1">System Level</option>
                        <option value="0">Custom Level</option>
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
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" onclick="sortBy('user_level_id')">
                            Level ID <i class="fas fa-sort ml-1"></i>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" onclick="sortBy('name')">
                            Nama <i class="fas fa-sort ml-1"></i>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Deskripsi
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" onclick="sortBy('priority')">
                            Priority <i class="fas fa-sort ml-1"></i>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Users
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Permissions
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" onclick="sortBy('created_at')">
                            Dibuat <i class="fas fa-sort ml-1"></i>
                        </th>
                        <th scope="col" class="relative px-6 py-3">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody id="levels-tbody" class="bg-white divide-y divide-gray-200">
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
                <i class="fas fa-layer-group text-4xl mb-4"></i>
                <p class="text-lg font-medium">Tidak ada user level ditemukan</p>
                <p class="text-sm">Coba ubah filter atau tambah level baru</p>
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
<div id="level-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between pb-3">
                <h3 class="text-lg font-medium text-gray-900" id="modal-title">Tambah Level Baru</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="level-form">
                <input type="hidden" id="user-level-id" name="user_level_id">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nama Level</label>
                        <input type="text" id="name" name="name" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                        <div class="text-red-600 text-sm mt-1 hidden" id="name-error"></div>
                    </div>

                    <div>
                        <label for="priority" class="block text-sm font-medium text-gray-700">Priority (0-100)</label>
                        <input type="number" id="priority" name="priority" min="0" max="100" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                        <div class="text-red-600 text-sm mt-1 hidden" id="priority-error"></div>
                        <p class="text-xs text-gray-500 mt-1">Semakin tinggi priority, semakin tinggi level</p>
                    </div>

                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                        <textarea id="description" name="description" rows="3"
                                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"></textarea>
                        <div class="text-red-600 text-sm mt-1 hidden" id="description-error"></div>
                    </div>
                </div>

                <!-- Permissions Section -->
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Permissions</label>
                    <div class="border border-gray-300 rounded-md p-4 bg-gray-50 max-h-64 overflow-y-auto">
                        <div id="permissions-container">
                            <!-- Permissions will be loaded here -->
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div class="flex items-center">
                        <input type="checkbox" id="is_active" name="is_active" value="1" checked
                               class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                        <label for="is_active" class="ml-2 block text-sm text-gray-900">
                            Level Aktif
                        </label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="is_system" name="is_system" value="1"
                               class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                        <label for="is_system" class="ml-2 block text-sm text-gray-900">
                            System Level <span class="text-xs text-red-600">(Tidak dapat dihapus)</span>
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
let currentSortField = 'priority';
let currentSortDirection = 'desc';
let selectedLevels = [];
let availablePermissions = {};

$(document).ready(function() {
    loadLevels();
    loadStatistics();
    loadAvailablePermissions();

    // Search with debounce
    let searchTimer;
    $('#search-input').on('keyup', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function() {
            currentPage = 1;
            loadLevels();
        }, 500);
    });

    // Filter changes
    $('#status-filter, #system-filter').on('change', function() {
        currentPage = 1;
        loadLevels();
    });

    // Select all functionality
    $('#select-all').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.level-checkbox').prop('checked', isChecked);
        updateSelectedLevels();
    });

    // Individual checkbox
    $(document).on('change', '.level-checkbox', function() {
        updateSelectedLevels();
    });

    // Form submission
    $('#level-form').on('submit', function(e) {
        e.preventDefault();
        saveLevel();
    });
});

// Tambahkan ini di user-levels index view untuk debug

function loadLevels() {
    const params = {
        page: currentPage,
        search: $('#search-input').val(),
        status: $('#status-filter').val(),
        system: $('#system-filter').val(),
        sort_field: currentSortField,
        sort_direction: currentSortDirection,
        per_page: 10
    };

    console.log('Loading levels with params:', params); // Debug log

    $('#loading-state').show();
    $('#empty-state').hide();

    $.get('{{ route("user-levels.index") }}', params)
        .done(function(response) {
            console.log('Response received:', response); // Debug log

            if (response.success) {
                console.log('Data count:', response.data.length); // Debug log
                console.log('Pagination:', response.pagination); // Debug log

                renderLevels(response.data);
                renderPagination(response.pagination);
                $('#loading-state').hide();

                if (response.data.length === 0) {
                    $('#empty-state').show();
                }
            } else {
                console.error('Response not successful:', response);
            }
        })
        .fail(function(xhr) {
            console.error('AJAX request failed:', xhr);
            console.error('Response Text:', xhr.responseText);
            $('#loading-state').hide();
            showError('Gagal memuat data level');
        });
}

function renderLevels(levels) {
    console.log('Rendering levels:', levels); // Debug log

    const tbody = $('#levels-tbody');
    tbody.empty();

    if (!levels || levels.length === 0) {
        console.log('No levels to render');
        return;
    }

    levels.forEach((level, index) => {
        console.log(`Rendering level ${index}:`, level); // Debug log

        const isDeleted = level.deleted_at !== null;
        const statusBadge = getStatusBadge(level.is_active, isDeleted);
        const systemBadge = level.is_system ? '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">System</span>' : '';
        const permissionsText = level.permissions && level.permissions.length > 0
            ? level.permissions.slice(0, 3).join(', ') + (level.permissions.length > 3 ? '...' : '')
            : 'No permissions';
        const createdAt = new Date(level.created_at).toLocaleDateString('id-ID');

        const row = `
            <tr class="${isDeleted ? 'bg-red-50' : ''}">
                <td class="px-6 py-4 whitespace-nowrap">
                    <input type="checkbox" class="level-checkbox h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded" value="${level.user_level_id}">
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    ${level.user_level_id}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">${level.name}</div>
                    ${systemBadge}
                </td>
                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                    ${level.description || '-'}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        ${level.priority}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <div class="text-sm text-gray-900">${level.total_users || 0} total</div>
                    <div class="text-xs text-gray-500">${level.active_users || 0} active</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    ${statusBadge}
                </td>
                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" title="${level.permissions ? level.permissions.join(', ') : 'No permissions'}">
                    ${permissionsText}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${createdAt}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center space-x-2">
                        ${getActionButtons(level, isDeleted)}
                    </div>
                </td>
            </tr>
        `;

        tbody.append(row);
    });

    console.log('Finished rendering', levels.length, 'levels');
}
function renderLevels(levels) {
    const tbody = $('#levels-tbody');
    tbody.empty();

    levels.forEach(level => {
        const isDeleted = level.deleted_at !== null;
        const statusBadge = getStatusBadge(level.is_active, isDeleted);
        const systemBadge = level.is_system ? '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">System</span>' : '';
        const permissionsText = level.permissions && level.permissions.length > 0
            ? level.permissions.slice(0, 3).join(', ') + (level.permissions.length > 3 ? '...' : '')
            : 'No permissions';
        const createdAt = new Date(level.created_at).toLocaleDateString('id-ID');

        const row = `
            <tr class="${isDeleted ? 'bg-red-50' : ''}">
                <td class="px-6 py-4 whitespace-nowrap">
                    <input type="checkbox" class="level-checkbox h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded" value="${level.user_level_id}">
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    ${level.user_level_id}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">${level.name}</div>
                    ${systemBadge}
                </td>
                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                    ${level.description || '-'}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        ${level.priority}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <div class="text-sm text-gray-900">${level.total_users || 0} total</div>
                    <div class="text-xs text-gray-500">${level.active_users || 0} active</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    ${statusBadge}
                </td>
                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" title="${level.permissions ? level.permissions.join(', ') : 'No permissions'}">
                    ${permissionsText}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${createdAt}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center space-x-2">
                        ${getActionButtons(level, isDeleted)}
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

function getActionButtons(level, isDeleted) {
    if (isDeleted) {
        return `
            <button onclick="restoreLevel('${level.user_level_id}')" class="text-green-600 hover:text-green-900" title="Pulihkan">
                <i class="fas fa-undo"></i>
            </button>
            ${!level.is_system ? `<button onclick="forceDeleteLevel('${level.user_level_id}')" class="text-red-600 hover:text-red-900" title="Hapus Permanen"><i class="fas fa-trash-alt"></i></button>` : ''}
        `;
    }

    return `
        <button onclick="viewLevel('${level.user_level_id}')" class="text-blue-600 hover:text-blue-900" title="Lihat">
            <i class="fas fa-eye"></i>
        </button>
        <button onclick="editLevel('${level.user_level_id}')" class="text-indigo-600 hover:text-indigo-900" title="Edit">
            <i class="fas fa-edit"></i>
        </button>
        ${!level.is_system ? `
            <button onclick="toggleLevelStatus('${level.user_level_id}')" class="text-yellow-600 hover:text-yellow-900" title="Toggle Status">
                <i class="fas fa-power-off"></i>
            </button>
            <button onclick="deleteLevel('${level.user_level_id}')" class="text-red-600 hover:text-red-900" title="Hapus">
                <i class="fas fa-trash"></i>
            </button>
        ` : ''}
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
    loadLevels();
}

function sortBy(field) {
    if (currentSortField === field) {
        currentSortDirection = currentSortDirection === 'asc' ? 'desc' : 'asc';
    } else {
        currentSortField = field;
        currentSortDirection = 'asc';
    }
    currentPage = 1;
    loadLevels();
}

function updateSelectedLevels() {
    selectedLevels = [];
    $('.level-checkbox:checked').each(function() {
        selectedLevels.push($(this).val());
    });

    if (selectedLevels.length > 0) {
        $('#bulk-actions').removeClass('hidden');
    } else {
        $('#bulk-actions').addClass('hidden');
    }

    // Update select all checkbox state
    const totalCheckboxes = $('.level-checkbox').length;
    const checkedCheckboxes = $('.level-checkbox:checked').length;

    if (checkedCheckboxes === 0) {
        $('#select-all').prop('indeterminate', false).prop('checked', false);
    } else if (checkedCheckboxes === totalCheckboxes) {
        $('#select-all').prop('indeterminate', false).prop('checked', true);
    } else {
        $('#select-all').prop('indeterminate', true);
    }
}

function loadStatistics() {
    $.get('{{ route("user-levels.statistics") }}')
        .done(function(response) {
            if (response.success) {
                const stats = response.data.statistics;
                $('#total-levels').text(stats.total_levels);
                $('#active-levels').text(stats.active_levels);
                $('#system-levels').text(stats.system_levels);
                $('#deleted-levels').text(stats.deleted_levels);
            }
        });
}

function loadAvailablePermissions() {
    $.get('{{ route("user-levels.available-permissions") }}')
        .done(function(response) {
            console.log('Available permissions response:', response); // Debug log
            if (response.success) {
                availablePermissions = response.data;
            }
        });
}

function renderPermissions(selectedPermissions = []) {
    const container = $('#permissions-container');
    container.empty();

    Object.keys(availablePermissions).forEach(category => {
        const categoryDiv = $(`
            <div class="mb-4">
                <h4 class="text-sm font-medium text-gray-900 mb-2 capitalize">${category.replace('_', ' ')}</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2" id="category-${category}">
                </div>
            </div>
        `);

        container.append(categoryDiv);

        const categoryContainer = $(`#category-${category}`);
        Object.keys(availablePermissions[category]).forEach(permission => {
            const isChecked = selectedPermissions.includes(permission) ? 'checked' : '';
            const permissionDiv = $(`
                <div class="flex items-center">
                    <input type="checkbox" name="permissions[]" value="${permission}" ${isChecked}
                           class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                    <label class="ml-2 text-sm text-gray-700">${availablePermissions[category][permission]}</label>
                </div>
            `);
            categoryContainer.append(permissionDiv);
        });
    });
}

function openCreateModal() {
    $('#modal-title').text('Tambah Level Baru');
    $('#level-form')[0].reset();
    $('#user-level-id').val('');
    $('#is_active').prop('checked', true);
    $('#is_system').prop('checked', false);
    renderPermissions();
    clearErrors();
    $('#level-modal').removeClass('hidden');
}

function editLevel(levelId) {
    $.get(`{{ url('user-levels') }}/${levelId}`)
        .done(function(response) {
            if (response.success) {
                const level = response.data;
                $('#modal-title').text('Edit Level');
                $('#user-level-id').val(level.user_level_id);
                $('#name').val(level.name);
                $('#description').val(level.description);
                $('#priority').val(level.priority);
                $('#is_active').prop('checked', level.is_active);
                $('#is_system').prop('checked', level.is_system);
                renderPermissions(level.permissions || []);
                clearErrors();
                $('#level-modal').removeClass('hidden');
            }
        })
        .fail(function(xhr) {
            showError('Gagal memuat data level');
        });
}

function saveLevel() {
    const formData = new FormData($('#level-form')[0]);
    const levelId = $('#user-level-id').val();
    const url = levelId ? `{{ url('user-levels') }}/${levelId}` : '{{ route("user-levels.store") }}';
    const method = levelId ? 'PUT' : 'POST';

    if (levelId && method === 'PUT') {
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
            loadLevels();
            loadStatistics();
            showSuccess(response.message);
        }
    })
    .fail(function(xhr) {
        if (xhr.status === 422) {
            displayValidationErrors(xhr.responseJSON.errors);
        } else {
            showError(xhr.responseJSON.message || 'Terjadi kesalahan saat menyimpan level');
        }
    })
    .always(function() {
        $('.loading-text').removeClass('hidden');
        $('.loading-spinner').addClass('hidden');
    });
}

function deleteLevel(levelId) {
    showConfirm('Level akan dipindahkan ke tempat sampah dan dapat dipulihkan kembali.', function() {
        $.ajax({
            url: `{{ url('user-levels') }}/${levelId}`,
            type: 'DELETE'
        })
        .done(function(response) {
            if (response.success) {
                loadLevels();
                loadStatistics();
                showSuccess(response.message);
            }
        })
        .fail(function(xhr) {
            showError(xhr.responseJSON.message || 'Gagal menghapus level');
        });
    });
}

function restoreLevel(levelId) {
    showConfirm('Pulihkan level ini?', function() {
        $.ajax({
            url: `{{ url('user-levels') }}/${levelId}/restore`,
            type: 'POST'
        })
        .done(function(response) {
            if (response.success) {
                loadLevels();
                loadStatistics();
                showSuccess(response.message);
            }
        })
        .fail(function(xhr) {
            showError(xhr.responseJSON.message || 'Gagal memulihkan level');
        });
    });
}

function forceDeleteLevel(levelId) {
    showConfirm('Level akan dihapus permanen dan tidak dapat dipulihkan. Apakah Anda yakin?', function() {
        $.ajax({
            url: `{{ url('user-levels') }}/${levelId}/force-delete`,
            type: 'DELETE'
        })
        .done(function(response) {
            if (response.success) {
                loadLevels();
                loadStatistics();
                showSuccess(response.message);
            }
        })
        .fail(function(xhr) {
            showError(xhr.responseJSON.message || 'Gagal menghapus level permanen');
        });
    });
}

function toggleLevelStatus(levelId) {
    $.ajax({
        url: `{{ url('user-levels') }}/${levelId}/toggle-status`,
        type: 'POST'
    })
    .done(function(response) {
        if (response.success) {
            loadLevels();
            loadStatistics();
            showSuccess(response.message);
        }
    })
    .fail(function(xhr) {
        showError(xhr.responseJSON.message || 'Gagal mengubah status level');
    });
}

function viewLevel(levelId) {
    $.get(`{{ url('user-levels') }}/${levelId}`)
        .done(function(response) {
            if (response.success) {
                const level = response.data;
                const permissionsList = level.permissions && level.permissions.length > 0
                    ? level.permissions.join(', ')
                    : 'Tidak ada permissions';
                const createdAt = new Date(level.created_at).toLocaleString('id-ID');

                Swal.fire({
                    title: level.name,
                    html: `
                        <div class="text-left space-y-3">
                            <div><strong>ID:</strong> ${level.user_level_id}</div>
                            <div><strong>Deskripsi:</strong> ${level.description || 'Tidak ada deskripsi'}</div>
                            <div><strong>Priority:</strong> ${level.priority}</div>
                            <div><strong>Status:</strong> ${level.is_active ? 'Aktif' : 'Non-Aktif'}</div>
                            <div><strong>System Level:</strong> ${level.is_system ? 'Ya' : 'Tidak'}</div>
                            <div><strong>Total Users:</strong> ${level.total_users || 0}</div>
                            <div><strong>Active Users:</strong> ${level.active_users || 0}</div>
                            <div><strong>Permissions:</strong><br><small>${permissionsList}</small></div>
                            <div><strong>Dibuat:</strong> ${createdAt}</div>
                        </div>
                    `,
                    confirmButtonColor: '#3b82f6',
                    confirmButtonText: 'Tutup',
                    width: '600px'
                });
            }
        })
        .fail(function(xhr) {
            showError('Gagal memuat detail level');
        });
}

function executeBulkAction() {
    const action = $('#bulk-action-select').val();
    if (!action || selectedLevels.length === 0) {
        showError('Pilih aksi dan minimal satu level');
        return;
    }

    const actionText = {
        'activate': 'mengaktifkan',
        'deactivate': 'menonaktifkan',
        'delete': 'menghapus',
        'restore': 'memulihkan',
        'force_delete': 'menghapus permanen'
    };

    showConfirm(`${actionText[action]} ${selectedLevels.length} level yang dipilih?`, function() {
        $.ajax({
            url: '{{ route("user-levels.bulk-action") }}',
            type: 'POST',
            data: {
                action: action,
                user_level_ids: selectedLevels
            }
        })
        .done(function(response) {
            if (response.success) {
                loadLevels();
                loadStatistics();
                selectedLevels = [];
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

function createDefaults() {
    showConfirm('Buat default user levels (Super Administrator, Administrator, Editor, User)?', function() {
        $.ajax({
            url: '{{ route("user-levels.create-defaults") }}',
            type: 'POST'
        })
        .done(function(response) {
            if (response.success) {
                loadLevels();
                loadStatistics();
                showSuccess(response.message);
            }
        })
        .fail(function(xhr) {
            showError(xhr.responseJSON.message || 'Gagal membuat default levels');
        });
    });
}

function exportData(format) {
    const params = {
        format: format,
        search: $('#search-input').val(),
        status: $('#status-filter').val(),
        system: $('#system-filter').val()
    };

    const queryString = $.param(params);
    window.open(`{{ route("user-levels.export") }}?${queryString}`, '_blank');
}

function refreshData() {
    loadLevels();
    loadStatistics();
    showSuccess('Data berhasil diperbarui');
}

function closeModal() {
    $('#level-modal').addClass('hidden');
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
$(document).on('click', '#level-modal', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>
@endpush
