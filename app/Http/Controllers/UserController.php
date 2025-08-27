<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users with AJAX support
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = User::with('userLevel')->withTrashed();

            // Search functionality
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('user_id', 'like', "%{$search}%");
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

            // Filter by user level
            if ($request->has('user_level') && !empty($request->user_level)) {
                $query->where('user_level_id', $request->user_level);
            }

            // Sorting
            $sortField = $request->get('sort_field', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            $query->orderBy($sortField, $sortDirection);

            // Pagination
            $perPage = $request->get('per_page', 10);
            $users = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $users->items(),
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                    'from' => $users->firstItem(),
                    'to' => $users->lastItem(),
                ]
            ]);
        }

        // Load user levels for filter
        $userLevels = UserLevel::active()->orderBy('priority', 'desc')->get();

        return view('users.indexUser', compact('userLevels'));
    }

    /**
     * Store a newly created user via AJAX
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'user_level_id' => 'nullable|exists:user_levels,user_level_id',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'user_level_id' => $request->user_level_id,
                'is_active' => $request->boolean('is_active', true),
            ];

            // Handle profile picture upload
            if ($request->hasFile('profile_picture')) {
                $file = $request->file('profile_picture');
                $path = $file->store('profile_pictures', 'public');
                $userData['profile_picture'] = $path;
            }

            $user = User::create($userData);
            $user->load('userLevel');

            return response()->json([
                'success' => true,
                'message' => 'User berhasil dibuat',
                'data' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified user via AJAX
     */
    public function show(Request $request, $userId)
    {
        try {
            $user = User::withTrashed()->with('userLevel')->where('user_id', $userId)->firstOrFail();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $user
                ]);
            }

            return view('users.showUser', compact('user'));
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan'
                ], 404);
            }

            return redirect()->route('users.index')->with('error', 'User tidak ditemukan');
        }
    }

    /**
     * Update the specified user via AJAX
     */
    public function update(Request $request, $userId)
    {
        try {
            $user = User::withTrashed()->where('user_id', $userId)->firstOrFail();

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => [
                    'required',
                    'string',
                    'email',
                    'max:255',
                    Rule::unique('users')->ignore($user->user_id, 'user_id')
                ],
                'password' => 'nullable|string|min:8|confirmed',
                'user_level_id' => 'nullable|exists:user_levels,user_level_id',
                'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'user_level_id' => $request->user_level_id,
                'is_active' => $request->boolean('is_active', true),
            ];

            // Update password if provided
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            // Handle profile picture upload
            if ($request->hasFile('profile_picture')) {
                // Delete old profile picture
                if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                    Storage::disk('public')->delete($user->profile_picture);
                }

                $file = $request->file('profile_picture');
                $path = $file->store('profile_pictures', 'public');
                $userData['profile_picture'] = $path;
            }

            $user->update($userData);
            $user->load('userLevel');

            return response()->json([
                'success' => true,
                'message' => 'User berhasil diperbarui',
                'data' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Soft delete user via AJAX
     */
    public function destroy(Request $request, $userId)
    {
        try {
            $user = User::where('user_id', $userId)->firstOrFail();
            $user->delete(); // This will trigger soft delete and deactivate user

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User berhasil dihapus'
                ]);
            }

            return redirect()->route('users.index')->with('success', 'User berhasil dihapus');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat menghapus user: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('users.index')->with('error', 'Terjadi kesalahan saat menghapus user');
        }
    }

    /**
     * Restore soft deleted user via AJAX
     */
    public function restore(Request $request, $userId)
    {
        try {
            $user = User::onlyTrashed()->where('user_id', $userId)->firstOrFail();
            $user->restore(); // This will restore and activate user

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User berhasil dipulihkan'
                ]);
            }

            return redirect()->route('users.index')->with('success', 'User berhasil dipulihkan');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat memulihkan user: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('users.index')->with('error', 'Terjadi kesalahan saat memulihkan user');
        }
    }

    /**
     * Force delete user (permanent) via AJAX
     */
    public function forceDestroy(Request $request, $userId)
    {
        try {
            $user = User::onlyTrashed()->where('user_id', $userId)->firstOrFail();

            // Delete profile picture if exists
            if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            $user->forceDelete();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User berhasil dihapus permanen'
                ]);
            }

            return redirect()->route('users.index')->with('success', 'User berhasil dihapus permanen');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat menghapus user permanen: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('users.index')->with('error', 'Terjadi kesalahan saat menghapus user permanen');
        }
    }

    /**
     * Toggle user status (active/inactive) via AJAX
     */
    public function toggleStatus(Request $request, $userId)
    {
        try {
            $user = User::where('user_id', $userId)->firstOrFail();
            $user->toggleStatus();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status user berhasil diubah',
                    'data' => [
                        'is_active' => $user->is_active,
                        'status_text' => $user->status_text
                    ]
                ]);
            }

            return redirect()->route('users.index')->with('success', 'Status user berhasil diubah');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat mengubah status: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('users.index')->with('error', 'Terjadi kesalahan saat mengubah status');
        }
    }

    /**
     * Bulk actions for users via AJAX
     */
    public function bulkAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:activate,deactivate,delete,restore,force_delete',
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,user_id'
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
            $userIds = $request->user_ids;
            $count = 0;

            switch ($action) {
                case 'activate':
                    $count = User::whereIn('user_id', $userIds)->update(['is_active' => true]);
                    $message = "Berhasil mengaktifkan {$count} user";
                    break;

                case 'deactivate':
                    $count = User::whereIn('user_id', $userIds)->update(['is_active' => false]);
                    $message = "Berhasil menonaktifkan {$count} user";
                    break;

                case 'delete':
                    $users = User::whereIn('user_id', $userIds)->get();
                    foreach ($users as $user) {
                        $user->delete();
                        $count++;
                    }
                    $message = "Berhasil menghapus {$count} user";
                    break;

                case 'restore':
                    $users = User::onlyTrashed()->whereIn('user_id', $userIds)->get();
                    foreach ($users as $user) {
                        $user->restore();
                        $count++;
                    }
                    $message = "Berhasil memulihkan {$count} user";
                    break;

                case 'force_delete':
                    $users = User::onlyTrashed()->whereIn('user_id', $userIds)->get();
                    foreach ($users as $user) {
                        // Delete profile picture if exists
                        if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                            Storage::disk('public')->delete($user->profile_picture);
                        }
                        $user->forceDelete();
                        $count++;
                    }
                    $message = "Berhasil menghapus permanen {$count} user";
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'count' => $count
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat melakukan aksi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export users data (CSV/Excel) via AJAX
     */
    public function export(Request $request)
    {
        try {
            $format = $request->get('format', 'csv');
            $query = User::with('userLevel');

            // Apply same filters as index
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('user_id', 'like', "%{$search}%");
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

            if ($request->has('user_level') && !empty($request->user_level)) {
                $query->where('user_level_id', $request->user_level);
            }

            $users = $query->get();

            $filename = 'users_export_' . date('Y-m-d_H-i-s') . '.' . $format;

            if ($format === 'csv') {
                $headers = [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                ];

                $callback = function() use ($users) {
                    $file = fopen('php://output', 'w');

                    // CSV headers
                    fputcsv($file, [
                        'User ID',
                        'Nama',
                        'Email',
                        'Level',
                        'Status',
                        'Email Verified',
                        'Last Login',
                        'Created At'
                    ]);

                    // CSV data
                    foreach ($users as $user) {
                        fputcsv($file, [
                            $user->user_id,
                            $user->name,
                            $user->email,
                            $user->userLevel ? $user->userLevel->name : 'No Level',
                            $user->status_text,
                            $user->email_verified_at ? 'Yes' : 'No',
                            $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i:s') : 'Never',
                            $user->created_at->format('Y-m-d H:i:s')
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
                'data' => $users,
                'count' => $users->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat export: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user statistics via AJAX
     */
    public function statistics(Request $request)
    {
        try {
            $stats = [
                'total_users' => User::withTrashed()->count(),
                'active_users' => User::where('is_active', true)->count(),
                'inactive_users' => User::where('is_active', false)->count(),
                'deleted_users' => User::onlyTrashed()->count(),
                'verified_users' => User::whereNotNull('email_verified_at')->count(),
                'unverified_users' => User::whereNull('email_verified_at')->count(),
                'recent_users' => User::where('created_at', '>=', now()->subDays(30))->count(),
            ];

            // Users by level
            $usersByLevel = UserLevel::withCount(['users' => function($query) {
                $query->where('is_active', true);
            }])->get()->map(function($level) {
                return [
                    'level_name' => $level->name,
                    'user_count' => $level->users_count
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'statistics' => $stats,
                    'users_by_level' => $usersByLevel
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil statistik: ' . $e->getMessage()
            ], 500);
        }
    }
}
