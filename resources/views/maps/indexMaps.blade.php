@extends('layouts.app')

@section('title', 'Map Visualization')
@section('page-title', 'Map Visualization')

@section('breadcrumb')
    <li><a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-home"></i></a></li>
    <li><span class="text-gray-400">/</span></li>
    <li><span class="text-gray-600 font-medium">Maps</span></li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .leaflet-container {
        height: 100%;
        width: 100%;
    }

    .map-controls {
        position: absolute;
        top: 10px;
        left: 10px;
        z-index: 1000;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        padding: 15px;
        max-width: 320px;
        max-height: calc(100vh - 40px);
        overflow-y: auto;
    }

    .map-legend {
        position: absolute;
        bottom: 20px;
        right: 20px;
        z-index: 1000;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        padding: 15px;
        max-width: 250px;
        max-height: 300px;
        overflow-y: auto;
    }

    .mitra-legend-item {
        display: flex;
        align-items: center;
        margin-bottom: 8px;
        cursor: pointer;
        padding: 4px;
        border-radius: 4px;
        transition: background-color 0.2s;
    }

    .mitra-legend-item:hover {
        background-color: #f3f4f6;
    }

    .mitra-color-circle {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin-right: 8px;
        border: 2px solid #fff;
        box-shadow: 0 1px 3px rgba(0,0,0,0.3);
    }

    .search-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border-radius: 4px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        max-height: 250px;
        overflow-y: auto;
        z-index: 1001;
        display: none;
        border: 1px solid #e5e7eb;
    }

    .search-result-item {
        padding: 10px 12px;
        border-bottom: 1px solid #e5e7eb;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .search-result-item:hover {
        background-color: #f3f4f6;
    }

    .search-result-item:last-child {
        border-bottom: none;
    }

    .search-result-header {
        font-weight: 600;
        font-size: 12px;
        padding: 8px 12px;
        background-color: #f9fafb;
        color: #6b7280;
        border-bottom: 1px solid #e5e7eb;
    }

    .stats-panel {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 1000;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        padding: 15px;
        min-width: 200px;
    }

    .stat-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .stat-item:last-child {
        margin-bottom: 0;
    }

    .cluster-icon {
        background: #4f46e5;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }

    .fullscreen-toggle {
        position: absolute;
        top: 10px;
        right: 230px;
        z-index: 1000;
        background: white;
        border: none;
        border-radius: 4px;
        padding: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        cursor: pointer;
    }

    .fullscreen-toggle:hover {
        background: #f3f4f6;
    }

    /* Loading indicator */
    .loading-indicator {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 2000;
        background: rgba(255, 255, 255, 0.9);
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        display: none;
    }

    .spinner {
        width: 20px;
        height: 20px;
        border: 2px solid #f3f3f3;
        border-top: 2px solid #3498db;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        display: inline-block;
        margin-right: 10px;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Cache status indicator */
    .cache-status {
        position: absolute;
        bottom: 20px;
        left: 20px;
        z-index: 1000;
        background: rgba(0, 0, 0, 0.7);
        color: white;
        padding: 6px 10px;
        border-radius: 4px;
        font-size: 11px;
        font-family: monospace;
    }

    .cache-hit {
        background: rgba(34, 197, 94, 0.8);
    }

    .cache-miss {
        background: rgba(239, 68, 68, 0.8);
    }

    /* Fullscreen styles */
    .map-fullscreen {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        z-index: 9999 !important;
    }

    .map-fullscreen .map-controls,
    .map-fullscreen .stats-panel,
    .map-fullscreen .map-legend {
        z-index: 10000 !important;
    }

    /* Collapsible controls */
    .controls-collapsed {
        transform: translateX(-290px);
        transition: transform 0.3s ease;
    }

    .controls-toggle {
        position: absolute;
        top: 50%;
        right: -35px;
        transform: translateY(-50%);
        background: white;
        border: none;
        border-radius: 0 4px 4px 0;
        padding: 8px 4px;
        box-shadow: 2px 0 4px rgba(0,0,0,0.1);
        cursor: pointer;
        z-index: 1001;
    }

    .controls-toggle:hover {
        background: #f3f4f6;
    }

    /* Enhanced controls styling */
    .control-section {
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #e5e7eb;
    }

    .control-section:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }

    .control-label {
        display: block;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
        font-size: 14px;
    }

    .btn-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .btn-action {
        width: 100%;
        padding: 8px 12px;
        border: none;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .btn-primary {
        background: #3b82f6;
        color: white;
    }

    .btn-primary:hover {
        background: #2563eb;
        transform: translateY(-1px);
    }

    .btn-success {
        background: #10b981;
        color: white;
    }

    .btn-success:hover {
        background: #059669;
        transform: translateY(-1px);
    }

    .btn-secondary {
        background: #6b7280;
        color: white;
    }

    .btn-secondary:hover {
        background: #4b5563;
        transform: translateY(-1px);
    }

    .btn-warning {
        background: #f59e0b;
        color: white;
    }

    .btn-warning:hover {
        background: #d97706;
        transform: translateY(-1px);
    }
</style>
@endpush

@section('content')
<div class="h-screen relative">
    <!-- Loading Indicator -->
    <div id="loading-indicator" class="loading-indicator">
        <div class="spinner"></div>
        <span>Memuat data peta...</span>
    </div>

    <!-- Map Container -->
    <div id="map" class="w-full h-full"></div>

    <!-- Map Controls -->
    <div class="map-controls" id="map-controls">
        <!-- Toggle Button -->
        <button class="controls-toggle" id="controls-toggle" title="Toggle Controls">
            <i class="fas fa-chevron-left"></i>
        </button>

        <!-- Search Section -->
        <div class="control-section">
            <label class="control-label">
                <i class="fas fa-search mr-2"></i>Pencarian
            </label>
            <div class="relative">
                <input type="text" id="search-input" placeholder="Cari point, mitra, atau koordinat..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <div id="search-results" class="search-results"></div>
            </div>
        </div>

        <!-- Mitra Filter -->
        <div class="control-section">
            <label class="control-label">
                <i class="fas fa-filter mr-2"></i>Filter Mitra
            </label>
            <select id="mitra-filter" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Semua Mitra</option>
                @foreach($mitras as $mitra)
                    <option value="{{ $mitra->mitra_id }}" data-color="{{ $mitra->warna_pt }}">
                        {{ $mitra->nama_pt }} ({{ $mitra->mitra_turunans_count }})
                    </option>
                @endforeach
            </select>
        </div>

        <!-- View Options -->
        <div class="control-section">
            <label class="control-label">
                <i class="fas fa-eye mr-2"></i>Opsi Tampilan
            </label>
            <div class="space-y-2">
                <label class="flex items-center">
                    <input type="checkbox" id="cluster-toggle" checked class="mr-2">
                    <span class="text-sm">Cluster markers</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" id="show-labels" class="mr-2">
                    <span class="text-sm">Tampilkan label</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" id="show-coordinates" class="mr-2">
                    <span class="text-sm">Tampilkan koordinat</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" id="viewport-loading" checked class="mr-2">
                    <span class="text-sm">Optimasi viewport</span>
                </label>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="control-section">
            <label class="control-label">
                <i class="fas fa-bolt mr-2"></i>Aksi Cepat
            </label>
            <div class="btn-group">
                <button id="fit-all-points" class="btn-action btn-primary">
                    <i class="fas fa-expand-arrows-alt"></i>
                    Lihat Semua Point
                </button>
                <button id="my-location" class="btn-action btn-success">
                    <i class="fas fa-crosshairs"></i>
                    Lokasi Saya
                </button>
                <button id="refresh-data" class="btn-action btn-secondary">
                    <i class="fas fa-sync-alt"></i>
                    Refresh Data
                </button>
                <button id="clear-cache" class="btn-action btn-warning">
                    <i class="fas fa-trash"></i>
                    Clear Cache
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Panel -->
    <div class="stats-panel">
        <h3 class="font-medium text-gray-900 mb-3">
            <i class="fas fa-chart-bar mr-2"></i>Statistik
        </h3>
        <div class="stat-item">
            <span class="text-sm text-gray-600">Total Points:</span>
            <span class="text-sm font-medium" id="total-points">{{ $stats['total_points'] }}</span>
        </div>
        <div class="stat-item">
            <span class="text-sm text-gray-600">Total Mitra:</span>
            <span class="text-sm font-medium" id="total-mitras">{{ $stats['total_mitras'] }}</span>
        </div>
        <div class="stat-item">
            <span class="text-sm text-gray-600">Ditampilkan:</span>
            <span class="text-sm font-medium" id="visible-points">0</span>
        </div>
        <div class="stat-item">
            <span class="text-sm text-gray-600">Cache Status:</span>
            <span class="text-xs font-medium" id="cache-status">-</span>
        </div>
    </div>

    <!-- Legend -->
    <div class="map-legend">
        <h3 class="font-medium text-gray-900 mb-3">
            <i class="fas fa-list mr-2"></i>Legend Mitra
        </h3>
        <div id="legend-content">
            @foreach($stats['points_by_mitra'] as $mitra)
                @if($mitra->points_count > 0)
                <div class="mitra-legend-item" data-mitra-id="{{ $mitra->mitra_id }}">
                    <div class="mitra-color-circle" style="background-color: {{ $mitra->warna_pt }}"></div>
                    <div class="flex-1">
                        <div class="text-sm font-medium text-gray-900">{{ $mitra->nama_pt }}</div>
                        <div class="text-xs text-gray-500">{{ $mitra->points_count }} points</div>
                    </div>
                </div>
                @endif
            @endforeach
        </div>
    </div>

    <!-- Cache Status Indicator -->
    <div id="cache-indicator" class="cache-status">
        Cache: Ready
    </div>

    <!-- Fullscreen Toggle -->
    <button id="fullscreen-toggle" class="fullscreen-toggle" title="Toggle Fullscreen">
        <i class="fas fa-expand"></i>
    </button>
</div>

<!-- Point Detail Modal -->
<div id="point-detail-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-[10000]">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between pb-3">
                <h3 class="text-lg font-medium text-gray-900" id="modal-point-name">Point Detail</h3>
                <button onclick="closePointModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div id="modal-point-content" class="space-y-3">
                <!-- Content will be populated by JavaScript -->
            </div>

            <div class="flex items-center justify-end space-x-3 mt-6 pt-4 border-t">
                <button onclick="closePointModal()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    Tutup
                </button>
                <button id="view-on-google-maps"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700">
                    <i class="fas fa-external-link-alt mr-2"></i>Google Maps
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />

<script>
let map;
let allPoints = [];
let currentPoints = [];
let markersLayer;
let clusterGroup;
let searchTimeout;
let loadTimeout;
let currentPopup = null;
let cacheHits = 0;
let totalRequests = 0;
let isViewportLoading = true;

// Initialize map
$(document).ready(function() {
    initializeMap();
    setupEventListeners();
    loadInitialData();
});

function initializeMap() {
    // Initialize map centered on Indonesia
    map = L.map('map').setView([-2.5489, 118.0149], 5);

    // Add tile layers
    const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    });

    const satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: '© Esri'
    });

    // Add default layer
    osmLayer.addTo(map);

    // Layer control
    const baseMaps = {
        "OpenStreetMap": osmLayer,
        "Satellite": satelliteLayer
    };

    L.control.layers(baseMaps).addTo(map);

    // Initialize marker cluster group
    clusterGroup = L.markerClusterGroup({
        chunkedLoading: true,
        chunkInterval: 200,
        maxClusterRadius: 50,
        iconCreateFunction: function(cluster) {
            const count = cluster.getChildCount();
            let size = 'small';
            if (count >= 100) size = 'large';
            else if (count >= 10) size = 'medium';

            return L.divIcon({
                html: `<div class="cluster-icon">${count}</div>`,
                className: `marker-cluster marker-cluster-${size}`,
                iconSize: [40, 40]
            });
        }
    });

    map.addLayer(clusterGroup);

    // Add map event listeners for viewport loading
    map.on('moveend zoomend', function() {
        if (isViewportLoading) {
            clearTimeout(loadTimeout);
            loadTimeout = setTimeout(() => {
                loadPointsInViewport();
            }, 300);
        }
    });
}

