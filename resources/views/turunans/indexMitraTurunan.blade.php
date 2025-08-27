@extends('layouts.app')

@section('title', 'Point Management')
@section('page-title', 'Point Management')

@section('breadcrumb')
    <li><a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-home"></i></a></li>
    <li><span class="text-gray-400">/</span></li>
    <li><a href="{{ route('mitras.index') }}" class="text-gray-400 hover:text-gray-600">Mitra</a></li>
    <li><span class="text-gray-400">/</span></li>
    <li><span class="text-gray-600 font-medium">Points</span></li>
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
                            <i class="fas fa-map-marker-alt text-blue-600"></i>
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
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-paperclip text-green-600"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">With Files</dt>
                            <dd class="text-lg font-medium text-gray-900" id="points-with-files">-</dd>
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
                            <i class="fas fa-file-excel text-yellow-600"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Without Files</dt>
                            <dd class="text-lg font-medium text-gray-900" id="points-without-files">-</dd>
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
                            <i class="fas fa-clock text-purple-600"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Recent</dt>
                            <dd class="text-lg font-medium text-gray-900" id="recent-points">-</dd>
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
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Daftar Point Mapping</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">Kelola semua titik koordinat infrastruktur</p>
                </div>
                <div class="mt-4 sm:mt-0 space-x-2">
                    <button type="button" onclick="openKmzModal()"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        <i class="fas fa-upload -ml-1 mr-2"></i>
                        Upload KMZ
                    </button>
                    <button type="button" onclick="openCreateModal()"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        <i class="fas fa-plus -ml-1 mr-2"></i>
                        Tambah Point
                    </button>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="px-4 py-4 sm:px-6 bg-gray-50 border-b border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <!-- Search -->
                <div class="col-span-1 md:col-span-2">
                    <div class="relative">
                        <input type="text" id="search-input" placeholder="Cari nama point, koordinat, atau mitra..."
                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <!-- Mitra Filter -->
                <div>
                    <select id="mitra-filter" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md">
                        <option value="">Semua Mitra</option>
                        @foreach($mitras as $mitra)
                            <option value="{{ $mitra->mitra_id }}">{{ $mitra->nama_pt }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Type Point Filter -->
                <div>
                    <select id="type-filter" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md">
                        <option value="">Semua Type</option>
                        @foreach($typePoints as $type)
                            <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- File Filter -->
                <div>
                    <select id="file-filter" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md">
                        <option value="">Semua File</option>
                        <option value="1">Dengan File</option>
                        <option value="0">Tanpa File</option>
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
                    <button type="button" onclick="showMapView()" class="text-sm text-primary-600 hover:text-primary-900">
                        <i class="fas fa-map mr-1"></i>Map View
                    </button>
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
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" onclick="sortBy('mitra_turunan_id')">
                            Point ID <i class="fas fa-sort ml-1"></i>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" onclick="sortBy('nama_point')">
                            Nama Point <i class="fas fa-sort ml-1"></i>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" onclick="sortBy('type_point')">
                            Type <i class="fas fa-sort ml-1"></i>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Mitra
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Koordinat
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            File
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" onclick="sortBy('created_at')">
                            Dibuat <i class="fas fa-sort ml-1"></i>
                        </th>
                        <th scope="col" class="relative px-6 py-3">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody id="points-tbody" class="bg-white divide-y divide-gray-200">
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
                <i class="fas fa-map-marker-alt text-4xl mb-4"></i>
                <p class="text-lg font-medium">Tidak ada point ditemukan</p>
                <p class="text-sm">Coba ubah filter atau tambah point baru</p>
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

<!-- Create/Edit Point Modal -->
<div id="point-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between pb-3">
                <h3 class="text-lg font-medium text-gray-900" id="modal-title">Tambah Point Baru</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="point-form" enctype="multipart/form-data">
                <input type="hidden" id="point-id" name="mitra_turunan_id">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="mitra_id" class="block text-sm font-medium text-gray-700">Mitra</label>
                        <select id="mitra_id" name="mitra_id" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                            <option value="">Pilih Mitra</option>
                            @foreach($mitras as $mitra)
                                <option value="{{ $mitra->mitra_id }}" data-color="{{ $mitra->warna_pt }}">{{ $mitra->nama_pt }}</option>
                            @endforeach
                        </select>
                        <div class="text-red-600 text-sm mt-1 hidden" id="mitra_id-error"></div>
                    </div>

                    <div>
                        <label for="nama_point" class="block text-sm font-medium text-gray-700">Nama Point</label>
                        <input type="text" id="nama_point" name="nama_point"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                        <div class="text-red-600 text-sm mt-1 hidden" id="nama_point-error"></div>
                    </div>

                    <div>
                        <label for="type_point" class="block text-sm font-medium text-gray-700">Type Point</label>
                        <select id="type_point" name="type_point"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                            <option value="">Pilih Type</option>
                            <option value="tiang">Tiang</option>
                            <option value="odp">ODP</option>
                            <option value="olt">OLT</option>
                            <option value="onu">ONU</option>
                            <option value="odc">ODC</option>
                            <option value="joint">Joint</option>
                            <option value="manhole">Manhole</option>
                            <option value="hanhole">Hanhole</option>
                            <option value="closure">Closure</option>
                            <option value="splitter">Splitter</option>
                        </select>
                        <div class="text-red-600 text-sm mt-1 hidden" id="type_point-error"></div>
                    </div>

                    <div class="md:col-span-1">
                        <label for="koordinat" class="block text-sm font-medium text-gray-700">Koordinat</label>
                        <input type="text" id="koordinat" name="koordinat" required
                               placeholder="-6.2088,106.8456"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                        <div class="text-red-600 text-sm mt-1 hidden" id="koordinat-error"></div>
                        <p class="text-xs text-gray-500 mt-1">Format: latitude,longitude (contoh: -6.2088,106.8456)</p>

                        <!-- Map for coordinate picking -->
                        <div class="mt-2">
                            <button type="button" onclick="openMapPicker()" class="text-sm text-primary-600 hover:text-primary-900">
                                <i class="fas fa-map-marker-alt mr-1"></i>Pilih dari Map
                            </button>
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label for="deskripsi" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                        <textarea id="deskripsi" name="deskripsi" rows="3"
                                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"></textarea>
                        <div class="text-red-600 text-sm mt-1 hidden" id="deskripsi-error"></div>
                    </div>

                    <div class="md:col-span-2">
                        <label for="nama_file" class="block text-sm font-medium text-gray-700">File Attachment</label>
                        <input type="file" id="nama_file" name="nama_file"
                               class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                        <div class="text-red-600 text-sm mt-1 hidden" id="nama_file-error"></div>
                        <p class="text-xs text-gray-500 mt-1">Max: 10MB. Format: PDF, DOC, XLS, JPG, PNG</p>
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

<!-- KMZ Upload Modal -->
<div id="kmz-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between pb-3">
                <h3 class="text-lg font-medium text-gray-900">Upload KMZ/KML File</h3>
                <button onclick="closeKmzModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="kmz-form" enctype="multipart/form-data">
                <div class="space-y-4">
                    <div>
                        <label for="kmz_mitra_id" class="block text-sm font-medium text-gray-700">Pilih Mitra</label>
                        <select id="kmz_mitra_id" name="mitra_id" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                            <option value="">Pilih Mitra</option>
                            @foreach($mitras as $mitra)
                                <option value="{{ $mitra->mitra_id }}">{{ $mitra->nama_pt }}</option>
                            @endforeach
                        </select>
                        <div class="text-red-600 text-sm mt-1 hidden" id="kmz_mitra_id-error"></div>
                    </div>

                    <div>
                        <label for="kmz_file" class="block text-sm font-medium text-gray-700">Upload File KMZ/KML</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                            <div class="space-y-1 text-center">
                                <div class="mx-auto h-12 w-12 text-gray-400">
                                    <i class="fas fa-cloud-upload-alt text-3xl"></i>
                                </div>
                                <div class="flex text-sm text-gray-600">
                                    <label for="kmz_file" class="relative cursor-pointer bg-white rounded-md font-medium text-primary-600 hover:text-primary-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary-500">
                                        <span>Upload file</span>
                                        <input id="kmz_file" name="kmz_file" type="file" accept=".kmz,.kml" required class="sr-only">
                                    </label>
                                    <p class="pl-1">atau drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">KMZ atau KML sampai 10MB</p>
                            </div>
                        </div>
                        <div class="text-red-600 text-sm mt-1 hidden" id="kmz_file-error"></div>

                        <!-- File info -->
                        <div id="file-info" class="hidden mt-2 p-3 bg-blue-50 rounded-md">
                            <div class="flex items-center">
                                <i class="fas fa-file-archive text-blue-600 mr-2"></i>
                                <span id="file-name" class="text-sm text-blue-800"></span>
                                <span id="file-size" class="text-xs text-blue-600 ml-2"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Upload Options -->
                    <div class="bg-gray-50 p-4 rounded-md">
                        <h4 class="text-sm font-medium text-gray-900 mb-3">Opsi Import</h4>
                        <div class="space-y-2">
                            <div class="flex items-center">
                                <input type="checkbox" id="skip_duplicates" name="skip_duplicates" value="1" checked
                                       class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                <label for="skip_duplicates" class="ml-2 block text-sm text-gray-900">
                                    Skip koordinat duplikat
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" id="validate_coordinates" name="validate_coordinates" value="1" checked
                                       class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                <label for="validate_coordinates" class="ml-2 block text-sm text-gray-900">
                                    Validasi koordinat Indonesia
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" id="auto_detect_type" name="auto_detect_type" value="1" checked
                                       class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                <label for="auto_detect_type" class="ml-2 block text-sm text-gray-900">
                                    Auto-detect type point dari nama
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-4 mt-6 pt-4 border-t">
                    <button type="button" onclick="closeKmzModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-md hover:bg-primary-700">
                        <span class="kmz-loading-text">Upload & Import</span>
                        <span class="kmz-loading-spinner hidden">
                            <i class="fas fa-spinner fa-spin mr-2"></i>Processing...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Map View Modal -->
<div id="map-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-5 mx-auto p-5 border w-11/12 h-5/6 shadow-lg rounded-md bg-white">
        <div class="flex items-center justify-between pb-3 border-b">
            <h3 class="text-lg font-medium text-gray-900">Map View - Points Visualization</h3>
            <button onclick="closeMapModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <div class="mt-4 h-full">
            <!-- Map will be rendered here -->
            <div id="map-container" class="w-full h-5/6 bg-gray-200 rounded-md flex items-center justify-center">
                <div class="text-gray-500">
                    <i class="fas fa-map text-4xl mb-2"></i>
                    <p>Map integration akan ditambahkan di tahap selanjutnya</p>
                    <p class="text-sm">Leaflet/Google Maps integration</p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let currentPage = 1;
let currentSortField = 'created_at';
let currentSortDirection = 'desc';
let selectedPoints = [];

$(document).ready(function() {
    loadPoints();
    loadStatistics();

    // Search with debounce
    let searchTimer;
    $('#search-input').on('keyup', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function() {
            currentPage = 1;
            loadPoints();
        }, 500);
    });

    // Filter changes
    $('#mitra-filter, #type-filter, #file-filter').on('change', function() {
        currentPage = 1;
        loadPoints();
    });

    // File upload preview
    $('#kmz_file').on('change', function() {
        const file = this.files[0];
        if (file) {
            $('#file-name').text(file.name);
            $('#file-size').text('(' + (file.size / 1024 / 1024).toFixed(2) + ' MB)');
            $('#file-info').removeClass('hidden');
        } else {
            $('#file-info').addClass('hidden');
        }
    });

    // Select all functionality
    $('#select-all').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.point-checkbox').prop('checked', isChecked);
        updateSelectedPoints();
    });

    // Individual checkbox
    $(document).on('change', '.point-checkbox', function() {
        updateSelectedPoints();
    });

    // Form submissions
    $('#point-form').on('submit', function(e) {
        e.preventDefault();
        savePoint();
    });

    $('#kmz-form').on('submit', function(e) {
        e.preventDefault();
        uploadKmz();
    });
});

