@extends('layouts.app')

@section('title', 'Coverage Analysis')
@section('page-title', 'Coverage Analysis')

@section('breadcrumb')
    <li><a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-home"></i></a></li>
    <li><span class="text-gray-400">/</span></li>
    <li><span class="text-gray-600 font-medium">Coverage Analysis</span></li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map, #map-fullscreen {
        height: 500px;
        width: 100%;
        z-index: 1;
    }

    .routing-instructions {
        padding: 10px;
        margin: 10px 0;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 4px;
        max-height: 150px;
        overflow-y: auto;
    }

    /* Hide the default routing container that appears on map */
    .leaflet-routing-container {
        display: none;
    }

    /* Map container with relative positioning for fullscreen button */
    .map-container {
        position: relative;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    /* Fullscreen Button */
    .fullscreen-btn {
        position: absolute;
        top: 15px;
        right: 15px;
        z-index: 1000;
        background: white;
        border: none;
        border-radius: 6px;
        padding: 8px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
        cursor: pointer;
        transition: all 0.2s ease;
        width: 34px;
        height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .fullscreen-btn:hover {
        background: #f8f9fa;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.4);
    }

    .fullscreen-btn svg {
        width: 18px;
        height: 18px;
        color: #333;
    }

    /* Fullscreen Modal */
    .fullscreen-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.9);
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }

    .fullscreen-modal.active {
        display: flex;
    }

    .fullscreen-modal-content {
        width: 95%;
        height: 95%;
        position: relative;
        border-radius: 8px;
        overflow: hidden;
    }

    .close-fullscreen-btn {
        position: absolute;
        top: 20px;
        right: 20px;
        z-index: 10000;
        background: white;
        border: none;
        border-radius: 50%;
        padding: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
        cursor: pointer;
        transition: all 0.2s ease;
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .close-fullscreen-btn:hover {
        background: #f8f9fa;
        transform: scale(1.1);
    }

    .close-fullscreen-btn svg {
        width: 20px;
        height: 20px;
        color: #333;
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <div class="bg-white p-4 rounded-lg shadow-lg w-full overflow-x-auto">
        <p class="text-lg font-medium text-slate-500 tracking-wider">Coverage Analysis</p>

        <div class="mb-4 flex flex-wrap items-center justify-between">
            <div class="flex flex-wrap items-center mb-4 md:mb-0 gap-2">
                <input id="user-coor"
                    class="my-2 p-2 block text-sm border focus:outline-none focus:border-sky-500 focus:ring-sky-500 border-gray-300 rounded-lg cursor-pointer bg-gray-50 w-full md:w-[300px]"
                    type="text" placeholder="Cari berdasarkan coordinate atau alamat...">
                <button id="find"
                    class="px-4 h-9 text-white bg-indigo-700 hover:bg-indigo-800 focus:ring-4 focus:outline-none focus:ring-indigo-300 font-medium rounded-lg text-sm text-center w-full md:w-auto">
                    Find
                </button>
                <button id="gps"
                    class="px-4 h-9 text-white bg-teal-700 hover:bg-teal-800 focus:ring-4 focus:outline-none focus:ring-teal-300 font-medium rounded-lg text-sm text-center w-full md:w-auto">
                    GPS
                </button>
                <button id="clear-route"
                    class="px-4 h-9 text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm text-center w-full md:w-auto">
                    Clear
                </button>
            </div>

            <!-- Routing Container -->
            <div id="routing-container"
                class="routing-instructions p-4 border rounded-lg bg-gray-50 shadow-md w-full md:w-[400px]">
                <p class="text-sm text-gray-700">Routing instructions will appear here.</p>
            </div>
        </div>

        <!-- Map Container with Fullscreen Button -->
        <div class="map-container">
            <button class="fullscreen-btn" id="fullscreen-btn" title="Fullscreen">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                </svg>
            </button>
            <div id="map" class="h-[400px] z-0"></div>
        </div>
    </div>
</div>

<!-- Fullscreen Modal -->
<div id="fullscreen-modal" class="fullscreen-modal">
    <div class="fullscreen-modal-content">
        <button class="close-fullscreen-btn" id="close-fullscreen-btn" title="Close Fullscreen">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
        <div id="map-fullscreen"></div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>

<script>
// Global variables for fullscreen
let map, fullscreenMap;
let route, fullscreenRoute;
let markerA, markerB;
let fullscreenMarkerA, fullscreenMarkerB;
let allPoints = [];

// Get address from coordinates
async function getAddress(lat, lng) {
    try {
        const response = await fetch(
            `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`);
        const data = await response.json();
        return data.display_name;
    } catch (error) {
        console.error('Error getting address:', error);
        return 'Alamat tidak ditemukan';
    }
}

function createMarkerIcon(color) {
    return new L.Icon({
        iconUrl: 'https://cdn.jsdelivr.net/gh/pointhi/leaflet-color-markers@master/img/marker-icon-2x-' +
            color + '.png',
        iconSize: [20, 30],
        iconAnchor: [10, 30],
        popupAnchor: [0, -25],
    });
}

function createMitraMarkerIcon(color) {
    return L.divIcon({
        className: 'custom-div-icon',
        html: `<div style="background-color: ${color}; width: 12px; height: 12px; border-radius: 50%; border: 2px solid white; box-shadow: 0 1px 3px rgba(0,0,0,0.3);"></div>`,
        iconSize: [16, 16],
        iconAnchor: [8, 8]
    });
}

function createCustomMarkerIcon(iconUrl) {
    return new L.Icon({
        iconUrl: iconUrl,
        iconSize: [40, 55],
        iconAnchor: [35, 50],
        popupAnchor: [1, -34],
    });
}

function findNearestPoint(searchCoord, allPoints) {
    if (!allPoints || allPoints.length === 0) {
        return null;
    }

    function getDistance(coord1, coord2) {
        const lat1 = coord1[0];
        const lon1 = coord1[1];
        const lat2 = coord2[0];
        const lon2 = coord2[1];

        const dLat = lat2 - lat1;
        const dLon = lon2 - lon1;
        return Math.sqrt(dLat * dLat + dLon * dLon);
    }

    let nearestPoint = allPoints[0];
    let nearestDistance = getDistance(searchCoord, [nearestPoint.latitude, nearestPoint.longitude]);

    for (let i = 1; i < allPoints.length; i++) {
        const currentPoint = allPoints[i];
        const distance = getDistance(searchCoord, [currentPoint.latitude, currentPoint.longitude]);

        if (distance < nearestDistance) {
            nearestPoint = currentPoint;
            nearestDistance = distance;
        }
    }
    return nearestPoint;
}

function createRoutingControl(targetMap) {
    return L.Routing.control({
        router: L.Routing.osrmv1({
            serviceUrl: '/osrm/route', // Update serviceUrl
            useHints: false,
            route: function(waypoints, callback, context, options) {
                const coords = waypoints.map(wp => `${wp.latLng.lng},${wp.latLng.lat}`).join(';');
                const url = `/osrm/route/${coords}?overview=false&alternatives=true&steps=true`;

                fetch(url)
                    .then(res => res.json())
                    .then(data => {
                        const routes = data.routes.map(route => ({
                            name: route.legs[0].summary || 'Route',
                            coordinates: route.legs[0].steps.map(step =>
                                step.maneuver.location.reverse()
                            ),
                            summary: {
                                totalDistance: route.distance,
                                totalTime: route.duration
                            }
                        }));

                        callback.call(context, null, routes);
                    })
                    .catch(error => callback.call(context, error));
            }
        }),

        show: false,
        containerClassName: 'display-none',
        createMarker: function(i, waypoint, n) {
            if (i === 0) {
                const marker = L.marker(waypoint.latLng, {
                    icon: createMarkerIcon('red'),
                    draggable: true
                }).bindPopup('Point A (Pencarian)');

                if (targetMap === map) {
                    if (markerA) targetMap.removeLayer(markerA);
                    markerA = marker;
                } else {
                    if (fullscreenMarkerA) targetMap.removeLayer(fullscreenMarkerA);
                    fullscreenMarkerA = marker;
                }

                // Add drag event
                marker.on('dragend', function() {
                    const newPos = marker.getLatLng();
                    updateRouteFromDrag(0, newPos);
                });

                return marker;
            } else {
                const marker = L.marker(waypoint.latLng, {
                    icon: createMarkerIcon('blue'),
                    draggable: true
                }).bindPopup('Point B (Terdekat)');

                if (targetMap === map) {
                    if (markerB) targetMap.removeLayer(markerB);
                    markerB = marker;
                } else {
                    if (fullscreenMarkerB) targetMap.removeLayer(fullscreenMarkerB);
                    fullscreenMarkerB = marker;
                }

                // Add drag event
                marker.on('dragend', function() {
                    const newPos = marker.getLatLng();
                    updateRouteFromDrag(1, newPos);
                });

                return marker;
            }
        },
        lineOptions: {
            styles: [{
                color: '#03f',
                opacity: 0.7,
                weight: 5
            }]
        }
    });
}

function updateRouteFromDrag(waypointIndex, newPos) {
    // Update both maps when dragging
    if (route) {
        const waypoints = route.getWaypoints();
        waypoints[waypointIndex].latLng = newPos;
        route.setWaypoints(waypoints);
    }

    if (fullscreenRoute) {
        const waypoints = fullscreenRoute.getWaypoints();
        waypoints[waypointIndex].latLng = newPos;
        fullscreenRoute.setWaypoints(waypoints);
    }
}

// Initialize maps
function initializeMap() {
    map = L.map('map').setView([-2.5489, 118.0149], 5);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap'
    }).addTo(map);
}

