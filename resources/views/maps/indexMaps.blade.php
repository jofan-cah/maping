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
        max-width: 300px;
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

    .fullscreen-btn {
        position: absolute;
        top: 10px;
        right: 230px;
        z-index: 1000;
        background: white;
        border: none;
        border-radius: 6px;
        padding: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        cursor: pointer;
        transition: all 0.2s;
    }

    .fullscreen-btn:hover {
        background: #f3f4f6;
        transform: translateY(-1px);
    }

    .performance-info {
        position: absolute;
        bottom: 20px;
        left: 20px;
        z-index: 1000;
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 11px;
        font-family: monospace;
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
        background: white;
    }

    .map-fullscreen .map-controls,
    .map-fullscreen .stats-panel,
    .map-fullscreen .fullscreen-btn,
    .map-fullscreen .performance-info {
        z-index: 10000 !important;
    }

    .stat-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .stat-item:last-child {
        margin-bottom: 0;
    }

    /* Virtual marker styles */
    .virtual-marker {
        background: none;
        border: none;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        border: 1px solid white;
        box-shadow: 0 1px 2px rgba(0,0,0,0.3);
    }

    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.9);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 2000;
    }

    .spinner {
        width: 20px;
        height: 20px;
        border: 2px solid #f3f3f3;
        border-top: 2px solid #3498db;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-right: 10px;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endpush

@section('content')
<div class="h-screen relative">
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
        <span>Memproses data...</span>
    </div>

    <!-- Map Container -->
    <div id="map" class="w-full h-full"></div>

    <!-- Fullscreen Button -->
    <button id="fullscreenBtn" class="fullscreen-btn" title="Toggle Fullscreen">
        <i class="fas fa-expand" id="fullscreenIcon"></i>
    </button>

    <!-- Map Controls -->
    <div class="map-controls">
        <div class="space-y-4">
            <!-- Performance Mode -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Mode Rendering</label>
                <div class="space-y-2">
                    <label class="flex items-center">
                        <input type="radio" name="render-mode" value="virtual" checked class="mr-2">
                        <span class="text-sm">Virtual (Optimal)</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="render-mode" value="all" class="mr-2">
                        <span class="text-sm">Semua Points</span>
                    </label>
                </div>
            </div>

            <!-- Mitra Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Filter Mitra</label>
                <select id="mitraFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                    <option value="">Semua Mitra</option>
                    @foreach($mitras as $mitra)
                        <option value="{{ $mitra->mitra_id }}" data-color="{{ $mitra->warna_pt }}">
                            {{ $mitra->nama_pt }} ({{ $mitra->mitra_turunans_count }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Cari Point</label>
                <input type="text" id="searchInput" placeholder="Nama point atau mitra..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
            </div>

            <!-- Actions -->
            <div>
                <button id="fitAllBtn" class="w-full px-3 py-1 bg-blue-500 text-white text-sm rounded hover:bg-blue-600 mb-2">
                    Fit All Points
                </button>
                <button id="refreshBtn" class="w-full px-3 py-1 bg-gray-500 text-white text-sm rounded hover:bg-gray-600">
                    Refresh
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Panel -->
    <div class="stats-panel">
        <h3 class="font-medium text-gray-900 mb-3">Statistik</h3>
        <div class="stat-item">
            <span class="text-sm text-gray-600">Total:</span>
            <span class="text-sm font-medium" id="totalPoints">{{ $stats['total_points'] }}</span>
        </div>
        <div class="stat-item">
            <span class="text-sm text-gray-600">Rendered:</span>
            <span class="text-sm font-medium text-green-600" id="renderedPoints">0</span>
        </div>
        <div class="stat-item">
            <span class="text-sm text-gray-600">Mode:</span>
            <span class="text-xs font-medium text-blue-600" id="currentMode">Virtual</span>
        </div>
    </div>

    <!-- Performance Info -->
    <div class="performance-info">
        FPS: <span id="fpsCounter">60</span> |
        Render: <span id="renderTime">0</span>ms
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
class MapRenderer {
    constructor() {
        this.map = null;
        this.allPoints = @json($points);
        this.currentPoints = [...this.allPoints];
        this.visibleMarkers = new Map();
        this.renderMode = 'virtual';
        this.maxMarkersInViewport = 800;

        this.init();
    }

    init() {
        this.initMap();
        this.setupEvents();
        this.startPerformanceMonitor();
        this.render();
    }

    initMap() {
        this.map = L.map('map').setView([-2.5489, 118.0149], 5);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(this.map);

        // Map events
        this.map.on('moveend zoomend', () => {
            if (this.renderMode === 'virtual') {
                this.throttledRender();
            }
        });
    }

    setupEvents() {
        // Fullscreen button
        $('#fullscreenBtn').on('click', () => this.toggleFullscreen());

        // Render mode
        $('input[name="render-mode"]').on('change', (e) => {
            this.setRenderMode(e.target.value);
        });

        // Mitra filter
        $('#mitraFilter').on('change', (e) => {
            this.filterByMitra(e.target.value);
        });

        // Search
        let searchTimeout;
        $('#searchInput').on('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                this.searchPoints(e.target.value);
            }, 300);
        });

        // Actions
        $('#fitAllBtn').on('click', () => this.fitAllPoints());
        $('#refreshBtn').on('click', () => location.reload());

        // ESC key
        $(document).on('keydown', (e) => {
            if (e.key === 'Escape' && $('.h-screen').hasClass('map-fullscreen')) {
                this.toggleFullscreen();
            }
        });
    }

    setRenderMode(mode) {
        this.renderMode = mode;
        $('#currentMode').text(mode === 'virtual' ? 'Virtual' : 'All');
        this.clearAllMarkers();
        this.render();
    }

    render() {
        const startTime = performance.now();

        if (this.renderMode === 'virtual') {
            this.renderVirtual();
        } else {
            this.renderAll();
        }

        const endTime = performance.now();
        $('#renderTime').text(Math.round(endTime - startTime));
    }

    renderVirtual() {
        const bounds = this.map.getBounds();
        const buffer = 0.1;

        const latBuffer = (bounds.getNorth() - bounds.getSouth()) * buffer;
        const lngBuffer = (bounds.getEast() - bounds.getWest()) * buffer;

        const extendedBounds = L.latLngBounds([
            [bounds.getSouth() - latBuffer, bounds.getWest() - lngBuffer],
            [bounds.getNorth() + latBuffer, bounds.getEast() + lngBuffer]
        ]);

        const viewportPoints = this.currentPoints.filter(point => {
            if (point.latitude === 0 && point.longitude === 0) return false;
            return extendedBounds.contains([point.latitude, point.longitude]);
        });

        let pointsToRender = viewportPoints;
        if (viewportPoints.length > this.maxMarkersInViewport) {
            const step = Math.ceil(viewportPoints.length / this.maxMarkersInViewport);
            pointsToRender = viewportPoints.filter((_, index) => index % step === 0);
        }

        this.clearMarkersOutsideViewport(extendedBounds);

        pointsToRender.forEach(point => {
            if (!this.visibleMarkers.has(point.id)) {
                this.createMarker(point);
            }
        });

        $('#renderedPoints').text(pointsToRender.length);
    }

    renderAll() {
        $('#loadingOverlay').show();

        setTimeout(() => {
            this.clearAllMarkers();

            this.currentPoints.forEach(point => {
                if (point.latitude !== 0 || point.longitude !== 0) {
                    this.createMarker(point, true);
                }
            });

            $('#renderedPoints').text(this.currentPoints.length);
            $('#loadingOverlay').hide();
        }, 100);
    }

    createMarker(point, isSmall = false) {
        const size = isSmall ? 6 : 8;

        const marker = L.divIcon({
            className: 'virtual-marker',
            html: `<div style="background-color: ${point.mitra.color}; width: ${size}px; height: ${size}px; border-radius: 50%; border: 1px solid white; box-shadow: 0 1px 2px rgba(0,0,0,0.3);"></div>`,
            iconSize: [size, size],
            iconAnchor: [size/2, size/2]
        });

        const mapMarker = L.marker([point.latitude, point.longitude], {
            icon: marker
        });

        mapMarker.bindPopup(`
            <div class="p-2">
                <div class="font-medium">${point.name}</div>
                <div class="text-sm text-gray-600">
                    <div class="flex items-center">
                        <div class="w-2 h-2 rounded-full mr-2" style="background: ${point.mitra.color}"></div>
                        ${point.mitra.name}
                    </div>
                    <div class="text-xs mt-1">${point.coordinates}</div>
                </div>
            </div>
        `, { maxWidth: 200 });

        mapMarker.addTo(this.map);
        this.visibleMarkers.set(point.id, mapMarker);
    }

    clearMarkersOutsideViewport(bounds) {
        this.visibleMarkers.forEach((marker, pointId) => {
            const position = marker.getLatLng();
            if (!bounds.contains(position)) {
                this.map.removeLayer(marker);
                this.visibleMarkers.delete(pointId);
            }
        });
    }

    clearAllMarkers() {
        this.visibleMarkers.forEach(marker => {
            this.map.removeLayer(marker);
        });
        this.visibleMarkers.clear();
    }

    filterByMitra(mitraId) {
        if (mitraId === '') {
            this.currentPoints = [...this.allPoints];
        } else {
            this.currentPoints = this.allPoints.filter(point => point.mitra.id === mitraId);
        }

        this.clearAllMarkers();
        this.render();
    }

    searchPoints(query) {
        if (query.length < 2) return;

        const results = this.allPoints.filter(point =>
            point.name.toLowerCase().includes(query.toLowerCase()) ||
            point.mitra.name.toLowerCase().includes(query.toLowerCase())
        ).slice(0, 5);

        if (results.length > 0) {
            const firstResult = results[0];
            this.map.setView([firstResult.latitude, firstResult.longitude], 15);
        }
    }

    fitAllPoints() {
        if (this.currentPoints.length === 0) return;

        const validPoints = this.currentPoints.filter(p => p.latitude !== 0 || p.longitude !== 0);
        if (validPoints.length === 0) return;

        const bounds = L.latLngBounds(validPoints.map(p => [p.latitude, p.longitude]));
        this.map.fitBounds(bounds, { padding: [20, 20] });
    }

    toggleFullscreen() {
        const container = $('.h-screen');
        const icon = $('#fullscreenIcon');

        if (!container.hasClass('map-fullscreen')) {
            container.addClass('map-fullscreen');
            icon.removeClass('fa-expand').addClass('fa-compress');

            setTimeout(() => {
                this.map.invalidateSize();
            }, 100);
        } else {
            container.removeClass('map-fullscreen');
            icon.removeClass('fa-compress').addClass('fa-expand');

            setTimeout(() => {
                this.map.invalidateSize();
            }, 100);
        }
    }

    throttledRender = this.throttle(() => this.render(), 100);

    throttle(func, delay) {
        let timeoutId;
        return (...args) => {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => func.apply(this, args), delay);
        };
    }

    startPerformanceMonitor() {
        let frameCount = 0;
        let lastTime = performance.now();

        const updateFPS = () => {
            frameCount++;
            const currentTime = performance.now();

            if (currentTime - lastTime >= 1000) {
                const fps = Math.round(frameCount * 1000 / (currentTime - lastTime));
                $('#fpsCounter').text(fps);

                frameCount = 0;
                lastTime = currentTime;
            }

            requestAnimationFrame(updateFPS);
        };

        updateFPS();
    }
}

// Initialize
$(document).ready(() => {
    window.mapRenderer = new MapRenderer();
});
</script>
@endpush
