<?php

namespace App\Http\Controllers;

use App\Models\Mitra;
use App\Models\MitraTurunan;
use Illuminate\Http\Request;

class MapController extends Controller
{
   
    public function index(Request $request)
    {
        // Ambil semua data points dengan mitra
        $points = MitraTurunan::with('mitra')
            ->select('mitra_turunan_id', 'mitra_id', 'nama_point', 'koordinat', 'deskripsi')
            ->get()
            ->map(function ($point) {
                $coords = explode(',', $point->koordinat);
                return [
                    'id' => $point->mitra_turunan_id,
                    'name' => $point->nama_point ?: 'Unnamed Point',
                    'latitude' => floatval($coords[0] ?? 0),
                    'longitude' => floatval($coords[1] ?? 0),
                    'description' => $point->deskripsi,
                    'mitra' => [
                        'id' => $point->mitra->mitra_id ?? '',
                        'name' => $point->mitra->nama_pt ?? 'Unknown Mitra',
                        'color' => $point->mitra->warna_pt ?? '#6B7280',
                        'icon' => $point->mitra->icon_pt ?? null
                    ]
                ];
            });

        // Ambil daftar mitra untuk layer control
        $mitras = Mitra::select('mitra_id', 'nama_pt', 'warna_pt', 'icon_pt')
            ->withCount('mitraTurunans')
            ->orderBy('nama_pt')
            ->get();

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
     * Get all points as JSON (for AJAX/API calls)
     */
    public function getPoints(Request $request)
    {
        try {
            $query = MitraTurunan::with('mitra');

            // Filter by mitra if specified
            if ($request->has('mitra_id') && !empty($request->mitra_id)) {
                $query->where('mitra_id', $request->mitra_id);
            }

            // Filter by bounds (viewport optimization)
            if ($request->has('bounds')) {
                $bounds = $request->bounds;
                $query->whereRaw("
                    CAST(SUBSTRING_INDEX(koordinat, ',', 1) AS DECIMAL(10,8)) BETWEEN ? AND ? AND
                    CAST(SUBSTRING_INDEX(koordinat, ',', -1) AS DECIMAL(11,8)) BETWEEN ? AND ?
                ", [
                    $bounds['south'], $bounds['north'],
                    $bounds['west'], $bounds['east']
                ]);
            }

            $points = $query->get()->map(function ($point) {
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

            return response()->json([
                'success' => true,
                'data' => $points,
                'total' => $points->count()
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

            // Geographic bounds (rough calculation)
            $coordinates = MitraTurunan::pluck('koordinat')->map(function($coord) {
                $coords = explode(',', $coord);
                return [
                    'lat' => floatval($coords[0] ?? 0),
                    'lng' => floatval($coords[1] ?? 0)
                ];
            })->filter(function($coord) {
                return $coord['lat'] != 0 && $coord['lng'] != 0;
            });

            $bounds = null;
            if ($coordinates->count() > 0) {
                $bounds = [
                    'north' => $coordinates->max('lat'),
                    'south' => $coordinates->min('lat'),
                    'east' => $coordinates->max('lng'),
                    'west' => $coordinates->min('lng'),
                    'center' => [
                        'lat' => $coordinates->avg('lat'),
                        'lng' => $coordinates->avg('lng')
                    ]
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'total_points' => $totalPoints,
                    'total_mitras' => $totalMitras,
                    'points_by_mitra' => $pointsByMitra,
                    'geographic_bounds' => $bounds
                ]
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

            if (empty($search)) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }

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

            return response()->json([
                'success' => true,
                'data' => $points,
                'total' => $points->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error searching points: ' . $e->getMessage()
            ], 500);
        }
    }
}