function setupEventListeners() {
    // Search input with enhanced functionality
    $('#search-input').on('input', function() {
        clearTimeout(searchTimeout);
        const query = $(this).val().trim();

        if (query.length === 0) {
            $('#search-results').hide();
            return;
        }

        if (query.length < 2) {
            $('#search-results').html('<div class="search-result-item text-gray-500">Ketik minimal 2 karakter</div>').show();
            return;
        }

        searchTimeout = setTimeout(() => {
            searchPoints(query);
        }, 300);
    });

    // Hide search results when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#search-input, #search-results').length) {
            $('#search-results').hide();
        }
    });

    // Mitra filter
    $('#mitra-filter').on('change', function() {
        const selectedMitra = $(this).val();
        filterPointsByMitra(selectedMitra);
    });

    // View options
    $('#cluster-toggle').on('change', function() {
        toggleClustering($(this).is(':checked'));
    });

    $('#show-labels').on('change', function() {
        toggleLabels($(this).is(':checked'));
    });

    $('#show-coordinates').on('change', function() {
        toggleCoordinates($(this).is(':checked'));
    });

    $('#viewport-loading').on('change', function() {
        isViewportLoading = $(this).is(':checked');
        if (isViewportLoading) {
            loadPointsInViewport();
        } else {
            loadAllPoints();
        }
    });

    // Quick actions
    $('#fit-all-points').on('click', fitAllPoints);
    $('#my-location').on('click', showMyLocation);
    $('#refresh-data').on('click', refreshMapData);
    $('#clear-cache').on('click', clearMapCache);
    $('#fullscreen-toggle').on('click', toggleFullscreen);

    // Controls toggle
    $('#controls-toggle').on('click', toggleControls);

    // Legend interactions
    $(document).on('click', '.mitra-legend-item', function() {
        const mitraId = $(this).data('mitra-id');
        $('#mitra-filter').val(mitraId).trigger('change');
    });
}

