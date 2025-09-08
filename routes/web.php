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
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

// Logout Route (perlu middleware auth)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/profile', [LoginController::class, 'editProfile'])->name('profile.edit');
    Route::put('/profile', [LoginController::class, 'updateProfile'])->name('profile.update');

    // Dashboard route
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Users Management Routes
    Route::middleware('permission:users.view')->group(function () {
        // User resource routes dengan permission spesifik per action
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');

        Route::get('/users/create', [UserController::class, 'create'])->middleware('permission:users.create')->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->middleware('permission:users.create')->name('users.store');

        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->middleware('permission:users.edit')->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->middleware('permission:users.edit')->name('users.update');
        Route::patch('/users/{user}', [UserController::class, 'update'])->middleware('permission:users.edit');

        Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware('permission:users.delete')->name('users.destroy');
    });

    // Additional User Routes untuk AJAX
    Route::prefix('users')->name('users.')->middleware('permission:users.view')->group(function () {
        Route::get('statistics', [UserController::class, 'statistics'])->middleware('permission:users.statistics')->name('statistics');
        // User specific actions
        Route::post('{user}/restore', [UserController::class, 'restore'])->middleware('permission:users.restore')->name('restore');
        Route::delete('{user}/force-delete', [UserController::class, 'forceDestroy'])->middleware('permission:users.force_delete')->name('force-destroy');
        Route::post('{user}/toggle-status', [UserController::class, 'toggleStatus'])->middleware('permission:users.toggle_status')->name('toggle-status');

        // Bulk operations
        Route::post('bulk-action', [UserController::class, 'bulkAction'])->middleware('permission:users.bulk_action')->name('bulk-action');

        // Statistics and export
        Route::get('export', [UserController::class, 'export'])->middleware('permission:users.export')->name('export');
    });
    // Additional User Level Routes untuk AJAX
    Route::prefix('user-levels')->name('user-levels.')->middleware('permission:user_levels.view')->group(function () {
        Route::get('available-permissions', [UserLevelController::class, 'getAvailablePermissions'])->name('available-permissions');
        Route::get('statistics', [UserLevelController::class, 'statistics'])->middleware('permission:user_levels.statistics')->name('statistics');
        // User Level specific actions
        Route::post('{userLevel}/restore', [UserLevelController::class, 'restore'])->middleware('permission:user_levels.restore')->name('restore');
        Route::delete('{userLevel}/force-delete', [UserLevelController::class, 'forceDestroy'])->middleware('permission:user_levels.force_delete')->name('force-destroy');
        Route::post('{userLevel}/toggle-status', [UserLevelController::class, 'toggleStatus'])->middleware('permission:user_levels.toggle_status')->name('toggle-status');

        // Permission management
        Route::post('{userLevel}/add-permission', [UserLevelController::class, 'addPermission'])->middleware('permission:user_levels.permissions')->name('add-permission');
        Route::post('{userLevel}/remove-permission', [UserLevelController::class, 'removePermission'])->middleware('permission:user_levels.permissions')->name('remove-permission');

        // Bulk operations
        Route::post('bulk-action', [UserLevelController::class, 'bulkAction'])->middleware('permission:user_levels.bulk_action')->name('bulk-action');

        // Statistics and utilities
        Route::get('export', [UserLevelController::class, 'export'])->middleware('permission:user_levels.export')->name('export');
        Route::post('create-defaults', [UserLevelController::class, 'createDefaults'])->middleware('permission:user_levels.create_defaults')->name('create-defaults');
    });

    // User Levels Management Routes
    Route::middleware('permission:user_levels.view')->group(function () {
        // User Levels resource routes dengan permission spesifik per action
        Route::get('/user-levels', [UserLevelController::class, 'index'])->name('user-levels.index');
        Route::get('/user-levels/{userLevel}', [UserLevelController::class, 'show'])->name('user-levels.show');

        Route::get('/user-levels/create', [UserLevelController::class, 'create'])->middleware('permission:user_levels.create')->name('user-levels.create');
        Route::post('/user-levels', [UserLevelController::class, 'store'])->middleware('permission:user_levels.create')->name('user-levels.store');

        Route::get('/user-levels/{userLevel}/edit', [UserLevelController::class, 'edit'])->middleware('permission:user_levels.edit')->name('user-levels.edit');
        Route::put('/user-levels/{userLevel}', [UserLevelController::class, 'update'])->middleware('permission:user_levels.edit')->name('user-levels.update');
        Route::patch('/user-levels/{userLevel}', [UserLevelController::class, 'update'])->middleware('permission:user_levels.edit');

        Route::delete('/user-levels/{userLevel}', [UserLevelController::class, 'destroy'])->middleware('permission:user_levels.delete')->name('user-levels.destroy');
    });



    // Mitra Management Routes
    Route::middleware('permission:mitras.view')->group(function () {
        // Mitra resource routes dengan permission spesifik per action
        Route::get('/mitras', [MitraController::class, 'index'])->name('mitras.index');
        Route::get('/mitras/{mitra}', [MitraController::class, 'show'])->name('mitras.show');

        Route::get('/mitras/create', [MitraController::class, 'create'])->middleware('permission:mitras.create')->name('mitras.create');
        Route::post('/mitras', [MitraController::class, 'store'])->middleware('permission:mitras.create')->name('mitras.store');

        Route::get('/mitras/{mitra}/edit', [MitraController::class, 'edit'])->middleware('permission:mitras.edit')->name('mitras.edit');
        Route::put('/mitras/{mitra}', [MitraController::class, 'update'])->middleware('permission:mitras.edit')->name('mitras.update');
        Route::patch('/mitras/{mitra}', [MitraController::class, 'update'])->middleware('permission:mitras.edit');

        Route::delete('/mitras/{mitra}', [MitraController::class, 'destroy'])->middleware('permission:mitras.delete')->name('mitras.destroy');
    });

    // Additional Mitra Routes untuk AJAX
    Route::prefix('mitras')->name('mitras.')->middleware('permission:mitras.view')->group(function () {
        // Bulk operations
        Route::post('bulk-action', [MitraController::class, 'bulkAction'])->middleware('permission:mitras.bulk_action')->name('bulk-action');

        // Statistics and utilities
        Route::get('statistics', [MitraController::class, 'statistics'])->middleware('permission:mitras.statistics')->name('statistics');
        Route::get('colors', [MitraController::class, 'getColors'])->middleware('permission:mitras.colors')->name('colors');
        Route::get('export', [MitraController::class, 'export'])->middleware('permission:mitras.export')->name('export');

        // Mitra specific actions
        Route::post('{mitra}/duplicate', [MitraController::class, 'duplicate'])->middleware('permission:mitras.duplicate')->name('duplicate');
        Route::get('{mitra}/points-summary', [MitraController::class, 'getPointsSummary'])->middleware('permission:mitras.points_summary')->name('points-summary');
    });

    // Mitra Turunan (Points) Management Routes
    Route::middleware('permission:points.view')->group(function () {
        // Points resource routes dengan permission spesifik per action
        Route::get('/mitra-turunans', [MitraTurunanController::class, 'index'])->name('mitra-turunans.index');
        Route::get('/mitra-turunans/{mitraTurunan}', [MitraTurunanController::class, 'show'])->name('mitra-turunans.show');

        Route::get('/mitra-turunans/create', [MitraTurunanController::class, 'create'])->middleware('permission:points.create')->name('mitra-turunans.create');
        Route::post('/mitra-turunans', [MitraTurunanController::class, 'store'])->middleware('permission:points.create')->name('mitra-turunans.store');

        Route::get('/mitra-turunans/{mitraTurunan}/edit', [MitraTurunanController::class, 'edit'])->middleware('permission:points.edit')->name('mitra-turunans.edit');
        Route::put('/mitra-turunans/{mitraTurunan}', [MitraTurunanController::class, 'update'])->middleware('permission:points.edit')->name('mitra-turunans.update');
        Route::patch('/mitra-turunans/{mitraTurunan}', [MitraTurunanController::class, 'update'])->middleware('permission:points.edit');

        Route::delete('/mitra-turunans/{mitraTurunan}', [MitraTurunanController::class, 'destroy'])->middleware('permission:points.delete')->name('mitra-turunans.destroy');
    });

    // Additional Mitra Turunan Routes untuk AJAX & Advanced Features
    Route::prefix('mitra-turunans')->name('mitra-turunans.')->middleware('permission:points.view')->group(function () {

        // === File Management ===
        Route::post('upload-kmz', [MitraTurunanController::class, 'uploadKmz'])->middleware('permission:points.upload_kmz')->name('upload-kmz');
        Route::get('export', [MitraTurunanController::class, 'export'])->middleware('permission:points.export')->name('export');

        // === Bulk Operations ===
        Route::post('bulk-action', [MitraTurunanController::class, 'bulkAction'])->middleware('permission:points.bulk_action')->name('bulk-action');

        // === Statistics ===
        Route::get('statistics', [MitraTurunanController::class, 'statistics'])->middleware('permission:points.statistics')->name('statistics');

        // === Maps & Visualization API ===
        Route::get('map-data', [MitraTurunanController::class, 'getMapData'])->middleware('permission:points.map_data')->name('map-data');
        Route::post('{point}/coordinates', [MitraTurunanController::class, 'updateCoordinates'])->middleware('permission:points.update_coordinates')->name('update-coordinates');

        // === LRM & Route Planning API ===
        Route::get('nearest-points', [MitraTurunanController::class, 'findNearestPoints'])->middleware('permission:routing.nearest_points')->name('nearest-points');
        Route::get('points-in-radius', [MitraTurunanController::class, 'getPointsInRadius'])->middleware('permission:routing.points_in_radius')->name('points-in-radius');
        Route::post('calculate-route', [MitraTurunanController::class, 'calculateRoute'])->middleware('permission:routing.calculate_route')->name('calculate-route');
        Route::post('optimal-route', [MitraTurunanController::class, 'suggestOptimalRoute'])->middleware('permission:routing.optimal_route')->name('optimal-route');
    });

    Route::prefix('maps')->name('maps.')->group(function () {
        Route::get('/', [MapController::class, 'index'])->name('index');
        Route::get('points', [MapController::class, 'getPoints'])->name('points');
        Route::get('statistics', [MapController::class, 'getStatistics'])->name('statistics');
        Route::get('search', [MapController::class, 'searchPoints'])->name('search');
        Route::post('cache/clear', [MapController::class, 'clearCache'])->name('cache.clear');
    });

    // Coverage Routes
    Route::get('osrm/route/driving/{coordinates}', [CoverageController::class, 'route'])->middleware('permission:coverage.route')->name('route');
    Route::prefix('coverage')->name('coverage.')->middleware('permission:coverage.view')->group(function () {
        Route::get('/', [CoverageController::class, 'index'])->name('index');
        Route::get('points', [CoverageController::class, 'getPoints'])->middleware('permission:coverage.points')->name('points');
        Route::get('nearest', [CoverageController::class, 'findNearestPoints'])->middleware('permission:coverage.nearest')->name('nearest');
        Route::post('calculate', [CoverageController::class, 'calculateCoverage'])->middleware('permission:coverage.calculate')->name('calculate');
        Route::post('analyze-gap', [CoverageController::class, 'analyzeCoverageGap'])->middleware('permission:coverage.gap_analysis')->name('analyze-gap');
    });
});
// Redirect root ke login atau dashboard
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});