function loadPoints() {
    const params = {
        page: currentPage,
        search: $('#search-input').val(),
        mitra_id: $('#mitra-filter').val(),
        type_point: $('#type-filter').val(),
        has_file: $('#file-filter').val(),
        sort_field: currentSortField,
        sort_direction: currentSortDirection,
        per_page: 10
    };

    $('#loading-state').show();
    $('#empty-state').hide();

    $.get('{{ route("mitra-turunans.index") }}', params)
        .done(function(response) {
            if (response.success) {
                renderPoints(response.data);
                renderPagination(response.pagination);
                $('#loading-state').hide();

                if (response.data.length === 0) {
                    $('#empty-state').show();
                }
            }
        })
        .fail(function(xhr) {
            $('#loading-state').hide();
            showError('Gagal memuat data points');
        });
}

function renderPoints(points) {
    const tbody = $('#points-tbody');
    tbody.empty();

    points.forEach(point => {
        const createdAt = new Date(point.created_at).toLocaleDateString('id-ID');
        const hasFile = point.nama_file ?
            `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                <i class="fas fa-paperclip mr-1"></i>Ada File
            </span>` :
            `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                <i class="fas fa-times mr-1"></i>Tidak Ada
            </span>`;

        const typePoint = point.type_point ?
            `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                ${point.type_point.toUpperCase()}
            </span>` :
            `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                -
            </span>`;

        const mitraColor = point.mitra?.warna_pt || '#6B7280';
        const googleMapsUrl = `https://www.google.com/maps?q=${point.koordinat}`;

        const row = `
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                    <input type="checkbox" class="point-checkbox h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded" value="${point.mitra_turunan_id}">
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    ${point.mitra_turunan_id}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">${point.nama_point || 'Unnamed Point'}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    ${typePoint}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="w-3 h-3 rounded-full mr-2" style="background-color: ${mitraColor}"></div>
                        <span class="text-sm text-gray-900">${point.mitra?.nama_pt || 'No Mitra'}</span>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">${point.koordinat}</div>
                    <a href="${googleMapsUrl}" target="_blank" class="text-xs text-primary-600 hover:text-primary-900">
                        <i class="fas fa-external-link-alt mr-1"></i>Google Maps
                    </a>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    ${hasFile}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${createdAt}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center space-x-2">
                        <button onclick="viewPoint('${point.mitra_turunan_id}')" class="text-blue-600 hover:text-blue-900" title="Lihat Detail">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button onclick="editPoint('${point.mitra_turunan_id}')" class="text-indigo-600 hover:text-indigo-900" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="findNearestPoints('${point.mitra_turunan_id}')" class="text-green-600 hover:text-green-900" title="Cari Terdekat">
                            <i class="fas fa-search-location"></i>
                        </button>
                        ${point.nama_file ? `<button onclick="downloadFile('${point.mitra_turunan_id}')" class="text-yellow-600 hover:text-yellow-900" title="Download File"><i class="fas fa-download"></i></button>` : ''}
                        <button onclick="deletePoint('${point.mitra_turunan_id}')" class="text-red-600 hover:text-red-900" title="Hapus">
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
    loadPoints();
}

function sortBy(field) {
    if (currentSortField === field) {
        currentSortDirection = currentSortDirection === 'asc' ? 'desc' : 'asc';
    } else {
        currentSortField = field;
        currentSortDirection = 'asc';
    }
    currentPage = 1;
    loadPoints();
}

function updateSelectedPoints() {
    selectedPoints = [];
    $('.point-checkbox:checked').each(function() {
        selectedPoints.push($(this).val());
    });

    if (selectedPoints.length > 0) {
        $('#bulk-actions').removeClass('hidden');
    } else {
        $('#bulk-actions').addClass('hidden');
    }

    // Update select all checkbox state
    const totalCheckboxes = $('.point-checkbox').length;
    const checkedCheckboxes = $('.point-checkbox:checked').length;

    if (checkedCheckboxes === 0) {
        $('#select-all').prop('indeterminate', false).prop('checked', false);
    } else if (checkedCheckboxes === totalCheckboxes) {
        $('#select-all').prop('indeterminate', false).prop('checked', true);
    } else {
        $('#select-all').prop('indeterminate', true);
    }
}

function loadStatistics() {
    $.get('{{ route("mitra-turunans.statistics") }}')
        .done(function(response) {
            if (response.success) {
                const stats = response.data.statistics;
                $('#total-points').text(stats.total_points);
                $('#points-with-files').text(stats.points_with_files);
                $('#points-without-files').text(stats.points_without_files);
                $('#recent-points').text(stats.recent_points);
            }
        });
}

function openCreateModal() {
    $('#modal-title').text('Tambah Point Baru');
    $('#point-form')[0].reset();
    $('#point-id').val('');
    clearErrors();
    $('#point-modal').removeClass('hidden');
}

function editPoint(pointId) {
    $.get(`{{ url('mitra-turunans') }}/${pointId}`)
        .done(function(response) {
            if (response.success) {
                const point = response.data;
                $('#modal-title').text('Edit Point');
                $('#point-id').val(point.mitra_turunan_id);
                $('#mitra_id').val(point.mitra_id);
                $('#nama_point').val(point.nama_point);
                $('#type_point').val(point.type_point);
                $('#koordinat').val(point.koordinat);
                $('#deskripsi').val(point.deskripsi);
                clearErrors();
                $('#point-modal').removeClass('hidden');
            }
        })
        .fail(function(xhr) {
            showError('Gagal memuat data point');
        });
}

function savePoint() {
    const formData = new FormData($('#point-form')[0]);
    const pointId = $('#point-id').val();
    const url = pointId ? `{{ url('mitra-turunans') }}/${pointId}` : '{{ route("mitra-turunans.store") }}';
    const method = pointId ? 'PUT' : 'POST';

    if (pointId && method === 'PUT') {
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
            loadPoints();
            loadStatistics();
            showSuccess(response.message);
        }
    })
    .fail(function(xhr) {
        if (xhr.status === 422) {
            displayValidationErrors(xhr.responseJSON.errors);
        } else {
            showError(xhr.responseJSON.message || 'Terjadi kesalahan saat menyimpan point');
        }
    })
    .always(function() {
        $('.loading-text').removeClass('hidden');
        $('.loading-spinner').addClass('hidden');
    });
}

function deletePoint(pointId) {
    showConfirm('Point ini akan dihapus beserta file yang terkait!', function() {
        $.ajax({
            url: `{{ url('mitra-turunans') }}/${pointId}`,
            type: 'DELETE'
        })
        .done(function(response) {
            if (response.success) {
                loadPoints();
                loadStatistics();
                showSuccess(response.message);
            }
        })
        .fail(function(xhr) {
            showError(xhr.responseJSON.message || 'Gagal menghapus point');
        });
    });
}

function viewPoint(pointId) {
    $.get(`{{ url('mitra-turunans') }}/${pointId}`)
        .done(function(response) {
            if (response.success) {
                const point = response.data;
                const coords = point.koordinat.split(',');
                const lat = parseFloat(coords[0]) || 0;
                const lng = parseFloat(coords[1]) || 0;
                const createdAt = new Date(point.created_at).toLocaleString('id-ID');
                const googleMapsUrl = `https://www.google.com/maps?q=${point.koordinat}`;
                const hasFile = point.nama_file ? 'Ya' : 'Tidak';

                Swal.fire({
                    title: point.nama_point || 'Point Detail',
                    html: `
                        <div class="text-left space-y-3">
                            <div><strong>ID:</strong> ${point.mitra_turunan_id}</div>
                            <div><strong>Mitra:</strong> ${point.mitra?.nama_pt || 'No Mitra'}</div>
                            <div><strong>Type:</strong> ${point.type_point ? point.type_point.toUpperCase() : '-'}</div>
                            <div><strong>Koordinat:</strong> ${point.koordinat}</div>
                            <div><strong>Latitude:</strong> ${lat}</div>
                            <div><strong>Longitude:</strong> ${lng}</div>
                            <div><strong>Deskripsi:</strong> ${point.deskripsi || 'Tidak ada deskripsi'}</div>
                            <div><strong>File:</strong> ${hasFile}</div>
                            <div><strong>Dibuat:</strong> ${createdAt}</div>
                            <div class="mt-4">
                                <a href="${googleMapsUrl}" target="_blank" class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                    <i class="fas fa-map-marker-alt mr-2"></i>Buka di Google Maps
                                </a>
                            </div>
                        </div>
                    `,
                    confirmButtonColor: '#3b82f6',
                    confirmButtonText: 'Tutup',
                    width: '600px'
                });
            }
        })
        .fail(function(xhr) {
            showError('Gagal memuat detail point');
        });
}