function loadInitialData() {
    showLoading(true);
    loadStatistics();

    if (isViewportLoading) {
        loadPointsInViewport();
    } else {
        loadAllPoints();
    }
}

function loadPointsInViewport() {
    const selectedMitra = $('#mitra-filter').val();
    const bounds = map.getBounds();
    const zoom = map.getZoom();

    showLoading(true);

    $.get('{{ route("maps.points") }}', {
        mitra_id: selectedMitra,
        bounds: {
            north: bounds.getNorth(),
            south: bounds.getSouth(),
            east: bounds.getEast(),
            west: bounds.getWest()
        },
        zoom: zoom
    })
    .done(function(response) {
        trackCacheUsage(response);
        if (response.success) {
            currentPoints = response.data;
            displayPoints();
            updateStatistics();
        }
    })
    .fail(function() {
        showNotification('Gagal memuat data points', 'error');
    })
    .always(function() {
        showLoading(false);
    });
}

function loadAllPoints() {
    const selectedMitra = $('#mitra-filter').val();

    showLoading(true);

    $.get('{{ route("maps.points") }}', {
        mitra_id: selectedMitra
    })
    .done(function(response) {
        trackCacheUsage(response);
        if (response.success) {
            allPoints = response.data;
            currentPoints = [...allPoints];
            displayPoints();
            updateStatistics();
        }
    })
    .fail(function() {
        showNotification('Gagal memuat data points', 'error');
    })
    .always(function() {
        showLoading(false);
    });
}