function initializeFullscreenMap() {
    if (fullscreenMap) return;

    fullscreenMap = L.map('map-fullscreen').setView([-2.5489, 118.0149], 5);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap'
    }).addTo(fullscreenMap);

    // Load points on fullscreen map
    loadPointsOnMap(fullscreenMap);
}

function loadPoints() {
    $.get('/coverage/points')
        .done(function(data) {
            allPoints = data;
            loadPointsOnMap(map);
            console.log(`Loaded ${allPoints.length} points`);
        })
        .fail(function() {
            console.error('Failed to load points');
        });
}

function loadPointsOnMap(targetMap) {
    allPoints.forEach(point => {
        if (point.latitude === 0 && point.longitude === 0) return;

        const marker = L.marker([point.latitude, point.longitude], {
            icon: createMitraMarkerIcon(point.mitra.warna_pt)
        }).addTo(targetMap);

        marker.bindPopup(`
            <div class="p-2">
                <div class="font-medium text-gray-900 mb-2">${point.point_name}</div>
                <div class="text-sm text-gray-600 mb-2">
                    <div class="flex items-center mb-1">
                        <div class="w-3 h-3 rounded-full mr-2" style="background-color: ${point.mitra.warna_pt}"></div>
                        <span>${point.mitra.nama_pt}</span>
                    </div>
                    <div class="text-xs text-gray-500">${point.point_location_maps}</div>
                </div>
                ${point.description ? `<div class="text-sm text-gray-600 mb-2">${point.description}</div>` : ''}
            </div>
        `);
    });
}