function findNearestPoints(pointId) {
    // Get point coordinates first
    $.get(`{{ url('mitra-turunans') }}/${pointId}`)
        .done(function(response) {
            if (response.success) {
                const point = response.data;
                const coords = point.koordinat.split(',');
                const lat = parseFloat(coords[0]);
                const lng = parseFloat(coords[1]);

                // Find nearest points
                $.get('{{ route("mitra-turunans.nearest-points") }}', {
                    latitude: lat,
                    longitude: lng,
                    limit: 10,
                    radius_km: 5
                })
                .done(function(nearestResponse) {
                    if (nearestResponse.success) {
                        let nearestHtml = '<div class="space-y-2 max-h-64 overflow-y-auto">';

                        if (nearestResponse.data.length > 0) {
                            nearestResponse.data.forEach(nearPoint => {
                                if (nearPoint.id !== pointId) { // Exclude current point
                                    nearestHtml += `
                                        <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                            <div>
                                                <div class="text-sm font-medium">${nearPoint.name || 'Unnamed Point'}</div>
                                                <div class="text-xs text-gray-500">${nearPoint.mitra.name}</div>
                                                <div class="text-xs text-blue-600">${nearPoint.type ? nearPoint.type.toUpperCase() : ''}</div>
                                            </div>
                                            <div class="text-sm text-blue-600">${nearPoint.distance_km} km</div>
                                        </div>
                                    `;
                                }
                            });
                        } else {
                            nearestHtml += '<div class="text-center text-gray-500 py-4">Tidak ada point dalam radius 5km</div>';
                        }

                        nearestHtml += '</div>';

                        Swal.fire({
                            title: `Points Terdekat dari ${point.nama_point}`,
                            html: nearestHtml,
                            confirmButtonColor: '#3b82f6',
                            confirmButtonText: 'Tutup',
                            width: '500px'
                        });
                    }
                })
                .fail(function() {
                    showError('Gagal mencari points terdekat');
                });
            }
        });
}

