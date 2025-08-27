<?php

namespace App\Http\Controllers;

use App\Models\Mitra;
use App\Models\MitraTurunan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CoverageController extends Controller
{
    /**
     * Display coverage analysis page
     */
    public function index(Request $request)
    {
        return view('coverage.indexCoverage');
    }

    /**
     * Proxy route requests to OSRM server
     */
    public function route($coordinates, Request $request)
    {
        try {
            // Ambil semua parameter query (?overview, ?steps, dst)
            $query = $request->query();

            // Kirim langsung ke OSRM
            $osrmUrl = "http://192.168.192.5:5000/route/v1/driving/{$coordinates}";

            $response = Http::timeout(30)->get($osrmUrl, $query);

            if ($response->failed()) {
                return response()->json([
                    'error' => 'Failed to get route from OSRM',
                    'status' => $response->status()
                ], 500);
            }

            return $response->json();

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'OSRM server connection failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all points for coverage analysis
     */
    public function getPoints(Request $request)
    {
        try {
            // Ambil semua data points dengan mitra (mirip seperti contoh getOdp)
            $points = MitraTurunan::with('mitra')
                ->select('mitra_turunan_id', 'mitra_id', 'nama_point', 'koordinat', 'deskripsi')
                ->whereNotNull('koordinat')
                ->get()
                ->map(function ($point) {
                    $coords = explode(',', $point->koordinat);
                    return [
                        'point_id' => $point->mitra_turunan_id,
                        'point_name' => $point->nama_point ?: 'Unnamed Point',
                        'point_location_maps' => $point->koordinat,
                        'latitude' => floatval($coords[0] ?? 0),
                        'longitude' => floatval($coords[1] ?? 0),
                        'description' => $point->deskripsi,
                        'mitra' => [
                            'mitra_id' => $point->mitra->mitra_id ?? '',
                            'nama_pt' => $point->mitra->nama_pt ?? 'Unknown Mitra',
                            'warna_pt' => $point->mitra->warna_pt ?? '#6B7280'
                        ]
                    ];
                });

            // Kembalikan data dalam format JSON (mirip seperti contoh)
            return response()->json($points, 200);

        } catch (\Exception $e) {
            // Jika terjadi error, kembalikan respons error
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Find nearest points to given coordinates
     */
    public function findNearestPoints(Request $request)
    {
        try {
            $latitude = $request->get('latitude');
            $longitude = $request->get('longitude');
            $radius = $request->get('radius', 10); // Default 10km
            $limit = $request->get('limit', 20);

            if (!$latitude || !$longitude) {
                return response()->json([
                    'error' => 'Latitude and longitude are required'
                ], 400);
            }

            // Cari points dalam radius menggunakan Haversine formula
            $points = MitraTurunan::with('mitra')
                ->selectRaw("
                    *,
                    (6371 * acos(cos(radians(?)) * cos(radians(SUBSTRING_INDEX(koordinat, ',', 1)))
                    * cos(radians(SUBSTRING_INDEX(koordinat, ',', -1)) - radians(?))
                    + sin(radians(?)) * sin(radians(SUBSTRING_INDEX(koordinat, ',', 1))))) AS distance_km
                ", [$latitude, $longitude, $latitude])
                ->having('distance_km', '<=', $radius)
                ->orderBy('distance_km', 'asc')
                ->limit($limit)
                ->get()
                ->map(function ($point) {
                    $coords = explode(',', $point->koordinat);
                    return [
                        'point_id' => $point->mitra_turunan_id,
                        'point_name' => $point->nama_point ?: 'Unnamed Point',
                        'latitude' => floatval($coords[0] ?? 0),
                        'longitude' => floatval($coords[1] ?? 0),
                        'distance_km' => round($point->distance_km, 2),
                        'mitra' => [
                            'mitra_id' => $point->mitra->mitra_id ?? '',
                            'nama_pt' => $point->mitra->nama_pt ?? 'Unknown Mitra',
                            'warna_pt' => $point->mitra->warna_pt ?? '#6B7280'
                        ]
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $points,
                'query_point' => [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'radius_km' => $radius
                ],
                'total_found' => $points->count()
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Calculate coverage area for given coordinates
     */
    public function calculateCoverage(Request $request)
    {
        try {
            $latitude = $request->get('latitude');
            $longitude = $request->get('longitude');
            $coverageRadius = $request->get('coverage_radius', 2); // Default 2km coverage

            if (!$latitude || !$longitude) {
                return response()->json([
                    'error' => 'Latitude and longitude are required'
                ], 400);
            }

            // Hitung points dalam coverage area
            $coveredPoints = MitraTurunan::with('mitra')
                ->selectRaw("
                    *,
                    (6371 * acos(cos(radians(?)) * cos(radians(SUBSTRING_INDEX(koordinat, ',', 1)))
                    * cos(radians(SUBSTRING_INDEX(koordinat, ',', -1)) - radians(?))
                    + sin(radians(?)) * sin(radians(SUBSTRING_INDEX(koordinat, ',', 1))))) AS distance_km
                ", [$latitude, $longitude, $latitude])
                ->having('distance_km', '<=', $coverageRadius)
                ->orderBy('distance_km', 'asc')
                ->get();

            // Hitung statistik coverage
            $totalPoints = MitraTurunan::count();
            $coveredCount = $coveredPoints->count();
            $coveragePercentage = $totalPoints > 0 ? ($coveredCount / $totalPoints) * 100 : 0;

            // Group by mitra
            $coverageByMitra = $coveredPoints->groupBy('mitra_id')->map(function ($points, $mitraId) {
                $mitra = $points->first()->mitra;
                return [
                    'mitra_id' => $mitraId,
                    'mitra_name' => $mitra->nama_pt ?? 'Unknown Mitra',
                    'mitra_color' => $mitra->warna_pt ?? '#6B7280',
                    'points_count' => $points->count(),
                    'points' => $points->map(function($point) {
                        $coords = explode(',', $point->koordinat);
                        return [
                            'point_id' => $point->mitra_turunan_id,
                            'point_name' => $point->nama_point,
                            'latitude' => floatval($coords[0] ?? 0),
                            'longitude' => floatval($coords[1] ?? 0),
                            'distance_km' => round($point->distance_km, 2)
                        ];
                    })
                ];
            })->values();

            return response()->json([
                'success' => true,
                'coverage_center' => [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'coverage_radius_km' => $coverageRadius
                ],
                'statistics' => [
                    'total_points' => $totalPoints,
                    'covered_points' => $coveredCount,
                    'coverage_percentage' => round($coveragePercentage, 2)
                ],
                'coverage_by_mitra' => $coverageByMitra,
                'covered_points' => $coveredPoints->map(function ($point) {
                    $coords = explode(',', $point->koordinat);
                    return [
                        'point_id' => $point->mitra_turunan_id,
                        'point_name' => $point->nama_point ?: 'Unnamed Point',
                        'latitude' => floatval($coords[0] ?? 0),
                        'longitude' => floatval($coords[1] ?? 0),
                        'distance_km' => round($point->distance_km, 2),
                        'mitra' => [
                            'mitra_id' => $point->mitra->mitra_id ?? '',
                            'nama_pt' => $point->mitra->nama_pt ?? 'Unknown Mitra',
                            'warna_pt' => $point->mitra->warna_pt ?? '#6B7280'
                        ]
                    ];
                })
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Analyze coverage gap between two points
     */
    public function analyzeCoverageGap(Request $request)
    {
        try {
            $pointA = $request->get('point_a'); // [lat, lng]
            $pointB = $request->get('point_b'); // [lat, lng]
            $searchRadius = $request->get('search_radius', 5); // Default 5km

            if (!$pointA || !$pointB || count($pointA) != 2 || count($pointB) != 2) {
                return response()->json([
                    'error' => 'Both point_a and point_b are required as [latitude, longitude] arrays'
                ], 400);
            }

            $latA = $pointA[0];
            $lngA = $pointA[1];
            $latB = $pointB[0];
            $lngB = $pointB[1];

            // Hitung jarak antara titik A dan B
            $distance = $this->calculateDistance($latA, $lngA, $latB, $lngB);

            // Cari points di sekitar titik A
            $pointsAroundA = MitraTurunan::with('mitra')
                ->selectRaw("
                    *,
                    (6371 * acos(cos(radians(?)) * cos(radians(SUBSTRING_INDEX(koordinat, ',', 1)))
                    * cos(radians(SUBSTRING_INDEX(koordinat, ',', -1)) - radians(?))
                    + sin(radians(?)) * sin(radians(SUBSTRING_INDEX(koordinat, ',', 1))))) AS distance_km
                ", [$latA, $lngA, $latA])
                ->having('distance_km', '<=', $searchRadius)
                ->orderBy('distance_km', 'asc')
                ->get();

            // Cari points di sekitar titik B
            $pointsAroundB = MitraTurunan::with('mitra')
                ->selectRaw("
                    *,
                    (6371 * acos(cos(radians(?)) * cos(radians(SUBSTRING_INDEX(koordinat, ',', 1)))
                    * cos(radians(SUBSTRING_INDEX(koordinat, ',', -1)) - radians(?))
                    + sin(radians(?)) * sin(radians(SUBSTRING_INDEX(koordinat, ',', 1))))) AS distance_km
                ", [$latB, $lngB, $latB])
                ->having('distance_km', '<=', $searchRadius)
                ->orderBy('distance_km', 'asc')
                ->get();

            // Cari titik tengah untuk analisis gap
            $midLat = ($latA + $latB) / 2;
            $midLng = ($lngA + $lngB) / 2;

            $pointsInMiddle = MitraTurunan::with('mitra')
                ->selectRaw("
                    *,
                    (6371 * acos(cos(radians(?)) * cos(radians(SUBSTRING_INDEX(koordinat, ',', 1)))
                    * cos(radians(SUBSTRING_INDEX(koordinat, ',', -1)) - radians(?))
                    + sin(radians(?)) * sin(radians(SUBSTRING_INDEX(koordinat, ',', 1))))) AS distance_km
                ", [$midLat, $midLng, $midLat])
                ->having('distance_km', '<=', $distance / 4) // 1/4 dari jarak A-B
                ->orderBy('distance_km', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'analysis' => [
                    'point_a' => ['latitude' => $latA, 'longitude' => $lngA],
                    'point_b' => ['latitude' => $latB, 'longitude' => $lngB],
                    'distance_km' => round($distance, 2),
                    'midpoint' => ['latitude' => $midLat, 'longitude' => $midLng],
                    'search_radius_km' => $searchRadius
                ],
                'coverage_summary' => [
                    'points_around_a' => $pointsAroundA->count(),
                    'points_around_b' => $pointsAroundB->count(),
                    'points_in_middle' => $pointsInMiddle->count(),
                    'has_coverage_gap' => $pointsInMiddle->count() == 0 && $distance > 4
                ],
                'points_around_a' => $this->formatPointsForResponse($pointsAroundA),
                'points_around_b' => $this->formatPointsForResponse($pointsAroundB),
                'points_in_middle' => $this->formatPointsForResponse($pointsInMiddle)
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Calculate distance between two coordinates using Haversine formula
     */
    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat/2) * sin($dLat/2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLng/2) * sin($dLng/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $earthRadius * $c;
    }

    /**
     * Format points collection for API response
     */
    private function formatPointsForResponse($points)
    {
        return $points->map(function ($point) {
            $coords = explode(',', $point->koordinat);
            return [
                'point_id' => $point->mitra_turunan_id,
                'point_name' => $point->nama_point ?: 'Unnamed Point',
                'latitude' => floatval($coords[0] ?? 0),
                'longitude' => floatval($coords[1] ?? 0),
                'distance_km' => round($point->distance_km ?? 0, 2),
                'mitra' => [
                    'mitra_id' => $point->mitra->mitra_id ?? '',
                    'nama_pt' => $point->mitra->nama_pt ?? 'Unknown Mitra',
                    'warna_pt' => $point->mitra->warna_pt ?? '#6B7280'
                ]
            ];
        });
    }
}
