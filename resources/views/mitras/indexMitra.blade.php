@extends('layouts.app')

@section('title', 'Mitra Management')
@section('page-title', 'Mitra Management')

@section('breadcrumb')
    <li><a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-home"></i></a></li>
    <li><span class="text-gray-400">/</span></li>
    <li><span class="text-gray-600 font-medium">Mitra Management</span></li>
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
                            <i class="fas fa-building text-blue-600"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Mitra</dt>
                            <dd class="text-lg font-medium text-gray-900" id="total-mitras">-</dd>
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
                            <i class="fas fa-map-marked-alt text-green-600"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Dengan Points</dt>
                            <dd class="text-lg font-medium text-gray-900" id="mitras-with-points">-</dd>
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
                            <i class="fas fa-map-marker-alt text-yellow-600"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Points</dt>
                            <dd class="text-lg font-medium text-gray-900" id="total-points">-</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-plus text-purple-600"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Mitra Baru</dt>
                            <dd class="text-lg font-medium text-gray-900" id="recent-mitras">-</dd>
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
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Daftar Mitra</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">Kelola semua mitra partner dalam sistem</p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <button type="button" onclick="openCreateModal()"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        <i class="fas fa-plus -ml-1 mr-2"></i>
                        Tambah Mitra
                    </button>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="px-4 py-4 sm:px-6 bg-gray-50 border-b border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Search -->
                <div class="col-span-1 md:col-span-2">
                    <div class="relative">
                        <input type="text" id="search-input" placeholder="Cari nama PT atau ID mitra..."
                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <!-- Color Filter -->
                <div>
                    <select id="color-filter" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md">
                        <option value="">Semua Warna</option>
                        <!-- Options will be populated by JavaScript -->
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
                            <option value="delete">Hapus</option>
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
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" onclick="sortBy('mitra_id')">
                            Mitra ID <i class="fas fa-sort ml-1"></i>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" onclick="sortBy('nama_pt')">
                            Nama PT <i class="fas fa-sort ml-1"></i>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Warna & Icon
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Total Points
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" onclick="sortBy('created_at')">
                            Dibuat <i class="fas fa-sort ml-1"></i>
                        </th>
                        <th scope="col" class="relative px-6 py-3">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody id="mitras-tbody" class="bg-white divide-y divide-gray-200">
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
                <i class="fas fa-building text-4xl mb-4"></i>
                <p class="text-lg font-medium">Tidak ada mitra ditemukan</p>
                <p class="text-sm">Coba ubah filter atau tambah mitra baru</p>
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
<div id="mitra-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between pb-3">
                <h3 class="text-lg font-medium text-gray-900" id="modal-title">Tambah Mitra Baru</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="mitra-form" enctype="multipart/form-data">
                <input type="hidden" id="mitra-id" name="mitra_id">

                <div class="space-y-4">
                    <div>
                        <label for="nama_pt" class="block text-sm font-medium text-gray-700">Nama PT</label>
                        <input type="text" id="nama_pt" name="nama_pt" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                        <div class="text-red-600 text-sm mt-1 hidden" id="nama_pt-error"></div>
                    </div>

                    <div>
                        <label for="warna_pt" class="block text-sm font-medium text-gray-700">Warna PT (Hex Code)</label>
                        <div class="mt-1 flex">
                            <input type="color" id="color-picker" class="h-10 w-16 border border-gray-300 rounded-l-md cursor-pointer">
                            <input type="text" id="warna_pt" name="warna_pt" placeholder="#3B82F6"
                                   class="flex-1 border-gray-300 rounded-r-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                        </div>
                        <div class="text-red-600 text-sm mt-1 hidden" id="warna_pt-error"></div>
                        <p class="text-xs text-gray-500 mt-1">Format: #RRGGBB (contoh: #3B82F6)</p>
                    </div>

                    <div>
                        <label for="icon_pt" class="block text-sm font-medium text-gray-700">Icon PT</label>
                        <input type="file" id="icon_pt" name="icon_pt" accept="image/*"
                               class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                        <div class="text-red-600 text-sm mt-1 hidden" id="icon_pt-error"></div>
                        <p class="text-xs text-gray-500 mt-1">Format: JPEG, PNG, JPG, GIF, SVG. Max: 2MB</p>

                        <!-- Preview Icon -->
                        <div id="icon-preview" class="mt-3 hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Preview:</label>
                            <img id="preview-image" src="" alt="Preview" class="h-16 w-16 object-cover rounded-lg border border-gray-300">
                        </div>
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