function loadStatistics() {
    $.get('{{ route("maps.statistics") }}')
    .done(function(response) {
        if (response.success) {
            $('#total-points').text(response.data.total_points);
            $('#total-mitras').text(response.data.total_mitras);
            updateCacheStatus(response.cached ? 'HIT' : 'MISS');
        }
    });
}

function searchPoints(query) {
    showLoading(true, 'Mencari...');

    $.get('{{ route("maps.search") }}', {
        q: query
    })
    .done(function(response) {
        trackCacheUsage(response);
        if (response.success) {
            displaySearchResults(response.data, query);
        }
    })
    .fail(function() {
        $('#search-results').html('<div class="search-result-item text-red-500">Error saat mencari</div>').show();
    })
    .always(function() {
        showLoading(false);
    });
}

function displaySearchResults(results, query) {
    const container = $('#search-results');
    container.empty();

    if (results.length === 0) {
        container.html('<div class="search-result-item text-gray-500">Tidak ada hasil ditemukan untuk "' + query + '"</div>');
    } else {
        // Group results by type
        const pointResults = results.filter(r => r.type !== 'mitra');
        const mitraResults = results.filter(r => r.type === 'mitra');

        // Add points section
        if (pointResults.length > 0) {
            container.append('<div class="search-result-header">POINTS (' + pointResults.length + ')</div>');
            pointResults.forEach(point => {
                const item = $(`
                    <div class="search-result-item" data-point-id="${point.id}" data-type="point">
                        <div class="font-medium text-sm">${point.name}</div>
                        <div class="text-xs text-gray-500 flex items-center">
                            <div class="w-2 h-2 rounded-full mr-2" style="background-color: ${point.mitra.color}"></div>
                            ${point.mitra.name} • ${point.coordinates}
                        </div>
                    </div>
                `);

                item.on('click', function() {
                    zoomToPoint(point);
                    container.hide();
                    $('#search-input').val('');
                });

                container.append(item);
            });
        }

        // Add mitra section if searching by mitra name
        if (mitraResults.length > 0) {
            container.append('<div class="search-result-header">MITRA (' + mitraResults.length + ')</div>');
            mitraResults.forEach(mitra => {
                const item = $(`
                    <div class="search-result-item" data-mitra-id="${mitra.id}" data-type="mitra">
                        <div class="font-medium text-sm flex items-center">
                            <div class="w-3 h-3 rounded-full mr-2" style="background-color: ${mitra.color}"></div>
                            ${mitra.name}
                        </div>
                        <div class="text-xs text-gray-500">${mitra.points_count} points</div>
                    </div>
                `);

                item.on('click', function() {
                    $('#mitra-filter').val(mitra.id).trigger('change');
                    container.hide();
                    $('#search-input').val('');
                });

                container.append(item);
            });
        }
    }

    container.show();
}

