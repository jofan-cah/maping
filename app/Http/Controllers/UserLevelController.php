<?php

namespace App\Http\Controllers;

use App\Models\UserLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserLevelController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = UserLevel::withTrashed()->withCount([
                'users as total_users',
                'users as active_users' => function ($q) {
                    $q->where('is_active', true);
                }
            ]);

            // Search functionality
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('user_level_id', 'like', "%{$search}%");
                });
            }

            // Filter by status
            if ($request->has('status') && $request->status !== '') {
                if ($request->status === 'active') {
                    $query->where('is_active', true)->whereNull('deleted_at');
                } elseif ($request->status === 'inactive') {
                    $query->where('is_active', false)->whereNull('deleted_at');
                } elseif ($request->status === 'deleted') {
                    $query->onlyTrashed();
                }
            }

            // Filter by system/non-system
            if ($request->has('system') && $request->system !== '') {
                $query->where('is_system', $request->system === '1');
            }

            // Sorting
            $sortField = $request->get('sort_field', 'priority');
            $sortDirection = $request->get('sort_direction', 'desc');
            $query->orderBy($sortField, $sortDirection);

            // Pagination
            $perPage = $request->get('per_page', 10);
            $userLevels = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $userLevels->items(),
                'pagination' => [
                    'current_page' => $userLevels->currentPage(),
                    'last_page' => $userLevels->lastPage(),
                    'per_page' => $userLevels->perPage(),
                    'total' => $userLevels->total(),
                    'from' => $userLevels->firstItem(),
                    'to' => $userLevels->lastItem(),
                ]
            ]);
        }

        return view('levels.indexUserLevel');
    }

    /**
     * Store a newly created user level via AJAX
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:user_levels',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
            'priority' => 'required|integer|min:0|max:100',
            'is_active' => 'boolean',
            'is_system' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $userLevelData = [
                'name' => $request->name,
                'description' => $request->description,
                'permissions' => $request->permissions ?: [],
                'priority' => $request->priority,
                'is_active' => $request->boolean('is_active', true),
                'is_system' => $request->boolean('is_system', false),
            ];

            $userLevel = UserLevel::create($userLevelData);

            return response()->json([
                'success' => true,
                'message' => 'User Level berhasil dibuat',
                'data' => $userLevel
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat user level: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified user level via AJAX
     */
    public function show(Request $request, $userLevelId)
    {
        try {
            $userLevel = UserLevel::withTrashed()
                ->withCount(['users as total_users', 'users as active_users' => function ($q) {
                    $q->where('is_active', true);
                }])
                ->where('user_level_id', $userLevelId)
                ->firstOrFail();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $userLevel
                ]);
            }

            return view('admin.user-levels.show', compact('userLevel'));
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User Level tidak ditemukan'
                ], 404);
            }

            return redirect()->route('user-levels.index')->with('error', 'User Level tidak ditemukan');
        }
    }

    /**
     * Update the specified user level via AJAX
     */
    public function update(Request $request, $userLevelId)
    {
        try {
            $userLevel = UserLevel::withTrashed()->where('user_level_id', $userLevelId)->firstOrFail();

            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('user_levels')->ignore($userLevel->user_level_id, 'user_level_id')
                ],
                'description' => 'nullable|string',
                'permissions' => 'nullable|array',
                'permissions.*' => 'string',
                'priority' => 'required|integer|min:0|max:100',
                'is_active' => 'boolean',
                'is_system' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Prevent modification of system levels
            if ($userLevel->is_system && $request->boolean('is_system') === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat mengubah status system level'
                ], 403);
            }

            $userLevelData = [
                'name' => $request->name,
                'description' => $request->description,
                'permissions' => $request->permissions ?: [],
                'priority' => $request->priority,
                'is_active' => $request->boolean('is_active', true),
            ];

            // Only allow is_system to be changed if current user level is not system
            if (!$userLevel->is_system) {
                $userLevelData['is_system'] = $request->boolean('is_system', false);
            }

            $userLevel->update($userLevelData);

            return response()->json([
                'success' => true,
                'message' => 'User Level berhasil diperbarui',
                'data' => $userLevel
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui user level: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Soft delete user level via AJAX
     */
    public function destroy(Request $request, $userLevelId)
    {
        try {
            $userLevel = UserLevel::where('user_level_id', $userLevelId)->firstOrFail();

            // Check if this is a system level
            if ($userLevel->is_system) {
                return response()->json([
                    'success' => false,
                    'message' => 'System level tidak dapat dihapus'
                ], 403);
            }

            // Check if there are users using this level
            $userCount = $userLevel->users()->count();
            if ($userCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Tidak dapat menghapus level yang masih digunakan oleh {$userCount} user"
                ], 403);
            }

            $userLevel->delete();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User Level berhasil dihapus'
                ]);
            }

            return redirect()->route('user-levels.index')->with('success', 'User Level berhasil dihapus');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat menghapus user level: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('user-levels.index')->with('error', 'Terjadi kesalahan saat menghapus user level');
        }
    }

    /**
     * Restore soft deleted user level via AJAX
     */
    public function restore(Request $request, $userLevelId)
    {
        try {
            $userLevel = UserLevel::onlyTrashed()->where('user_level_id', $userLevelId)->firstOrFail();
            $userLevel->restore();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User Level berhasil dipulihkan'
                ]);
            }

            return redirect()->route('user-levels.index')->with('success', 'User Level berhasil dipulihkan');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat memulihkan user level: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('user-levels.index')->with('error', 'Terjadi kesalahan saat memulihkan user level');
        }
    }

    /**
     * Force delete user level (permanent) via AJAX
     */
    public function forceDestroy(Request $request, $userLevelId)
    {
        try {
            $userLevel = UserLevel::onlyTrashed()->where('user_level_id', $userLevelId)->firstOrFail();

            // Check if this is a system level
            if ($userLevel->is_system) {
                return response()->json([
                    'success' => false,
                    'message' => 'System level tidak dapat dihapus permanen'
                ], 403);
            }

            $userLevel->forceDelete();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User Level berhasil dihapus permanen'
                ]);
            }

            return redirect()->route('user-levels.index')->with('success', 'User Level berhasil dihapus permanen');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat menghapus user level permanen: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('user-levels.index')->with('error', 'Terjadi kesalahan saat menghapus user level permanen');
        }
    }

    /**
     * Toggle user level status (active/inactive) via AJAX
     */
    public function toggleStatus(Request $request, $userLevelId)
    {
        try {
            $userLevel = UserLevel::where('user_level_id', $userLevelId)->firstOrFail();

            // Prevent deactivation of system levels
            if ($userLevel->is_system && $userLevel->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'System level tidak dapat dinonaktifkan'
                ], 403);
            }

            $userLevel->toggleStatus();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status user level berhasil diubah',
                    'data' => [
                        'is_active' => $userLevel->is_active,
                        'status_text' => $userLevel->status_text
                    ]
                ]);
            }

            return redirect()->route('user-levels.index')->with('success', 'Status user level berhasil diubah');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat mengubah status: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('user-levels.index')->with('error', 'Terjadi kesalahan saat mengubah status');
        }
    }

    /**
     * Add permission to user level via AJAX
     */
    public function addPermission(Request $request, $userLevelId)
    {
        $validator = Validator::make($request->all(), [
            'permission' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $userLevel = UserLevel::where('user_level_id', $userLevelId)->firstOrFail();
            $userLevel->addPermission($request->permission);

            return response()->json([
                'success' => true,
                'message' => 'Permission berhasil ditambahkan',
                'data' => [
                    'permissions' => $userLevel->permissions,
                    'permissions_text' => $userLevel->permissions_text
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menambahkan permission: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove permission from user level via AJAX
     */
    public function removePermission(Request $request, $userLevelId)
    {
        $validator = Validator::make($request->all(), [
            'permission' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $userLevel = UserLevel::where('user_level_id', $userLevelId)->firstOrFail();
            $userLevel->removePermission($request->permission);

            return response()->json([
                'success' => true,
                'message' => 'Permission berhasil dihapus',
                'data' => [
                    'permissions' => $userLevel->permissions,
                    'permissions_text' => $userLevel->permissions_text
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus permission: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk actions for user levels via AJAX
     */
    public function bulkAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:activate,deactivate,delete,restore,force_delete',
            'user_level_ids' => 'required|array|min:1',
            'user_level_ids.*' => 'exists:user_levels,user_level_id'
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
            $userLevelIds = $request->user_level_ids;
            $count = 0;
            $skipped = 0;

            switch ($action) {
                case 'activate':
                    // Skip system levels that are already active
                    $levels = UserLevel::whereIn('user_level_id', $userLevelIds)->get();
                    foreach ($levels as $level) {
                        if ($level->is_system && !$level->is_active) {
                            $skipped++;
                            continue;
                        }
                        $level->activate();
                        $count++;
                    }
                    $message = "Berhasil mengaktifkan {$count} user level";
                    if ($skipped > 0) {
                        $message .= " ({$skipped} system level dilewati)";
                    }
                    break;

                case 'deactivate':
                    // Skip system levels
                    $levels = UserLevel::whereIn('user_level_id', $userLevelIds)->get();
                    foreach ($levels as $level) {
                        if ($level->is_system) {
                            $skipped++;
                            continue;
                        }
                        $level->deactivate();
                        $count++;
                    }
                    $message = "Berhasil menonaktifkan {$count} user level";
                    if ($skipped > 0) {
                        $message .= " ({$skipped} system level dilewati)";
                    }
                    break;

                case 'delete':
                    $levels = UserLevel::whereIn('user_level_id', $userLevelIds)->get();
                    foreach ($levels as $level) {
                        if ($level->is_system || $level->users()->count() > 0) {
                            $skipped++;
                            continue;
                        }
                        $level->delete();
                        $count++;
                    }
                    $message = "Berhasil menghapus {$count} user level";
                    if ($skipped > 0) {
                        $message .= " ({$skipped} level dilewati karena system level atau masih digunakan)";
                    }
                    break;

                case 'restore':
                    $levels = UserLevel::onlyTrashed()->whereIn('user_level_id', $userLevelIds)->get();
                    foreach ($levels as $level) {
                        $level->restore();
                        $count++;
                    }
                    $message = "Berhasil memulihkan {$count} user level";
                    break;

                case 'force_delete':
                    $levels = UserLevel::onlyTrashed()->whereIn('user_level_id', $userLevelIds)->get();
                    foreach ($levels as $level) {
                        if ($level->is_system) {
                            $skipped++;
                            continue;
                        }
                        $level->forceDelete();
                        $count++;
                    }
                    $message = "Berhasil menghapus permanen {$count} user level";
                    if ($skipped > 0) {
                        $message .= " ({$skipped} system level dilewati)";
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
     * Get available permissions list via AJAX
     */
    public function getAvailablePermissions(Request $request)
    {
        try {
            // Define available permissions berdasarkan modul yang ada di aplikasi
            $permissions = [
                'user_management' => [
                    'users.view' => 'Lihat Daftar User',
                    'users.create' => 'Buat User Baru',
                    'users.edit' => 'Edit User',
                    'users.delete' => 'Hapus User',
                    'users.restore' => 'Pulihkan User',
                    'users.force_delete' => 'Hapus Permanen User',
                    'users.toggle_status' => 'Aktifkan/Nonaktifkan User',
                    'users.export' => 'Export Data User',
                    'users.statistics' => 'Lihat Statistik User',
                    'users.bulk_action' => 'Aksi Bulk User',
                ],
                'user_level_management' => [
                    'user_levels.view' => 'Lihat Daftar User Level',
                    'user_levels.create' => 'Buat User Level Baru',
                    'user_levels.edit' => 'Edit User Level',
                    'user_levels.delete' => 'Hapus User Level',
                    'user_levels.restore' => 'Pulihkan User Level',
                    'user_levels.force_delete' => 'Hapus Permanen User Level',
                    'user_levels.toggle_status' => 'Aktifkan/Nonaktifkan User Level',
                    'user_levels.permissions' => 'Kelola Permissions',
                    'user_levels.export' => 'Export Data User Level',
                    'user_levels.statistics' => 'Lihat Statistik User Level',
                    'user_levels.bulk_action' => 'Aksi Bulk User Level',
                    'user_levels.create_defaults' => 'Buat Default Levels',
                ],
                'mitra_management' => [
                    'mitras.view' => 'Lihat Daftar Mitra',
                    'mitras.create' => 'Buat Mitra Baru',
                    'mitras.edit' => 'Edit Mitra',
                    'mitras.delete' => 'Hapus Mitra',
                    'mitras.duplicate' => 'Duplikasi Mitra',
                    'mitras.export' => 'Export Data Mitra',
                    'mitras.statistics' => 'Lihat Statistik Mitra',
                    'mitras.bulk_action' => 'Aksi Bulk Mitra',
                    'mitras.colors' => 'Kelola Warna Mitra',
                    'mitras.points_summary' => 'Lihat Ringkasan Points Mitra',
                ],
                'points_management' => [
                    'points.view' => 'Lihat Daftar Points',
                    'points.create' => 'Buat Point Baru',
                    'points.edit' => 'Edit Point',
                    'points.delete' => 'Hapus Point',
                    'points.upload_kmz' => 'Upload File KMZ/KML',
                    'points.export' => 'Export Data Points',
                    'points.statistics' => 'Lihat Statistik Points',
                    'points.bulk_action' => 'Aksi Bulk Points',
                    'points.map_data' => 'Akses Data Map',
                    'points.update_coordinates' => 'Update Koordinat Points',
                ],
                'routing_analysis' => [
                    'routing.nearest_points' => 'Cari Points Terdekat',
                    'routing.points_in_radius' => 'Points dalam Radius',
                    'routing.calculate_route' => 'Hitung Route',
                    'routing.optimal_route' => 'Route Optimal',
                    'routing.coverage_analysis' => 'Analisis Coverage',
                    'routing.gap_analysis' => 'Analisis Gap Coverage',
                ],
                'maps_visualization' => [
                    'maps.view' => 'Lihat Maps',
                    'maps.points' => 'Akses Points di Map',
                    'maps.search' => 'Search di Map',
                    'maps.statistics' => 'Statistik Map',
                    'maps.fullscreen' => 'Mode Fullscreen Map',
                    'maps.export' => 'Export Data Map',
                ],
                'coverage_analysis' => [
                    'coverage.view' => 'Lihat Coverage Analysis',
                    'coverage.points' => 'Akses Data Points Coverage',
                    'coverage.nearest' => 'Cari Points Terdekat',
                    'coverage.calculate' => 'Hitung Coverage Area',
                    'coverage.gap_analysis' => 'Analisis Gap Coverage',
                    'coverage.route' => 'Akses Routing Coverage',
                ],
                'reports_analytics' => [
                    'reports.view' => 'Lihat Laporan',
                    'reports.create' => 'Buat Laporan',
                    'reports.export' => 'Export Laporan',
                    'reports.analytics' => 'Analytics Dashboard',
                    'reports.statistics' => 'Statistik Umum',
                    'reports.charts' => 'Grafik dan Chart',
                ],
                'system_settings' => [
                    'settings.view' => 'Lihat Pengaturan',
                    'settings.edit' => 'Edit Pengaturan',
                    'settings.system' => 'Pengaturan Sistem',
                    'settings.database' => 'Pengaturan Database',
                    'settings.backup' => 'Backup & Restore',
                    'settings.maintenance' => 'Mode Maintenance',
                ],
                'system_administration' => [
                    'system.logs' => 'Akses System Logs',
                    'system.monitoring' => 'System Monitoring',
                    'system.cache' => 'Kelola Cache',
                    'system.queue' => 'Kelola Queue',
                    'system.scheduler' => 'Kelola Scheduler',
                    'system.debug' => 'Debug Information',
                ],
                'dashboard_access' => [
                    'dashboard.view' => 'Akses Dashboard',
                    'dashboard.statistics' => 'Lihat Statistik Dashboard',
                    'dashboard.widgets' => 'Kelola Widget Dashboard',
                    'dashboard.export' => 'Export Data Dashboard',
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $permissions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil daftar permission: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Get user level statistics via AJAX
     */
    public function statistics(Request $request)
    {
        try {
            $stats = [
                'total_levels' => UserLevel::withTrashed()->count(),
                'active_levels' => UserLevel::where('is_active', true)->count(),
                'inactive_levels' => UserLevel::where('is_active', false)->count(),
                'deleted_levels' => UserLevel::onlyTrashed()->count(),
                'system_levels' => UserLevel::where('is_system', true)->count(),
                'non_system_levels' => UserLevel::where('is_system', false)->count(),
            ];

            // Levels with user count
            $levelsWithUsers = UserLevel::withCount(['users as total_users', 'users as active_users' => function ($q) {
                $q->where('is_active', true);
            }])->get()->map(function ($level) {
                return [
                    'level_id' => $level->user_level_id,
                    'level_name' => $level->name,
                    'priority' => $level->priority,
                    'total_users' => $level->total_users,
                    'active_users' => $level->active_users,
                    'is_system' => $level->is_system,
                    'is_active' => $level->is_active
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'statistics' => $stats,
                    'levels_with_users' => $levelsWithUsers
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
     * Export user levels data (CSV/Excel) via AJAX
     */
    public function export(Request $request)
    {
        try {
            $format = $request->get('format', 'csv');
            $query = UserLevel::withCount(['users as total_users', 'users as active_users' => function ($q) {
                $q->where('is_active', true);
            }]);

            // Apply same filters as index
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('user_level_id', 'like', "%{$search}%");
                });
            }

            if ($request->has('status') && $request->status !== '') {
                if ($request->status === 'active') {
                    $query->where('is_active', true)->whereNull('deleted_at');
                } elseif ($request->status === 'inactive') {
                    $query->where('is_active', false)->whereNull('deleted_at');
                } elseif ($request->status === 'deleted') {
                    $query->onlyTrashed();
                }
            }

            if ($request->has('system') && $request->system !== '') {
                $query->where('is_system', $request->system === '1');
            }

            $userLevels = $query->get();

            $filename = 'user_levels_export_' . date('Y-m-d_H-i-s') . '.' . $format;

            if ($format === 'csv') {
                $headers = [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                ];

                $callback = function () use ($userLevels) {
                    $file = fopen('php://output', 'w');

                    // CSV headers
                    fputcsv($file, [
                        'Level ID',
                        'Nama',
                        'Deskripsi',
                        'Priority',
                        'Status',
                        'System Level',
                        'Total Users',
                        'Active Users',
                        'Permissions',
                        'Created At'
                    ]);

                    // CSV data
                    foreach ($userLevels as $level) {
                        fputcsv($file, [
                            $level->user_level_id,
                            $level->name,
                            $level->description,
                            $level->priority,
                            $level->status_text,
                            $level->is_system ? 'Yes' : 'No',
                            $level->total_users,
                            $level->active_users,
                            $level->permissions_text,
                            $level->created_at->format('Y-m-d H:i:s')
                        ]);
                    }

                    fclose($file);
                };

                return response()->stream($callback, 200, $headers);
            }

            // For other formats, return JSON with data
            return response()->json([
                'success' => true,
                'message' => 'Export berhasil diproses',
                'data' => $userLevels,
                'count' => $userLevels->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat export: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create default user levels via AJAX
     */
    public function createDefaults(Request $request)
    {
        try {
            UserLevel::createDefaultLevels();

            return response()->json([
                'success' => true,
                'message' => 'Default user levels berhasil dibuat'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat default levels: ' . $e->getMessage()
            ], 500);
        }
    }
}