<!-- Points Summary Modal -->
<div id="points-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="flex items-center justify-between pb-3 border-b">
            <h3 class="text-lg font-medium text-gray-900" id="points-modal-title">Ringkasan Points</h3>
            <button onclick="closePointsModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <div id="points-content" class="mt-4">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let currentPage = 1;
let currentSortField = 'created_at';
let currentSortDirection = 'desc';
let selectedMitras = [];
let availableColors = [];

$(document).ready(function() {
    loadMitras();
    loadStatistics();
    loadAvailableColors();

    // Search with debounce
    let searchTimer;
    $('#search-input').on('keyup', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function() {
            currentPage = 1;
            loadMitras();
        }, 500);
    });

    // Filter changes
    $('#color-filter').on('change', function() {
        currentPage = 1;
        loadMitras();
    });

    // Color picker integration
    $('#color-picker').on('change', function() {
        $('#warna_pt').val($(this).val().toUpperCase());
    });

    $('#warna_pt').on('input', function() {
        const color = $(this).val();
        if (color.match(/^#[0-9A-Fa-f]{6}$/)) {
            $('#color-picker').val(color);
        }
    });

    // File preview
    $('#icon_pt').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#preview-image').attr('src', e.target.result);
                $('#icon-preview').removeClass('hidden');
            };
            reader.readAsDataURL(file);
        } else {
            $('#icon-preview').addClass('hidden');
        }
    });

    // Select all functionality
    $('#select-all').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.mitra-checkbox').prop('checked', isChecked);
        updateSelectedMitras();
    });

    // Individual checkbox
    $(document).on('change', '.mitra-checkbox', function() {
        updateSelectedMitras();
    });

    // Form submission
    $('#mitra-form').on('submit', function(e) {
        e.preventDefault();
        saveMitra();
    });
});

function loadMitras() {
    const params = {
        page: currentPage,
        search: $('#search-input').val(),
        warna: $('#color-filter').val(),
        sort_field: currentSortField,
        sort_direction: currentSortDirection,
        per_page: 10
    };

    $('#loading-state').show();
    $('#empty-state').hide();

    $.get('{{ route("mitras.index") }}', params)
        .done(function(response) {
            if (response.success) {
                renderMitras(response.data);
                renderPagination(response.pagination);
                $('#loading-state').hide();

                if (response.data.length === 0) {
                    $('#empty-state').show();
                }
            }
        })
        .fail(function(xhr) {
            $('#loading-state').hide();
            showError('Gagal memuat data mitra');
        });
}