function filterPointsByMitra(mitraId) {
    showLoading(true);

    if (isViewportLoading) {
        loadPointsInViewport();
    } else {
        loadAllPoints();
    }
}

function displayPoints() {
    // Clear existing markers
    clusterGroup.clearLayers();

    currentPoints.forEach(point => {
        if (point.latitude === 0 && point.longitude === 0) return;

        // Create custom marker icon based on mitra color
        const markerIcon = L.divIcon({
            className: 'custom-marker',
            html: `<div style="background-color: ${point.mitra.color}; width: 12px; height: 12px; border-radius: 50%; border: 2px solid white; box-shadow: 0 1px 3px rgba(0,0,0,0.3);"></div>`,
            iconSize: [16, 16],
            iconAnchor: [8, 8]
        });

        const marker = L.marker([point.latitude, point.longitude], {
            icon: markerIcon
        });

        // Create popup content
        const popupContent = createPopupContent(point);
        marker.bindPopup(popupContent, {
            maxWidth: 300,
            className: 'custom-popup'
        });

        // Store point data in marker
        marker.pointData = point;

        // Add click event for detailed view
        marker.on('click', function() {
            showPointDetail(point.id);
        });

        clusterGroup.addLayer(marker);
    });

    // Update visible points count
    $('#visible-points').text(currentPoints.length);
}

function createPopupContent(point) {
    return `
        <div class="p-2">
            <div class="font-medium text-gray-900 mb-2">${point.name}</div>
            <div class="text-sm text-gray-600 mb-2">
                <div class="flex items-center mb-1">
                    <div class="w-3 h-3 rounded-full mr-2" style="background-color: ${point.mitra.color}"></div>
                    <span>${point.mitra.name}</span>
                </div>
                <div class="text-xs text-gray-500">
                    ${point.coordinates}
                </div>
            </div>
            ${point.description ? `<div class="text-sm text-gray-600 mb-2">${point.description}</div>` : ''}
            <div class="flex space-x-2 text-xs">
                <button onclick="showPointDetail('${point.id}')" class="text-blue-600 hover:text-blue-800">Detail</button>
                <button onclick="openGoogleMaps('${point.coordinates}')" class="text-green-600 hover:text-green-800">Maps</button>
            </div>
        </div>
    `;
}

function zoomToPoint(point) {
    map.setView([point.latitude, point.longitude], 15);

    // Find and open the marker popup
    clusterGroup.eachLayer(function(layer) {
        if (layer.pointData && layer.pointData.id === point.id) {
            layer.openPopup();
        }
    });
}