function downloadFile(pointId) {
    // This would handle file download
    window.open(`{{ url('mitra-turunans') }}/${pointId}/download-file`, '_blank');
}

function openKmzModal() {
    $('#kmz-form')[0].reset();
    $('#file-info').addClass('hidden');
    clearKmzErrors();
    $('#kmz-modal').removeClass('hidden');
}

function uploadKmz() {
    const formData = new FormData($('#kmz-form')[0]);

    $('.kmz-loading-text').addClass('hidden');
    $('.kmz-loading-spinner').removeClass('hidden');

    $.ajax({
        url: '{{ route("mitra-turunans.upload-kmz") }}',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false
    })
    .done(function(response) {
        if (response.success) {
            closeKmzModal();
            loadPoints();
            loadStatistics();

            // Show detailed import results
            let resultHtml = `
                <div class="text-left space-y-2">
                    <div><strong>Mitra:</strong> ${response.data.mitra}</div>
                    <div><strong>Total Points Ditemukan:</strong> ${response.data.total_found}</div>
                    <div><strong>Berhasil Diimport:</strong> ${response.data.imported_count}</div>
                    <div><strong>Type Terdeteksi:</strong> ${response.data.type_detected_count || 0}</div>
                    <div><strong>Dilewati:</strong> ${response.data.skipped_count}</div>
                </div>
            `;

            if (response.data.errors && response.data.errors.length > 0) {
                resultHtml += '<div class="mt-4"><strong>Errors:</strong><ul class="list-disc list-inside text-sm text-red-600">';
                response.data.errors.slice(0, 5).forEach(error => {
                    resultHtml += `<li>${error}</li>`;
                });
                resultHtml += '</ul></div>';
            }

            Swal.fire({
                title: 'Import Berhasil!',
                html: resultHtml,
                icon: 'success',
                confirmButtonColor: '#3b82f6'
            });
        }
    })
    .fail(function(xhr) {
        if (xhr.status === 422) {
            displayKmzValidationErrors(xhr.responseJSON.errors);
        } else {
            showError(xhr.responseJSON.message || 'Terjadi kesalahan saat upload KMZ');
        }
    })
    .always(function() {
        $('.kmz-loading-text').removeClass('hidden');
        $('.kmz-loading-spinner').addClass('hidden');
    });
}