function renderMitras(mitras) {
    const tbody = $('#mitras-tbody');
    tbody.empty();

    mitras.forEach(mitra => {
        const createdAt = new Date(mitra.created_at).toLocaleDateString('id-ID');
        const iconHtml = mitra.icon_url
            ? `<img src="${mitra.icon_url}" alt="Icon" class="w-8 h-8 rounded-lg object-cover">`
            : `<div class="w-8 h-8 rounded-lg bg-gray-200 flex items-center justify-center"><i class="fas fa-building text-gray-400"></i></div>`;

        const row = `
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                    <input type="checkbox" class="mitra-checkbox h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded" value="${mitra.mitra_id}">
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    ${mitra.mitra_id}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">${mitra.nama_pt}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center space-x-3">
                        <div class="w-6 h-6 rounded border border-gray-300" style="background-color: ${mitra.warna_pt}" title="${mitra.warna_pt}"></div>
                        ${iconHtml}
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        ${mitra.total_points || 0} points
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${createdAt}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center space-x-2">
                        <button onclick="viewMitra('${mitra.mitra_id}')" class="text-blue-600 hover:text-blue-900" title="Lihat">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button onclick="showPoints('${mitra.mitra_id}')" class="text-green-600 hover:text-green-900" title="Lihat Points">
                            <i class="fas fa-map-marker-alt"></i>
                        </button>
                        <button onclick="editMitra('${mitra.mitra_id}')" class="text-indigo-600 hover:text-indigo-900" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="duplicateMitra('${mitra.mitra_id}')" class="text-yellow-600 hover:text-yellow-900" title="Duplikasi">
                            <i class="fas fa-copy"></i>
                        </button>
                        <button onclick="deleteMitra('${mitra.mitra_id}')" class="text-red-600 hover:text-red-900" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;

        tbody.append(row);
    });
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
    loadMitras();
}

function sortBy(field) {
    if (currentSortField === field) {
        currentSortDirection = currentSortDirection === 'asc' ? 'desc' : 'asc';
    } else {
        currentSortField = field;
        currentSortDirection = 'asc';
    }
    currentPage = 1;
    loadMitras();
}

function updateSelectedMitras() {
    selectedMitras = [];
    $('.mitra-checkbox:checked').each(function() {
        selectedMitras.push($(this).val());
    });

    if (selectedMitras.length > 0) {
        $('#bulk-actions').removeClass('hidden');
    } else {
        $('#bulk-actions').addClass('hidden');
    }

    // Update select all checkbox state
    const totalCheckboxes = $('.mitra-checkbox').length;
    const checkedCheckboxes = $('.mitra-checkbox:checked').length;

    if (checkedCheckboxes === 0) {
        $('#select-all').prop('indeterminate', false).prop('checked', false);
    } else if (checkedCheckboxes === totalCheckboxes) {
        $('#select-all').prop('indeterminate', false).prop('checked', true);
    } else {
        $('#select-all').prop('indeterminate', true);
    }
}

function loadStatistics() {
    $.get('{{ route("mitras.statistics") }}')
        .done(function(response) {
            if (response.success) {
                const stats = response.data.statistics;
                $('#total-mitras').text(stats.total_mitras);
                $('#mitras-with-points').text(stats.mitras_with_points);
                $('#total-points').text(stats.total_points);
                $('#recent-mitras').text(stats.recent_mitras);
            }
        });
}



function loadAvailableColors() {
    $.get('{{ route("mitras.colors") }}')
        .done(function(response) {
            if (response.success) {
                const colorFilter = $('#color-filter');
                const currentValue = colorFilter.val();

                // Clear existing options except first
                colorFilter.find('option:not(:first)').remove();

                // Add color options
                response.data.forEach(color => {
                    const option = $(`<option value="${color}">${color}</option>`);
                    colorFilter.append(option);
                });

                // Restore selected value
                colorFilter.val(currentValue);
            }
        });
}

function openCreateModal() {
    $('#modal-title').text('Tambah Mitra Baru');
    $('#mitra-form')[0].reset();
    $('#mitra-id').val('');
    $('#warna_pt').val('#3B82F6');
    $('#color-picker').val('#3B82F6');
    $('#icon-preview').addClass('hidden');
    clearErrors();
    $('#mitra-modal').removeClass('hidden');
}

function editMitra(mitraId) {
    $.get(`{{ url('mitras') }}/${mitraId}`)
        .done(function(response) {
            if (response.success) {
                const mitra = response.data;
                $('#modal-title').text('Edit Mitra');
                $('#mitra-id').val(mitra.mitra_id);
                $('#nama_pt').val(mitra.nama_pt);
                $('#warna_pt').val(mitra.warna_pt);
                $('#color-picker').val(mitra.warna_pt);

                // Show current icon if exists
                if (mitra.icon_url) {
                    $('#preview-image').attr('src', mitra.icon_url);
                    $('#icon-preview').removeClass('hidden');
                }

                clearErrors();
                $('#mitra-modal').removeClass('hidden');
            }
        })
        .fail(function(xhr) {
            showError('Gagal memuat data mitra');
        });
}

function saveMitra() {
    const formData = new FormData($('#mitra-form')[0]);
    const mitraId = $('#mitra-id').val();
    const url = mitraId ? `{{ url('mitras') }}/${mitraId}` : '{{ route("mitras.store") }}';
    const method = mitraId ? 'PUT' : 'POST';

    if (mitraId && method === 'PUT') {
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
            loadMitras();
            loadStatistics();
            loadAvailableColors();
            showSuccess(response.message);
        }
    })
    .fail(function(xhr) {
        if (xhr.status === 422) {
            displayValidationErrors(xhr.responseJSON.errors);
        } else {
            showError(xhr.responseJSON.message || 'Terjadi kesalahan saat menyimpan mitra');
        }
    })
    .always(function() {
        $('.loading-text').removeClass('hidden');
        $('.loading-spinner').addClass('hidden');
    });
}

function deleteMitra(mitraId) {
    showConfirm('Mitra ini akan dihapus permanen. Pastikan tidak ada points yang terkait!', function() {
        $.ajax({
            url: `{{ url('mitras') }}/${mitraId}`,
            type: 'DELETE'
        })
        .done(function(response) {
            if (response.success) {
                loadMitras();
                loadStatistics();
                showSuccess(response.message);
            }
        })
        .fail(function(xhr) {
            showError(xhr.responseJSON.message || 'Gagal menghapus mitra');
        });
    });
}

function duplicateMitra(mitraId) {
    showConfirm('Duplikasi mitra ini?', function() {
        $.ajax({
            url: `{{ url('mitras') }}/${mitraId}/duplicate`,
            type: 'POST'
        })
        .done(function(response) {
            if (response.success) {
                loadMitras();
                loadStatistics();
                showSuccess(response.message);
            }
        })
        .fail(function(xhr) {
            showError(xhr.responseJSON.message || 'Gagal menduplikasi mitra');
        });
    });
}

function viewMitra(mitraId) {
    $.get(`{{ url('mitras') }}/${mitraId}`)
        .done(function(response) {
            if (response.success) {
                const mitra = response.data;
                const iconHtml = mitra.icon_url
                    ? `<img src="${mitra.icon_url}" alt="Icon" class="w-16 h-16 rounded-lg object-cover mx-auto mb-4">`
                    : '';
                const createdAt = new Date(mitra.created_at).toLocaleString('id-ID');
                const totalPoints = mitra.mitra_turunans ? mitra.mitra_turunans.length : 0;

                Swal.fire({
                    title: mitra.nama_pt,
                    html: `
                        <div class="text-left space-y-3">
                            ${iconHtml}
                            <div><strong>ID:</strong> ${mitra.mitra_id}</div>
                            <div><strong>Nama PT:</strong> ${mitra.nama_pt}</div>
                            <div class="flex items-center">
                                <strong class="mr-2">Warna:</strong>
                                <div class="w-6 h-6 rounded border border-gray-300 mr-2" style="background-color: ${mitra.warna_pt}"></div>
                                <span>${mitra.warna_pt}</span>
                            </div>
                            <div><strong>Total Points:</strong> ${totalPoints}</div>
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
            showError('Gagal memuat detail mitra');
        });
}