function showPointDetail(pointId) {
    showLoading(true, 'Memuat detail...');

    $.get(`{{ route('maps.points') }}/../point/${pointId}`)
    .done(function(response) {
        if (response.success) {
            const point = response.data;

            $('#modal-point-name').text(point.name);

            const content = `
                <div class="text-sm space-y-3">
                    <div><strong>ID:</strong> ${point.id}</div>
                    <div><strong>Mitra:</strong>
                        <span class="inline-flex items-center">
                            <div class="w-3 h-3 rounded-full mr-2" style="background-color: ${point.mitra.color}"></div>
                            ${point.mitra.name}
                        </span>
                    </div>
                    <div><strong>Koordinat:</strong> ${point.coordinates}</div>
                    <div><strong>Latitude:</strong> ${point.latitude}</div>
                    <div><strong>Longitude:</strong> ${point.longitude}</div>
                    ${point.description ? `<div><strong>Deskripsi:</strong> ${point.description}</div>` : ''}
                    ${point.type_point ? `<div><strong>Tipe:</strong> ${point.type_point}</div>` : ''}
                    ${point.has_file ? '<div><strong>File:</strong> <span class="text-green-600">Ada file terlampir</span></div>' : ''}
                    <div><strong>Dibuat:</strong> ${new Date(point.created_at).toLocaleString('id-ID')}</div>
                    <div><strong>Diupdate:</strong> ${new Date(point.updated_at).toLocaleString('id-ID')}</div>
                </div>
            `;

            $('#modal-point-content').html(content);

            $('#view-on-google-maps').off('click').on('click', function() {
                openGoogleMaps(point.coordinates);
            });

            $('#point-detail-modal').removeClass('hidden');
        }
    })
    .fail(function() {
        showNotification('Gagal memuat detail point', 'error');
    })
    .always(function() {
        showLoading(false);
    });
}

function closePointModal() {
    $('#point-detail-modal').addClass('hidden');
}

function toggleClustering(enabled) {
    if (enabled) {
        if (!map.hasLayer(clusterGroup)) {
            map.addLayer(clusterGroup);
        }
    } else {
        map.removeLayer(clusterGroup);
        // Add individual markers
        const markersGroup = L.layerGroup();
        clusterGroup.eachLayer(function(layer) {
            markersGroup.addLayer(layer);
        });
        map.addLayer(markersGroup);
    }
}

function toggleLabels(show) {
    // Implementation for showing/hiding labels
    showNotification('Fitur label dalam pengembangan', 'info');
}

function toggleCoordinates(show) {
    // Implementation for showing coordinates on markers
    showNotification('Fitur koordinat dalam pengembangan', 'info');
}

function toggleControls() {
    const controls = $('#map-controls');
    const toggleBtn = $('#controls-toggle i');

    if (controls.hasClass('controls-collapsed')) {
        // Show controls
        controls.removeClass('controls-collapsed');
        toggleBtn.removeClass('fa-chevron-right').addClass('fa-chevron-left');
    } else {
        // Hide controls
        controls.addClass('controls-collapsed');
        toggleBtn.removeClass('fa-chevron-left').addClass('fa-chevron-right');
    }
}

function fitAllPoints() {
    if (currentPoints.length === 0) {
        showNotification('Tidak ada points untuk ditampilkan', 'warning');
        return;
    }

    const group = new L.featureGroup();
    clusterGroup.eachLayer(function(layer) {
        group.addLayer(layer);
    });

    if (group.getLayers().length > 0) {
        map.fitBounds(group.getBounds(), {
            padding: [20, 20]
        });
    }
}

function showMyLocation() {
    if (navigator.geolocation) {
        showLoading(true, 'Mencari lokasi...');

        navigator.geolocation.getCurrentPosition(function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;

            map.setView([lat, lng], 13);

            L.marker([lat, lng])
                .addTo(map)
                .bindPopup('Lokasi Anda')
                .openPopup();

            showNotification('Lokasi ditemukan', 'success');
        }, function() {
            showNotification('Tidak dapat mengakses lokasi', 'error');
        });

        showLoading(false);
    } else {
        showNotification('Geolocation tidak didukung browser', 'error');
    }
}

function refreshMapData() {
    showNotification('Memuat ulang data...', 'info');
    loadInitialData();
}