function executeBulkAction() {
    const action = $('#bulk-action-select').val();
    if (!action || selectedPoints.length === 0) {
        showError('Pilih aksi dan minimal satu point');
        return;
    }

    const actionText = {
        'delete': 'menghapus'
    };

    showConfirm(`${actionText[action]} ${selectedPoints.length} point yang dipilih?`, function() {
        $.ajax({
            url: '{{ route("mitra-turunans.bulk-action") }}',
            type: 'POST',
            data: {
                action: action,
                point_ids: selectedPoints
            }
        })
        .done(function(response) {
            if (response.success) {
                loadPoints();
                loadStatistics();
                selectedPoints = [];
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

function showMapView() {
    $('#map-modal').removeClass('hidden');
    // Map integration would be implemented here
    // This could use Leaflet, Google Maps, or other mapping libraries
}

function openMapPicker() {
    showError('Map picker akan diimplementasikan pada tahap selanjutnya');
    // This would open a map modal for coordinate picking
}

function exportData(format) {
    const params = {
        format: format,
        mitra_id: $('#mitra-filter').val(),
        type_point: $('#type-filter').val()
    };

    const queryString = $.param(params);
    window.open(`{{ route("mitra-turunans.export") }}?${queryString}`, '_blank');
}

function refreshData() {
    loadPoints();
    loadStatistics();
    showSuccess('Data berhasil diperbarui');
}

function closeModal() {
    $('#point-modal').addClass('hidden');
    clearErrors();
}

function closeKmzModal() {
    $('#kmz-modal').addClass('hidden');
    clearKmzErrors();
}

function closeMapModal() {
    $('#map-modal').addClass('hidden');
}

function clearErrors() {
    $('.text-red-600').addClass('hidden').text('');
    $('.border-red-500').removeClass('border-red-500');
}

function clearKmzErrors() {
    $('#kmz_mitra_id-error, #kmz_file-error').addClass('hidden').text('');
    $('#kmz_mitra_id, #kmz_file').removeClass('border-red-500');
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

function displayKmzValidationErrors(errors) {
    clearKmzErrors();

    for (const field in errors) {
        const errorElement = $(`#kmz_${field}-error`);
        const inputElement = $(`#kmz_${field}`);

        if (errorElement.length) {
            errorElement.removeClass('hidden').text(errors[field][0]);
            inputElement.addClass('border-red-500');
        }
    }
}

// Close modals when clicking outside
$(document).on('click', '#point-modal, #kmz-modal, #map-modal', function(e) {
    if (e.target === this) {
        if (e.target.id === 'point-modal') {
            closeModal();
        } else if (e.target.id === 'kmz-modal') {
            closeKmzModal();
        } else if (e.target.id === 'map-modal') {
            closeMapModal();
        }
    }
});
</script>
@endpush
