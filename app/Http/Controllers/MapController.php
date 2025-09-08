<?php

namespace App\Http\Controllers;

use App\Models\Mitra;
use App\Models\MitraTurunan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MapController extends Controller
{
    /**
     * Display all points on map - SIMPLE VERSION
     */
    public function index(Request $request)
    {
        // Cache semua points untuk 30 menit
        $points = Cache::remember('all_map_points', 1800, function() {
            return MitraTurunan::with('mitra')
                ->select('mitra_turunan_id', 'mitra_id', 'nama_point', 'koordinat', 'deskripsi')
                ->get()
                ->map(function ($point) {
                    $coords = explode(',', $point->koordinat);
                    return [
                        'id' => $point->mitra_turunan_id,
                        'name' => $point->nama_point ?: 'Unnamed Point',
                        'latitude' => floatval($coords[0] ?? 0),
                        'longitude' => floatval($coords[1] ?? 0),
                        'coordinates' => $point->koordinat,
                        'description' => $point->deskripsi,
                        'mitra' => [
                            'id' => $point->mitra->mitra_id ?? '',
                            'name' => $point->mitra->nama_pt ?? 'Unknown Mitra',
                            'color' => $point->mitra->warna_pt ?? '#6B7280',
                            'icon' => $point->mitra->icon_pt ?? null
                        ]
                    ];
                });
        });

        // Cache daftar mitra untuk 1 jam
        $mitras = Cache::remember('map_mitras', 3600, function() {
            return Mitra::select('mitra_id', 'nama_pt', 'warna_pt', 'icon_pt')
                ->withCount('mitraTurunans')
                ->orderBy('nama_pt')
                ->get();
        });

        // Statistics
        $stats = [
            'total_points' => $points->count(),
            'total_mitras' => $mitras->count(),
            'points_by_mitra' => $mitras->map(function($mitra) {
                return [
                    'mitra_id' => $mitra->mitra_id,
                    'nama_pt' => $mitra->nama_pt,
                    'warna_pt' => $mitra->warna_pt,
                    'points_count' => $mitra->mitra_turunans_count
                ];
            })
        ];

        return view('maps.indexMaps', compact('points', 'mitras', 'stats'));
    }

    /**
     * Get all points as JSON - SIMPLE VERSION
     */
    public function getPoints(Request $request)
    {
        try {
            // Gunakan cache yang sama dengan index
            $points = Cache::remember('all_map_points', 1800, function() {
                return MitraTurunan::with('mitra')
                    ->select('mitra_turunan_id', 'mitra_id', 'nama_point', 'koordinat', 'deskripsi', 'nama_file', 'created_at')
                    ->get()
                    ->map(function ($point) {
                        $coords = explode(',', $point->koordinat);
                        return [
                            'id' => $point->mitra_turunan_id,
                            'name' => $point->nama_point ?: 'Unnamed Point',
                            'latitude' => floatval($coords[0] ?? 0),
                            'longitude' => floatval($coords[1] ?? 0),
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
            });

            // Filter by mitra if specified
            if ($request->has('mitra_id') && !empty($request->mitra_id)) {
                $points = $points->filter(function($point) use ($request) {
                    return $point['mitra']['id'] === $request->mitra_id;
                });
            }

            return response()->json([
                'success' => true,
                'data' => $points->values(), // Reset array keys
                'total' => $points->count(),
                'cached' => true
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading points: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get map statistics
     */
    public function getStatistics(Request $request)
    {
        try {
            $stats = Cache::remember('map_statistics', 1800, function() {
                $totalPoints = MitraTurunan::count();
                $totalMitras = Mitra::count();

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
            return response()->json([
                'success' => false,
                'message' => 'Error loading statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search points by name or description
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

            // Cache search selama 5 menit
            $cacheKey = 'map_search_' . md5(strtolower($search));

            $results = Cache::remember($cacheKey, 300, function() use ($search) {
                $points = MitraTurunan::with('mitra')
                    ->where(function($query) use ($search) {
                        $query->where('nama_point', 'like', "%{$search}%")
                              ->orWhere('deskripsi', 'like', "%{$search}%")
                              ->orWhere('koordinat', 'like', "%{$search}%");
                    })
                    ->limit(20)
                    ->get()
                    ->map(function ($point) {
                        $coords = explode(',', $point->koordinat);
                        return [
                            'id' => $point->mitra_turunan_id,
                            'name' => $point->nama_point ?: 'Unnamed Point',
                            'latitude' => floatval($coords[0] ?? 0),
                            'longitude' => floatval($coords[1] ?? 0),
                            'coordinates' => $point->koordinat,
                            'description' => $point->deskripsi,
                            'mitra' => [
                                'id' => $point->mitra->mitra_id ?? '',
                                'name' => $point->mitra->nama_pt ?? 'Unknown Mitra',
                                'color' => $point->mitra->warna_pt ?? '#6B7280'
                            ]
                        ];
                    });

                // Juga search mitra
                $mitras = Mitra::where('nama_pt', 'like', "%{$search}%")
                    ->withCount('mitraTurunans')
                    ->limit(5)
                    ->get()
                    ->map(function($mitra) {
                        return [
                            'id' => $mitra->mitra_id,
                            'name' => $mitra->nama_pt,
                            'color' => $mitra->warna_pt,
                            'points_count' => $mitra->mitra_turunans_count,
                            'type' => 'mitra'
                        ];
                    });

                return $points->concat($mitras);
            });

            return response()->json([
                'success' => true,
                'data' => $results,
                'total' => $results->count(),
                'cached' => true
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error searching points: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear cache
     */
    public function clearCache()
    {
        Cache::forget('all_map_points');
        Cache::forget('map_mitras');
        Cache::forget('map_statistics');

        // Clear search cache
        $cacheDir = storage_path('framework/cache/data');
        if (is_dir($cacheDir)) {
            $files = glob($cacheDir . '/*');
            foreach ($files as $file) {
                if (is_file($file) && strpos(file_get_contents($file), 'map_search_') !== false) {
                    unlink($file);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Cache cleared successfully'
        ]);
    }
}