function clearMapCache() {
    showLoading(true, 'Clearing cache...');

    $.post('{{ route("maps.cache.clear") }}', {
        _token: '{{ csrf_token() }}',
        type: 'all'
    })
    .done(function(response) {
        if (response.success) {
            showNotification('Cache berhasil dibersihkan', 'success');
            loadInitialData(); // Reload data
        }
    })
    .fail(function() {
        showNotification('Gagal membersihkan cache', 'error');
    })
    .always(function() {
        showLoading(false);
    });
}

function toggleFullscreen() {
    const mapContainer = $('#map').parent();

    if (!mapContainer.hasClass('map-fullscreen')) {
        // Enter fullscreen mode
        mapContainer.addClass('map-fullscreen');
        $('#fullscreen-toggle i').removeClass('fa-expand').addClass('fa-compress');

        // Resize map after transition
        setTimeout(() => {
            map.invalidateSize();
        }, 100);

        showNotification('Mode fullscreen aktif. Tekan ESC untuk keluar.', 'info');
    } else {
        // Exit fullscreen mode
        mapContainer.removeClass('map-fullscreen');
        $('#fullscreen-toggle i').removeClass('fa-compress').addClass('fa-expand');

        // Resize map after transition
        setTimeout(() => {
            map.invalidateSize();
        }, 100);
    }
}

function showLoading(show, message = 'Memuat...') {
    if (show) {
        $('#loading-indicator span').text(message);
        $('#loading-indicator').show();
    } else {
        $('#loading-indicator').hide();
    }
}

function updateStatistics() {
    $('#visible-points').text(currentPoints.length);
}

function trackCacheUsage(response) {
    totalRequests++;
    if (response.cached) cacheHits++;

    const hitRate = ((cacheHits / totalRequests) * 100).toFixed(1);
    updateCacheStatus(response.cached ? 'HIT' : 'MISS', hitRate);
}

function updateCacheStatus(status, hitRate = null) {
    const statusElement = $('#cache-status');
    const indicatorElement = $('#cache-indicator');

    if (status === 'HIT') {
        statusElement.text('HIT').addClass('text-green-600').removeClass('text-red-600');
        indicatorElement.removeClass('cache-miss').addClass('cache-hit');
    } else {
        statusElement.text('MISS').addClass('text-red-600').removeClass('text-green-600');
        indicatorElement.removeClass('cache-hit').addClass('cache-miss');
    }

    if (hitRate !== null) {
        indicatorElement.text(`Cache: ${hitRate}% hit rate`);
    }
}

function openGoogleMaps(coordinates) {
    window.open(`https://www.google.com/maps?q=${coordinates}`, '_blank');
}

function showNotification(message, type = 'info') {
    const notification = $(`
        <div class="fixed top-4 right-4 z-[10001] max-w-sm p-4 rounded-lg shadow-lg ${
            type === 'error' ? 'bg-red-500 text-white' :
            type === 'success' ? 'bg-green-500 text-white' :
            type === 'warning' ? 'bg-yellow-500 text-white' :
            'bg-blue-500 text-white'
        }">
            <div class="flex items-center">
                <i class="fas ${
                    type === 'error' ? 'fa-exclamation-circle' :
                    type === 'success' ? 'fa-check-circle' :
                    type === 'warning' ? 'fa-exclamation-triangle' :
                    'fa-info-circle'
                } mr-2"></i>
                ${message}
            </div>
        </div>
    `);

    $('body').append(notification);

    setTimeout(() => {
        notification.fadeOut(() => notification.remove());
    }, 3000);
}

// Handle ESC key for fullscreen exit and modal close
$(document).on('keydown', function(e) {
    if (e.key === 'Escape') {
        const mapContainer = $('#map').parent();
        if (mapContainer.hasClass('map-fullscreen')) {
            toggleFullscreen();
        }

        // Close point detail modal if open
        if (!$('#point-detail-modal').hasClass('hidden')) {
            closePointModal();
        }

        // Hide search results
        $('#search-results').hide();
    }
});

// Close modal when clicking outside
$(document).on('click', '#point-detail-modal', function(e) {
    if (e.target === this) {
        closePointModal();
    }
});

// Handle fullscreen changes
document.addEventListener('fullscreenchange', function() {
    if (!document.fullscreenElement) {
        $('#fullscreen-toggle i').removeClass('fa-compress').addClass('fa-expand');
    }
});
</script>
@endpush
