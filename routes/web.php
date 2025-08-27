<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MitraController;
use App\Http\Controllers\MitraTurunanController;
use App\Http\Controllers\UserLevelController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\CoverageController;

// Login Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

// Logout Route (perlu middleware auth)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Dashboard route
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Users Management Routes


    // Additional User Routes untuk AJAX
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('statistics', [UserController::class, 'statistics'])->name('statistics');
        // User specific actions
        Route::post('{user}/restore', [UserController::class, 'restore'])->name('restore');
        Route::delete('{user}/force-delete', [UserController::class, 'forceDestroy'])->name('force-destroy');
        Route::post('{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');

        // Bulk operations
        Route::post('bulk-action', [UserController::class, 'bulkAction'])->name('bulk-action');

        // Statistics and export
        Route::get('export', [UserController::class, 'export'])->name('export');
    });
    Route::resource('users', UserController::class);

    // User Levels Management Routes


    // Additional User Level Routes untuk AJAX
    Route::prefix('user-levels')->name('user-levels.')->group(function () {
        Route::get('statistics', [UserLevelController::class, 'statistics'])->name('statistics');
        // User Level specific actions
        Route::post('{userLevel}/restore', [UserLevelController::class, 'restore'])->name('restore');
        Route::delete('{userLevel}/force-delete', [UserLevelController::class, 'forceDestroy'])->name('force-destroy');
        Route::post('{userLevel}/toggle-status', [UserLevelController::class, 'toggleStatus'])->name('toggle-status');

        // Permission management
        Route::post('{userLevel}/add-permission', [UserLevelController::class, 'addPermission'])->name('add-permission');
        Route::post('{userLevel}/remove-permission', [UserLevelController::class, 'removePermission'])->name('remove-permission');

        // Bulk operations
        Route::post('bulk-action', [UserLevelController::class, 'bulkAction'])->name('bulk-action');

        // Statistics and utilities
        Route::get('available-permissions', [UserLevelController::class, 'getAvailablePermissions'])->name('available-permissions');
        Route::get('export', [UserLevelController::class, 'export'])->name('export');
        Route::post('create-defaults', [UserLevelController::class, 'createDefaults'])->name('create-defaults');
    });
    Route::resource('user-levels', UserLevelController::class);


    // Mitra Management Routes

    // Additional Mitra Routes untuk AJAX
    Route::prefix('mitras')->name('mitras.')->group(function () {
        // Bulk operations
        Route::post('bulk-action', [MitraController::class, 'bulkAction'])->name('bulk-action');

        // Statistics and utilities
        Route::get('statistics', [MitraController::class, 'statistics'])->name('statistics');
        Route::get('colors', [MitraController::class, 'getColors'])->name('colors');
        Route::get('export', [MitraController::class, 'export'])->name('export');

        // Mitra specific actions
        Route::post('{mitra}/duplicate', [MitraController::class, 'duplicate'])->name('duplicate');
        Route::get('{mitra}/points-summary', [MitraController::class, 'getPointsSummary'])->name('points-summary');
    });

    Route::resource('mitras', MitraController::class);

    // Mitra Turunan (Points) Management Routes

    // Additional Mitra Turunan Routes untuk AJAX & Advanced Features
    Route::prefix('mitra-turunans')->name('mitra-turunans.')->group(function () {

        // === File Management ===
        Route::post('upload-kmz', [MitraTurunanController::class, 'uploadKmz'])->name('upload-kmz');
        Route::get('export', [MitraTurunanController::class, 'export'])->name('export');

        // === Bulk Operations ===
        Route::post('bulk-action', [MitraTurunanController::class, 'bulkAction'])->name('bulk-action');

        // === Statistics ===
        Route::get('statistics', [MitraTurunanController::class, 'statistics'])->name('statistics');

        // === Maps & Visualization API ===
        Route::get('map-data', [MitraTurunanController::class, 'getMapData'])->name('map-data');
        Route::post('{point}/coordinates', [MitraTurunanController::class, 'updateCoordinates'])->name('update-coordinates');

        // === LRM & Route Planning API ===
        Route::get('nearest-points', [MitraTurunanController::class, 'findNearestPoints'])->name('nearest-points');
        Route::get('points-in-radius', [MitraTurunanController::class, 'getPointsInRadius'])->name('points-in-radius');
        Route::post('calculate-route', [MitraTurunanController::class, 'calculateRoute'])->name('calculate-route');
        Route::post('optimal-route', [MitraTurunanController::class, 'suggestOptimalRoute'])->name('optimal-route');
    });
    Route::resource('mitra-turunans', MitraTurunanController::class);
    // Maps Routes
    Route::prefix('maps')->name('maps.')->group(function () {
        Route::get('/', [MapController::class, 'index'])->name('index');
        Route::get('points', [MapController::class, 'getPoints'])->name('points');
        Route::get('statistics', [MapController::class, 'getStatistics'])->name('statistics');
        Route::get('search', [MapController::class, 'searchPoints'])->name('search');
    });

    // Coverage Routes
    Route::get('osrm/route/driving/{coordinates}', [CoverageController::class, 'route'])->name('route');
    Route::prefix('coverage')->name('coverage.')->group(function () {
        Route::get('/', [CoverageController::class, 'index'])->name('index');
        Route::get('points', [CoverageController::class, 'getPoints'])->name('points');
        Route::get('nearest', [CoverageController::class, 'findNearestPoints'])->name('nearest');
        Route::post('calculate', [CoverageController::class, 'calculateCoverage'])->name('calculate');
        Route::post('analyze-gap', [CoverageController::class, 'analyzeCoverageGap'])->name('analyze-gap');
    });
});

// Redirect root ke login atau dashboard
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});