function showPoints(mitraId) {
    $.get(`{{ url('mitras') }}/${mitraId}/points-summary`)
        .done(function(response) {
            if (response.success) {
                const data = response.data;
                let pointsHtml = '';

                if (data.points && data.points.length > 0) {
                    pointsHtml = `
                        <div class="mt-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-3">Points Terbaru (10 terakhir):</h4>
                            <div class="space-y-2 max-h-64 overflow-y-auto">
                    `;

                    data.points.forEach(point => {
                        const createdAt = new Date(point.created_at).toLocaleDateString('id-ID');
                        const hasFile = point.nama_file ? '<i class="fas fa-paperclip text-green-500"></i>' : '<i class="fas fa-times text-gray-400"></i>';

                        pointsHtml += `
                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                <div>
                                    <div class="text-sm font-medium">${point.nama_point || 'Unnamed Point'}</div>
                                    <div class="text-xs text-gray-500">${point.koordinat} • ${createdAt}</div>
                                </div>
                                <div>${hasFile}</div>
                            </div>
                        `;
                    });

                    pointsHtml += '</div></div>';
                } else {
                    pointsHtml = '<div class="text-center text-gray-500 py-8">Belum ada points untuk mitra ini</div>';
                }

                $('#points-modal-title').text(`Points Summary - ${data.points[0]?.mitra?.nama_pt || 'Mitra'}`);
                $('#points-content').html(`
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        <div class="text-center p-3 bg-blue-50 rounded-lg">
                            <div class="text-2xl font-bold text-blue-600">${data.total_points}</div>
                            <div class="text-xs text-blue-600">Total Points</div>
                        </div>
                        <div class="text-center p-3 bg-green-50 rounded-lg">
                            <div class="text-2xl font-bold text-green-600">${data.points_with_files}</div>
                            <div class="text-xs text-green-600">With Files</div>
                        </div>
                        <div class="text-center p-3 bg-yellow-50 rounded-lg">
                            <div class="text-2xl font-bold text-yellow-600">${data.points_without_files}</div>
                            <div class="text-xs text-yellow-600">Without Files</div>
                        </div>
                        <div class="text-center p-3 bg-purple-50 rounded-lg">
                            <div class="text-2xl font-bold text-purple-600">${data.recent_points}</div>
                            <div class="text-xs text-purple-600">Recent (7 days)</div>
                        </div>
                    </div>
                    ${pointsHtml}
                `);

                $('#points-modal').removeClass('hidden');
            }
        })
        .fail(function(xhr) {
            showError('Gagal memuat ringkasan points');
        });
}