// Initialize main map
$(document).ready(function() {
    initializeMap();
    loadPoints();

    // Initialize routing controls
    route = createRoutingControl(map);
    route.addTo(map);

    // Routing event listeners
    route.on('routesfound', async function(e) {
        let routes = e.routes;
        let summary = routes[0].summary;
        const startPoint = routes[0].coordinates[0];
        const startAddress = await getAddress(startPoint.lat, startPoint.lng);

        let routingHtml = `
            <div class="routing-info">
                <p><strong>Alamat:</strong> ${startAddress}</p>
                <p><strong>Jarak:</strong> ${(summary.totalDistance / 1000).toFixed(2)} km</p>
                <p><strong>Waktu:</strong> ${Math.round(summary.totalTime / 60)} menit</p>
            </div>
        `;

        $('#routing-container').html(routingHtml);
    });

    // Event handlers
    let userCoor = $('#user-coor');
    let btnFind = $('#find');
    let btnGps = $('#gps');
    let btnClear = $('#clear-route');

    btnFind.on('click', function() {
        const input = userCoor.val().trim();
        if (!input) return;

        // Check if input is coordinate format
        const regexCoor = /^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?),\s*[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$/;

        if (regexCoor.test(input)) {
            // Direct coordinate
            const coords = input.split(',').map(c => parseFloat(c.trim()));
            findRoute(coords[0], coords[1]);
        } else {
            // Search address
            searchAddress(input);
        }
    });

    btnGps.on('click', function() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                userCoor.val(`${lat},${lng}`);
                findRoute(lat, lng);
            }, function() {
                alert('Tidak dapat mengakses lokasi GPS');
            });
        } else {
            alert('GPS tidak didukung browser ini');
        }
    });

    btnClear.on('click', function() {
        // Clear route
        if (route) route.setWaypoints([]);
        if (fullscreenRoute) fullscreenRoute.setWaypoints([]);

        // Clear markers
        if (markerA) {
            map.removeLayer(markerA);
            markerA = null;
        }
        if (markerB) {
            map.removeLayer(markerB);
            markerB = null;
        }
        if (fullscreenMarkerA && fullscreenMap) {
            fullscreenMap.removeLayer(fullscreenMarkerA);
            fullscreenMarkerA = null;
        }
        if (fullscreenMarkerB && fullscreenMap) {
            fullscreenMap.removeLayer(fullscreenMarkerB);
            fullscreenMarkerB = null;
        }

        // Clear input and instructions
        userCoor.val('');
        $('#routing-container').html('<p class="text-sm text-gray-700">Routing instructions will appear here.</p>');
    });

    // Enter key support
    userCoor.on('keypress', function(e) {
        if (e.which === 13) {
            btnFind.click();
        }
    });
});

