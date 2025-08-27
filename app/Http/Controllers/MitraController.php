<?php

namespace App\Http\Controllers;

use App\Models\Mitra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MitraController extends Controller
{
    /**
     * Display a listing of mitras with AJAX support
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Mitra::withCount([
                'mitraTurunans as total_points',
                'mitraTurunans as active_points' => function($q) {
                    // Jika nanti ada status di mitra_turunans
                    // $q->where('status', 'active');
                }
            ]);

            // Search functionality
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nama_pt', 'like', "%{$search}%")
                      ->orWhere('mitra_id', 'like', "%{$search}%");
                });
            }

            // Filter by warna (if needed)
            if ($request->has('warna') && !empty($request->warna)) {
                $query->where('warna_pt', $request->warna);
            }

            // Sorting
            $sortField = $request->get('sort_field', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            $query->orderBy($sortField, $sortDirection);

            // Pagination
            $perPage = $request->get('per_page', 10);
            $mitras = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $mitras->items(),
                'pagination' => [
                    'current_page' => $mitras->currentPage(),
                    'last_page' => $mitras->lastPage(),
                    'per_page' => $mitras->perPage(),
                    'total' => $mitras->total(),
                    'from' => $mitras->firstItem(),
                    'to' => $mitras->lastItem(),
                ]
            ]);
        }

        return view('mitras.indexMitra');
    }

    /**
     * Store a newly created mitra via AJAX
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_pt' => 'required|string|max:255',
            'warna_pt' => 'nullable|string|regex:/^#([a-fA-F0-9]{6})$/', // Hex color validation
            'icon_pt' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $mitraData = [
                'nama_pt' => $request->nama_pt,
                'warna_pt' => $request->warna_pt ?: '#3B82F6', // Default blue
            ];

            // Handle icon upload
            if ($request->hasFile('icon_pt')) {
                $file = $request->file('icon_pt');
                $path = $file->store('mitra_icons', 'public');
                $mitraData['icon_pt'] = $path;
            }

            $mitra = Mitra::create($mitraData);

            return response()->json([
                'success' => true,
                'message' => 'Mitra berhasil dibuat',
                'data' => $mitra->load('mitraTurunans')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat mitra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified mitra via AJAX
     */
    public function show(Request $request, $mitraId)
    {
        try {
            $mitra = Mitra::with(['mitraTurunans' => function($query) {
                $query->orderBy('created_at', 'desc');
            }])->where('mitra_id', $mitraId)->firstOrFail();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $mitra
                ]);
            }

            return view('admin.mitras.show', compact('mitra'));

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mitra tidak ditemukan'
                ], 404);
            }

            return redirect()->route('mitras.index')->with('error', 'Mitra tidak ditemukan');
        }
    }

    /**
     * Update the specified mitra via AJAX
     */
    public function update(Request $request, $mitraId)
    {
        try {
            $mitra = Mitra::where('mitra_id', $mitraId)->firstOrFail();

            $validator = Validator::make($request->all(), [
                'nama_pt' => 'required|string|max:255',
                'warna_pt' => 'nullable|string|regex:/^#([a-fA-F0-9]{6})$/',
                'icon_pt' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $mitraData = [
                'nama_pt' => $request->nama_pt,
                'warna_pt' => $request->warna_pt ?: '#3B82F6',
            ];

            // Handle icon upload
            if ($request->hasFile('icon_pt')) {
                // Delete old icon
                if ($mitra->icon_pt && Storage::disk('public')->exists($mitra->icon_pt)) {
                    Storage::disk('public')->delete($mitra->icon_pt);
                }

                $file = $request->file('icon_pt');
                $path = $file->store('mitra_icons', 'public');
                $mitraData['icon_pt'] = $path;
            }

            $mitra->update($mitraData);

            return response()->json([
                'success' => true,
                'message' => 'Mitra berhasil diperbarui',
                'data' => $mitra->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui mitra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified mitra via AJAX
     */
    public function destroy(Request $request, $mitraId)
    {
        try {
            $mitra = Mitra::where('mitra_id', $mitraId)->firstOrFail();

            // Check if mitra has related points
            $pointsCount = $mitra->mitraTurunans()->count();
            if ($pointsCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Tidak dapat menghapus mitra yang memiliki {$pointsCount} titik/point"
                ], 403);
            }

            // Delete icon if exists
            if ($mitra->icon_pt && Storage::disk('public')->exists($mitra->icon_pt)) {
                Storage::disk('public')->delete($mitra->icon_pt);
            }

            $mitra->delete();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Mitra berhasil dihapus'
                ]);
            }

            return redirect()->route('mitras.index')->with('success', 'Mitra berhasil dihapus');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat menghapus mitra: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('mitras.index')->with('error', 'Terjadi kesalahan saat menghapus mitra');
        }
    }

    /**
     * Bulk actions for mitras via AJAX
     */
    public function bulkAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:delete',
            'mitra_ids' => 'required|array|min:1',
            'mitra_ids.*' => 'exists:mitras,mitra_id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $action = $request->action;
            $mitraIds = $request->mitra_ids;
            $count = 0;
            $skipped = 0;

            switch ($action) {
                case 'delete':
                    foreach ($mitraIds as $mitraId) {
                        $mitra = Mitra::where('mitra_id', $mitraId)->first();
                        if ($mitra) {
                            // Check if has points
                            if ($mitra->mitraTurunans()->count() > 0) {
                                $skipped++;
                                continue;
                            }

                            // Delete icon
                            if ($mitra->icon_pt && Storage::disk('public')->exists($mitra->icon_pt)) {
                                Storage::disk('public')->delete($mitra->icon_pt);
                            }

                            $mitra->delete();
                            $count++;
                        }
                    }
                    $message = "Berhasil menghapus {$count} mitra";
                    if ($skipped > 0) {
                        $message .= " ({$skipped} mitra dilewati karena masih memiliki points)";
                    }
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'count' => $count,
                'skipped' => $skipped
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat melakukan aksi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export mitras data (CSV) via AJAX
     */
    public function export(Request $request)
    {
        try {
            $format = $request->get('format', 'csv');
            $query = Mitra::withCount('mitraTurunans as total_points');

            // Apply same filters as index
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nama_pt', 'like', "%{$search}%")
                      ->orWhere('mitra_id', 'like', "%{$search}%");
                });
            }

            if ($request->has('warna') && !empty($request->warna)) {
                $query->where('warna_pt', $request->warna);
            }

            $mitras = $query->get();
            $filename = 'mitras_export_' . date('Y-m-d_H-i-s') . '.' . $format;

            if ($format === 'csv') {
                $headers = [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                ];

                $callback = function() use ($mitras) {
                    $file = fopen('php://output', 'w');

                    // CSV headers
                    fputcsv($file, [
                        'Mitra ID',
                        'Nama PT',
                        'Warna',
                        'Total Points',
                        'Has Icon',
                        'Created At'
                    ]);

                    // CSV data
                    foreach ($mitras as $mitra) {
                        fputcsv($file, [
                            $mitra->mitra_id,
                            $mitra->nama_pt,
                            $mitra->warna_pt,
                            $mitra->total_points,
                            $mitra->icon_pt ? 'Yes' : 'No',
                            $mitra->created_at->format('Y-m-d H:i:s')
                        ]);
                    }

                    fclose($file);
                };

                return response()->stream($callback, 200, $headers);
            }

            return response()->json([
                'success' => true,
                'message' => 'Export berhasil diproses',
                'data' => $mitras,
                'count' => $mitras->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat export: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get mitra statistics via AJAX
     */
    public function statistics(Request $request)
    {
        try {
            $stats = [
                'total_mitras' => Mitra::count(),
                'mitras_with_points' => Mitra::has('mitraTurunans')->count(),
                'mitras_without_points' => Mitra::doesntHave('mitraTurunans')->count(),
                'total_points' => \App\Models\MitraTurunan::count(),
                'recent_mitras' => Mitra::where('created_at', '>=', now()->subDays(30))->count(),
            ];

            // Popular colors
            $popularColors = Mitra::select('warna_pt')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('warna_pt')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->get()
                ->map(function($item) {
                    return [
                        'color' => $item->warna_pt,
                        'count' => $item->count
                    ];
                });

            // Mitras with most points
            $topMitras = Mitra::withCount('mitraTurunans as points_count')
                ->orderBy('points_count', 'desc')
                ->limit(5)
                ->get()
                ->map(function($mitra) {
                    return [
                        'mitra_id' => $mitra->mitra_id,
                        'nama_pt' => $mitra->nama_pt,
                        'points_count' => $mitra->points_count,
                        'warna_pt' => $mitra->warna_pt
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'statistics' => $stats,
                    'popular_colors' => $popularColors,
                    'top_mitras' => $topMitras
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil statistik: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available colors for filter
     */
    public function getColors(Request $request)
    {
        try {
            $colors = Mitra::select('warna_pt')
                ->distinct()
                ->whereNotNull('warna_pt')
                ->orderBy('warna_pt')
                ->pluck('warna_pt');

            return response()->json([
                'success' => true,
                'data' => $colors
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil daftar warna'
            ], 500);
        }
    }

    /**
     * Duplicate mitra
     */
    public function duplicate(Request $request, $mitraId)
    {
        try {
            $originalMitra = Mitra::where('mitra_id', $mitraId)->firstOrFail();

            $newMitraData = [
                'nama_pt' => $originalMitra->nama_pt . ' (Copy)',
                'warna_pt' => $originalMitra->warna_pt,
            ];

            // Copy icon if exists
            if ($originalMitra->icon_pt && Storage::disk('public')->exists($originalMitra->icon_pt)) {
                $originalPath = $originalMitra->icon_pt;
                $extension = pathinfo($originalPath, PATHINFO_EXTENSION);
                $newPath = 'mitra_icons/copy_' . time() . '.' . $extension;

                Storage::disk('public')->copy($originalPath, $newPath);
                $newMitraData['icon_pt'] = $newPath;
            }

            $newMitra = Mitra::create($newMitraData);

            return response()->json([
                'success' => true,
                'message' => 'Mitra berhasil diduplikasi',
                'data' => $newMitra
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menduplikasi mitra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get mitra points summary
     */
    public function getPointsSummary(Request $request, $mitraId)
    {
        try {
            $mitra = Mitra::with(['mitraTurunans' => function($query) {
                $query->orderBy('created_at', 'desc');
            }])->where('mitra_id', $mitraId)->firstOrFail();

            $summary = [
                'total_points' => $mitra->mitraTurunans->count(),
                'points_with_files' => $mitra->mitraTurunans->whereNotNull('nama_file')->count(),
                'points_without_files' => $mitra->mitraTurunans->whereNull('nama_file')->count(),
                'recent_points' => $mitra->mitraTurunans->where('created_at', '>=', now()->subDays(7))->count(),
                'points' => $mitra->mitraTurunans->take(10) // Latest 10 points
            ];

            return response()->json([
                'success' => true,
                'data' => $summary
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil ringkasan points'
            ], 500);
        }
    }
}