function executeBulkAction() {
    const action = $('#bulk-action-select').val();
    if (!action || selectedMitras.length === 0) {
        showError('Pilih aksi dan minimal satu mitra');
        return;
    }

    const actionText = {
        'delete': 'menghapus'
    };

    showConfirm(`${actionText[action]} ${selectedMitras.length} mitra yang dipilih?`, function() {
        $.ajax({
            url: '{{ route("mitras.bulk-action") }}',
            type: 'POST',
            data: {
                action: action,
                mitra_ids: selectedMitras
            }
        })
        .done(function(response) {
            if (response.success) {
                loadMitras();
                loadStatistics();
                selectedMitras = [];
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
        warna: $('#color-filter').val()
    };

    const queryString = $.param(params);
    window.open(`{{ route("mitras.export") }}?${queryString}`, '_blank');
}

function refreshData() {
    loadMitras();
    loadStatistics();
    loadAvailableColors();
    showSuccess('Data berhasil diperbarui');
}

function closeModal() {
    $('#mitra-modal').addClass('hidden');
    clearErrors();
}

function closePointsModal() {
    $('#points-modal').addClass('hidden');
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

// Close modals when clicking outside
$(document).on('click', '#mitra-modal, #points-modal', function(e) {
    if (e.target === this) {
        if (e.target.id === 'mitra-modal') {
            closeModal();
        } else {
            closePointsModal();
        }
    }
});
</script>
@endpush