function searchAddress(address) {
    const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&countrycodes=ID&limit=1`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.length > 0) {
                const lat = parseFloat(data[0].lat);
                const lng = parseFloat(data[0].lon);
                $('#user-coor').val(`${lat},${lng}`);
                findRoute(lat, lng);
            } else {
                alert('Alamat tidak ditemukan');
            }
        })
        .catch(() => {
            alert('Error dalam pencarian alamat');
        });
}

function findRoute(lat, lng) {
    if (allPoints.length === 0) {
        alert('Data points belum dimuat');
        return;
    }

    // Find nearest point
    const nearestPoint = findNearestPoint([lat, lng], allPoints);

    if (!nearestPoint) {
        alert('Tidak ada point terdekat yang ditemukan');
        return;
    }

    // Set waypoints for routing
    const waypoints = [
        L.latLng(lat, lng),
        L.latLng(nearestPoint.latitude, nearestPoint.longitude)
    ];

    route.setWaypoints(waypoints);
    route.show();

    // Update fullscreen route if exists
    if (fullscreenRoute) {
        fullscreenRoute.setWaypoints(waypoints);
        fullscreenRoute.show();
    }

    // Pan map to show both points
    const group = new L.featureGroup([
        L.marker([lat, lng]),
        L.marker([nearestPoint.latitude, nearestPoint.longitude])
    ]);
    map.fitBounds(group.getBounds(), { padding: [20, 20] });
}

// Fullscreen functionality
document.getElementById('fullscreen-btn').addEventListener('click', function() {
    const modal = document.getElementById('fullscreen-modal');
    modal.classList.add('active');

    setTimeout(() => {
        if (!fullscreenMap) {
            initializeFullscreenMap();
        }

        // Initialize fullscreen routing if not exists
        if (!fullscreenRoute) {
            fullscreenRoute = createRoutingControl(fullscreenMap);
            fullscreenRoute.addTo(fullscreenMap);
        }

        // Copy current route if exists
        if (route && route.getWaypoints().length > 0) {
            fullscreenRoute.setWaypoints(route.getWaypoints());
            fullscreenRoute.show();
        }

        // Copy view from main map
        fullscreenMap.setView(map.getCenter(), map.getZoom());
        fullscreenMap.invalidateSize();
    }, 100);
});

// Close fullscreen
document.getElementById('close-fullscreen-btn').addEventListener('click', function() {
    const modal = document.getElementById('fullscreen-modal');
    modal.classList.remove('active');

    if (fullscreenMap && map) {
        map.setView(fullscreenMap.getCenter(), fullscreenMap.getZoom());
    }
});

// Close with ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('fullscreen-modal');
        if (modal.classList.contains('active')) {
            modal.classList.remove('active');
            if (fullscreenMap && map) {
                map.setView(fullscreenMap.getCenter(), fullscreenMap.getZoom());
            }
        }
    }
});

// Close when clicking outside
document.getElementById('fullscreen-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        this.classList.remove('active');
        if (fullscreenMap && map) {
            map.setView(fullscreenMap.getCenter(), fullscreenMap.getZoom());
        }
    }
});
</script>
@endpush
