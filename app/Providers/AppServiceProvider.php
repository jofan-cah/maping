<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Custom Blade directive untuk permission
        Blade::if('permission', function ($permission) {
            if (!Auth::check()) {
                return false;
            }
            return Auth::user()->hasPermission($permission);
        });

        // Custom Blade directive untuk multiple permissions (OR condition)
        Blade::if('anypermission', function ($permissions) {
            if (!Auth::check()) {
                return false;
            }

            if (!is_array($permissions)) {
                $permissions = [$permissions];
            }

            return Auth::user()->hasAnyPermission($permissions);
        });

        // Custom Blade directive untuk multiple permissions (AND condition)
        Blade::if('allpermissions', function ($permissions) {
            if (!Auth::check()) {
                return false;
            }

            if (!is_array($permissions)) {
                $permissions = [$permissions];
            }

            return Auth::user()->hasAllPermissions($permissions);
        });

        // Custom Blade directive untuk level checking
        Blade::if('level', function ($levelName) {
            if (!Auth::check()) {
                return false;
            }
            return Auth::user()->level_name === $levelName;
        });

        // Custom Blade directive untuk minimum level checking
        Blade::if('minlevel', function ($minPriority) {
            if (!Auth::check()) {
                return false;
            }
            return Auth::user()->hasMinLevel($minPriority);
        });

        // Custom Blade directive untuk admin checking
        Blade::if('admin', function () {
            if (!Auth::check()) {
                return false;
            }
            return Auth::user()->isAdmin();
        });

        // Custom Blade directive untuk super admin checking
        Blade::if('superadmin', function () {
            if (!Auth::check()) {
                return false;
            }
            return Auth::user()->isSuperAdmin();
        });

        // Custom Blade directive untuk active user checking
        Blade::if('active', function () {
            if (!Auth::check()) {
                return false;
            }
            return Auth::user()->is_active;
        });
    }
}
