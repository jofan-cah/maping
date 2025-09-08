<?php

namespace App\Http\Controllers;

use App\Models\Mitra;
use App\Models\MitraTurunan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MapController extends Controller
{
    /**
     * Display all points on map
     */
    public function index(Request $request)
    {
        // Cache statistics selama 30 menit
        $stats = Cache::remember('map_statistics', 1800, function() {
            $totalPoints = MitraTurunan::count();
            $totalMitras = Mitra::count();

            // Points by mitra dengan efficient query
            $pointsByMitra = DB::table('mitras as m')
                ->leftJoin('mitra_turunans as mt', 'm.mitra_id', '=', 'mt.mitra_id')
                ->select([
                    'm.mitra_id',
                    'm.nama_pt',
                    'm.warna_pt',
                    DB::raw('COUNT(mt.mitra_turunan_id) as points_count')
                ])
                ->groupBy('m.mitra_id', 'm.nama_pt', 'm.warna_pt')
                ->orderBy('m.nama_pt')
                ->get();

            // Geographic bounds dengan sample data untuk performa
            $sampleCoordinates = MitraTurunan::select('koordinat')
                ->whereRaw('MOD(CAST(SUBSTRING(mitra_turunan_id, 4) AS UNSIGNED), 50) = 0') // Sample setiap 50 data
                ->whereNotNull('koordinat')
                ->where('koordinat', '!=', '')
                ->pluck('koordinat')
                ->map(function($coord) {
                    if (empty($coord) || !str_contains($coord, ',')) return null;
                    $coords = explode(',', $coord);
                    $lat = floatval(trim($coords[0] ?? 0));
                    $lng = floatval(trim($coords[1] ?? 0));

                    // Filter koordinat yang valid
                    if ($lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180 && $lat != 0 && $lng != 0) {
                        return ['lat' => $lat, 'lng' => $lng];
                    }
                    return null;
                })
                ->filter(); // Remove null values

            $bounds = null;
            if ($sampleCoordinates->count() > 0) {
                $bounds = [
                    'north' => $sampleCoordinates->max('lat'),
                    'south' => $sampleCoordinates->min('lat'),
                    'east' => $sampleCoordinates->max('lng'),
                    'west' => $sampleCoordinates->min('lng'),
                    'center' => [
                        'lat' => $sampleCoordinates->avg('lat'),
                        'lng' => $sampleCoordinates->avg('lng')
                    ]
                ];
            }

            return [
                'total_points' => $totalPoints,
                'total_mitras' => $totalMitras,
                'points_by_mitra' => $pointsByMitra,
                'geographic_bounds' => $bounds
            ];
        });

        // Cache daftar mitra selama 1 jam
        $mitras = Cache::remember('map_mitras_list', 3600, function() {
            return Mitra::select('mitra_id', 'nama_pt', 'warna_pt', 'icon_pt')
                ->withCount('mitraTurunans')
                ->orderBy('nama_pt')
                ->get();
        });

        return view('maps.indexMaps', compact('mitras', 'stats'));
    }

    /**
     * Get all points as JSON (for AJAX/API calls) with caching and optimization
     */
    public function getPoints(Request $request)
    {
        try {
            $mitraId = $request->get('mitra_id', '');
            $bounds = $request->get('bounds', null);
            $zoom = $request->get('zoom', 5);

            // Buat cache key unik berdasarkan parameter
            $cacheKey = 'map_points_' . md5($mitraId . serialize($bounds) . $zoom);

            $points = Cache::remember($cacheKey, 600, function() use ($request, $mitraId, $bounds, $zoom) {
                $query = MitraTurunan::select([
                    'mitra_turunan_id',
                    'mitra_id',
                    'nama_point',
                    'koordinat',
                    'deskripsi',
                    'nama_file',
                    'created_at'
                ])->with(['mitra:mitra_id,nama_pt,warna_pt,icon_pt']);

                // Filter by mitra if specified
                if (!empty($mitraId)) {
                    $query->where('mitra_id', $mitraId);
                }

                // Filter koordinat yang valid
                $query->whereNotNull('koordinat')
                      ->where('koordinat', '!=', '')
                      ->where('koordinat', 'like', '%,%');

                // CRITICAL: Viewport filtering untuk performa
                if ($bounds && is_array($bounds)) {
                    $query->whereRaw("
                        CAST(SUBSTRING_INDEX(koordinat, ',', 1) AS DECIMAL(10,8)) BETWEEN ? AND ? AND
                        CAST(SUBSTRING_INDEX(koordinat, ',', -1) AS DECIMAL(11,8)) BETWEEN ? AND ?
                    ", [
                        $bounds['south'], $bounds['north'],
                        $bounds['west'], $bounds['east']
                    ]);
                }

                // Zoom level optimization - limit berdasarkan zoom
                $limit = $this->getPointLimitByZoom($zoom);
                if ($limit > 0) {
                    $query->limit($limit);
                }

                // Zoom level clustering - return fewer points for far zoom
                if ($zoom < 8) {
                    // Untuk zoom level rendah, sampel data dengan interval
                    $interval = max(1, floor(7000 / 500));
                    $query->whereRaw('MOD(CAST(SUBSTRING(mitra_turunan_id, 4) AS UNSIGNED), ?) = 0', [$interval]);
                }

                return $query->get();
            });

            // Transform data
            $transformedPoints = $points->map(function ($point) {
                $coords = explode(',', $point->koordinat);
                return [
                    'id' => $point->mitra_turunan_id,
                    'name' => $point->nama_point ?: 'Unnamed Point',
                    'latitude' => floatval(trim($coords[0] ?? 0)),
                    'longitude' => floatval(trim($coords[1] ?? 0)),
                    'coordinates' => $point->koordinat,
                    'description' => $point->deskripsi,
                    'has_file' => !is_null($point->nama_file),
                    'created_at' => $point->created_at->toISOString(),
                    'mitra' => [
                        'id' => $point->mitra->mitra_id ?? '',
                        'name' => $point->mitra->nama_pt ?? 'Unknown Mitra',
                        'color' => $point->mitra->warna_pt ?? '#6B7280',
                        'icon_url' => $point->mitra->icon_pt ? asset('storage/' . $point->mitra->icon_pt) : null
                    ]
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $transformedPoints,
                'total' => $transformedPoints->count(),
                'viewport_optimized' => !is_null($bounds),
                'cached' => true,
                'zoom_level' => $zoom
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading map points: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading points: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get clustered points for performance optimization
     */
    public function getClusteredPoints(Request $request)
    {
        try {
            $zoomLevel = $request->get('zoom', 5);
            $bounds = $request->get('bounds');
            $mitraId = $request->get('mitra_id', '');

            // Cache key untuk clustered data
            $cacheKey = 'map_clusters_' . md5($zoomLevel . serialize($bounds) . $mitraId);

            $clusters = Cache::remember($cacheKey, 300, function() use ($zoomLevel, $bounds, $mitraId) {
                // Determine cluster size based on zoom level
                $clusterSize = $this->getClusterSize($zoomLevel);

                $query = DB::table('mitra_turunans as mt')
                    ->join('mitras as m', 'mt.mitra_id', '=', 'm.mitra_id')
                    ->select([
                        DB::raw("ROUND(CAST(SUBSTRING_INDEX(mt.koordinat, ',', 1) AS DECIMAL(10,8)) / {$clusterSize}) * {$clusterSize} as cluster_lat"),
                        DB::raw("ROUND(CAST(SUBSTRING_INDEX(mt.koordinat, ',', -1) AS DECIMAL(11,8)) / {$clusterSize}) * {$clusterSize} as cluster_lng"),
                        DB::raw('COUNT(*) as point_count'),
                        DB::raw('GROUP_CONCAT(DISTINCT m.warna_pt) as colors'),
                        DB::raw('AVG(CAST(SUBSTRING_INDEX(mt.koordinat, ",", 1) AS DECIMAL(10,8))) as avg_lat'),
                        DB::raw('AVG(CAST(SUBSTRING_INDEX(mt.koordinat, ",", -1) AS DECIMAL(11,8))) as avg_lng'),
                        DB::raw('GROUP_CONCAT(DISTINCT m.nama_pt SEPARATOR ", ") as mitra_names')
                    ])
                    ->whereNotNull('mt.koordinat')
                    ->where('mt.koordinat', '!=', '')
                    ->where('mt.koordinat', 'like', '%,%');

                // Filter by mitra if specified
                if (!empty($mitraId)) {
                    $query->where('mt.mitra_id', $mitraId);
                }

                // Filter by bounds if provided
                if ($bounds && is_array($bounds)) {
                    $query->whereRaw("
                        CAST(SUBSTRING_INDEX(mt.koordinat, ',', 1) AS DECIMAL(10,8)) BETWEEN ? AND ? AND
                        CAST(SUBSTRING_INDEX(mt.koordinat, ',', -1) AS DECIMAL(11,8)) BETWEEN ? AND ?
                    ", [$bounds['south'], $bounds['north'], $bounds['west'], $bounds['east']]);
                }

                return $query->groupBy('cluster_lat', 'cluster_lng')
                    ->having('point_count', '>', 0)
                    ->get();
            });

            $transformedClusters = $clusters->map(function($cluster) {
                return [
                    'latitude' => floatval($cluster->avg_lat),
                    'longitude' => floatval($cluster->avg_lng),
                    'count' => $cluster->point_count,
                    'colors' => array_unique(explode(',', $cluster->colors)),
                    'mitra_names' => $cluster->mitra_names,
                    'is_cluster' => $cluster->point_count > 1
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $transformedClusters,
                'cluster_size' => $this->getClusterSize($zoomLevel),
                'zoom_level' => $zoomLevel,
                'cached' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading clustered points: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading clusters: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get map statistics
     */
    public function getStatistics(Request $request)
    {
        try {
            // Gunakan cache yang sama dengan index method
            $stats = Cache::remember('map_statistics', 1800, function() {
                $totalPoints = MitraTurunan::count();
                $totalMitras = Mitra::count();

                // Points by mitra
                $pointsByMitra = Mitra::withCount('mitraTurunans')
                    ->get()
                    ->map(function($mitra) {
                        return [
                            'mitra_id' => $mitra->mitra_id,
                            'nama_pt' => $mitra->nama_pt,
                            'warna_pt' => $mitra->warna_pt,
                            'points_count' => $mitra->mitra_turunans_count
                        ];
                    });

                return [
                    'total_points' => $totalPoints,
                    'total_mitras' => $totalMitras,
                    'points_by_mitra' => $pointsByMitra
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $stats,
                'cached' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading map statistics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search points by name or description with caching
     */
    public function searchPoints(Request $request)
    {
        try {
            $search = $request->get('q', '');

            if (empty($search) || strlen($search) < 2) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }

            // Cache search results selama 5 menit
            $cacheKey = 'map_search_' . md5(strtolower(trim($search)));

            $points = Cache::remember($cacheKey, 300, function() use ($search) {
                return MitraTurunan::select([
                    'mitra_turunan_id',
                    'mitra_id',
                    'nama_point',
                    'koordinat',
                    'deskripsi'
                ])
                ->with(['mitra:mitra_id,nama_pt,warna_pt'])
                ->whereNotNull('koordinat')
                ->where('koordinat', '!=', '')
                ->where('koordinat', 'like', '%,%')
                ->where(function($query) use ($search) {
                    $query->where('nama_point', 'like', $search . '%')  // Prefix search untuk index performance
                          ->orWhere('mitra_turunan_id', 'like', $search . '%')
                          ->orWhere('koordinat', 'like', '%' . $search . '%')
                          ->orWhere('deskripsi', 'like', '%' . $search . '%');
                })
                ->limit(20) // Batasi hasil pencarian
                ->get();
            });

            $transformedPoints = $points->map(function ($point) {
                $coords = explode(',', $point->koordinat);
                return [
                    'id' => $point->mitra_turunan_id,
                    'name' => $point->nama_point ?: 'Unnamed Point',
                    'latitude' => floatval(trim($coords[0] ?? 0)),
                    'longitude' => floatval(trim($coords[1] ?? 0)),
                    'coordinates' => $point->koordinat,
                    'description' => $point->deskripsi,
                    'mitra' => [
                        'id' => $point->mitra->mitra_id ?? '',
                        'name' => $point->mitra->nama_pt ?? 'Unknown Mitra',
                        'color' => $point->mitra->warna_pt ?? '#6B7280'
                    ]
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $transformedPoints,
                'total' => $transformedPoints->count(),
                'query' => $search,
                'cached' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Error searching points: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error searching points: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single point detail
     */
    public function getPointDetail($id)
    {
        try {
            // Cache individual point detail selama 10 menit
            $cacheKey = 'map_point_detail_' . $id;

            $point = Cache::remember($cacheKey, 600, function() use ($id) {
                return MitraTurunan::with('mitra')
                    ->where('mitra_turunan_id', $id)
                    ->first();
            });

            if (!$point) {
                return response()->json([
                    'success' => false,
                    'message' => 'Point not found'
                ], 404);
            }

            $coords = explode(',', $point->koordinat);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $point->mitra_turunan_id,
                    'name' => $point->nama_point ?: 'Unnamed Point',
                    'latitude' => floatval(trim($coords[0] ?? 0)),
                    'longitude' => floatval(trim($coords[1] ?? 0)),
                    'coordinates' => $point->koordinat,
                    'description' => $point->deskripsi,
                    'type_point' => $point->type_point,
                    'has_file' => !is_null($point->nama_file),
                    'file_name' => $point->nama_file,
                    'file_url' => $point->nama_file ? asset('storage/' . $point->nama_file) : null,
                    'created_at' => $point->created_at->toISOString(),
                    'updated_at' => $point->updated_at->toISOString(),
                    'mitra' => [
                        'id' => $point->mitra->mitra_id ?? '',
                        'name' => $point->mitra->nama_pt ?? 'Unknown Mitra',
                        'color' => $point->mitra->warna_pt ?? '#6B7280',
                        'icon_url' => $point->mitra->icon_pt ? asset('storage/' . $point->mitra->icon_pt) : null
                    ]
                ],
                'cached' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading point detail: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading point detail: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get points by mitra with caching
     */
    public function getPointsByMitra($mitraId)
    {
        try {
            $cacheKey = 'map_points_mitra_' . $mitraId;

            $points = Cache::remember($cacheKey, 900, function() use ($mitraId) {
                return MitraTurunan::with('mitra')
                    ->where('mitra_id', $mitraId)
                    ->whereNotNull('koordinat')
                    ->where('koordinat', '!=', '')
                    ->where('koordinat', 'like', '%,%')
                    ->get();
            });

            $transformedPoints = $points->map(function ($point) {
                $coords = explode(',', $point->koordinat);
                return [
                    'id' => $point->mitra_turunan_id,
                    'name' => $point->nama_point ?: 'Unnamed Point',
                    'latitude' => floatval(trim($coords[0] ?? 0)),
                    'longitude' => floatval(trim($coords[1] ?? 0)),
                    'coordinates' => $point->koordinat,
                    'description' => $point->deskripsi,
                    'mitra' => [
                        'id' => $point->mitra->mitra_id ?? '',
                        'name' => $point->mitra->nama_pt ?? 'Unknown Mitra',
                        'color' => $point->mitra->warna_pt ?? '#6B7280'
                    ]
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $transformedPoints,
                'total' => $transformedPoints->count(),
                'mitra_id' => $mitraId,
                'cached' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading points by mitra: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading points by mitra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get geographic bounds for all points
     */
    public function getBounds(Request $request)
    {
        try {
            $mitraId = $request->get('mitra_id', '');
            $cacheKey = 'map_bounds_' . ($mitraId ?: 'all');

            $bounds = Cache::remember($cacheKey, 1800, function() use ($mitraId) {
                $query = MitraTurunan::select('koordinat')
                    ->whereNotNull('koordinat')
                    ->where('koordinat', '!=', '')
                    ->where('koordinat', 'like', '%,%');

                if (!empty($mitraId)) {
                    $query->where('mitra_id', $mitraId);
                }

                $coordinates = $query->pluck('koordinat')
                    ->map(function($coord) {
                        if (empty($coord) || !str_contains($coord, ',')) return null;
                        $coords = explode(',', $coord);
                        $lat = floatval(trim($coords[0] ?? 0));
                        $lng = floatval(trim($coords[1] ?? 0));

                        if ($lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180 && $lat != 0 && $lng != 0) {
                            return ['lat' => $lat, 'lng' => $lng];
                        }
                        return null;
                    })
                    ->filter();

                if ($coordinates->count() === 0) {
                    return null;
                }

                return [
                    'north' => $coordinates->max('lat'),
                    'south' => $coordinates->min('lat'),
                    'east' => $coordinates->max('lng'),
                    'west' => $coordinates->min('lng'),
                    'center' => [
                        'lat' => $coordinates->avg('lat'),
                        'lng' => $coordinates->avg('lng')
                    ]
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $bounds,
                'cached' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting bounds: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error getting bounds: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear map cache
     */
    public function clearCache(Request $request)
    {
        try {
            $cacheType = $request->get('type', 'all');

            switch ($cacheType) {
                case 'statistics':
                    Cache::forget('map_statistics');
                    break;

                case 'mitras':
                    Cache::forget('map_mitras_list');
                    break;

                case 'points':
                    // Clear semua cache yang dimulai dengan map_points_
                    $this->clearCacheByPattern('map_points_*');
                    break;

                case 'search':
                    $this->clearCacheByPattern('map_search_*');
                    break;

                case 'all':
                default:
                    // Clear semua cache map
                    Cache::forget('map_statistics');
                    Cache::forget('map_mitras_list');
                    $this->clearCacheByPattern('map_*');
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => 'Cache cleared successfully',
                'type' => $cacheType
            ]);

        } catch (\Exception $e) {
            Log::error('Error clearing cache: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error clearing cache: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get cache information
     */
    public function getCacheInfo()
    {
        try {
            $cacheInfo = [
                'statistics_cached' => Cache::has('map_statistics'),
                'mitras_cached' => Cache::has('map_mitras_list'),
                'cache_driver' => config('cache.default'),
                'cache_prefix' => config('cache.prefix'),
            ];

            return response()->json([
                'success' => true,
                'data' => $cacheInfo
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting cache info: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refresh specific cache
     */
    public function refreshCache(Request $request)
    {
        try {
            $type = $request->get('type', 'statistics');

            switch ($type) {
                case 'statistics':
                    Cache::forget('map_statistics');
                    // Regenerate cache
                    $this->getStatistics($request);
                    break;

                case 'mitras':
                    Cache::forget('map_mitras_list');
                    // Will be regenerated on next request
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => ucfirst($type) . ' cache refreshed successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error refreshing cache: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper: Get point limit based on zoom level
     */
    private function getPointLimitByZoom($zoomLevel)
    {
        if ($zoomLevel >= 15) return 5000;      // High detail
        if ($zoomLevel >= 12) return 3000;      // Medium detail
        if ($zoomLevel >= 9) return 1500;       // Low detail
        if ($zoomLevel >= 6) return 800;        // Very low detail
        return 500;                             // Minimal detail
    }

    /**
     * Helper: Get cluster size based on zoom level
     */
    private function getClusterSize($zoomLevel)
    {
        if ($zoomLevel >= 15) return 0.0001;    // Very fine clustering
        if ($zoomLevel >= 12) return 0.001;     // Fine clustering
        if ($zoomLevel >= 9) return 0.01;       // Medium clustering
        if ($zoomLevel >= 6) return 0.1;        // Coarse clustering
        return 1;                               // Very coarse clustering
    }

    /**
     * Helper: Clear cache by pattern (for file cache driver)
     */
    private function clearCacheByPattern($pattern)
    {
        // Untuk file cache driver, kita perlu implementasi manual
        if (config('cache.default') === 'file') {
            $cacheDir = storage_path('framework/cache/data');
            $files = glob($cacheDir . '/*');

            foreach ($files as $file) {
                if (is_file($file)) {
                    $content = file_get_contents($file);
                    // Check if cache key matches pattern
                    if (strpos($content, str_replace('*', '', $pattern)) !== false) {
                        unlink($file);
                    }
                }
            }
        }
        // Untuk cache driver lain, implementasi bisa berbeda
    }
}
