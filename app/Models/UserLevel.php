<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserLevel extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'user_level_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_level_id',
        'name',
        'description',
        'permissions',
        'priority',
        'is_active',
        'is_system',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'priority' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($userLevel) {
            if (empty($userLevel->user_level_id)) {
                // Generate ID dengan format LVL + tahun + nomor urut
                $year = date('y');
                $lastNumber = static::where('user_level_id', 'like', "LVL{$year}%")
                    ->orderBy('user_level_id', 'desc')
                    ->first();

                if ($lastNumber) {
                    $number = (int)substr($lastNumber->user_level_id, -3) + 1;
                } else {
                    $number = 1;
                }

                $userLevel->user_level_id = sprintf('LVL%s%03d', $year, $number);
            }
        });
    }

    // Relationships
    public function users()
    {
        return $this->hasMany(User::class, 'user_level_id', 'user_level_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    public function scopeNonSystem($query)
    {
        return $query->where('is_system', false);
    }

    public function scopeByPriority($query, $direction = 'desc')
    {
        return $query->orderBy('priority', $direction);
    }

    // Accessors
    public function getStatusTextAttribute()
    {
        return $this->is_active ? 'Aktif' : 'Non-Aktif';
    }

    public function getPermissionsTextAttribute()
    {
        if (!$this->permissions || empty($this->permissions)) {
            return 'Tidak ada permission';
        }
        return implode(', ', $this->permissions);
    }

    public function getActiveUsersCountAttribute()
    {
        return $this->users()->where('is_active', true)->count();
    }

    public function getTotalUsersCountAttribute()
    {
        return $this->users()->count();
    }

    // Permission methods
    public function hasPermission($permission)
    {
        if (!$this->permissions || !is_array($this->permissions)) {
            return false;
        }
        return in_array($permission, $this->permissions);
    }

    public function addPermission($permission)
    {
        $permissions = $this->permissions ?: [];
        if (!in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $this->update(['permissions' => $permissions]);
        }
        return $this;
    }

    public function removePermission($permission)
    {
        $permissions = $this->permissions ?: [];
        $permissions = array_diff($permissions, [$permission]);
        $this->update(['permissions' => array_values($permissions)]);
        return $this;
    }

    public function hasAnyPermission($permissions)
    {
        if (!is_array($permissions)) {
            $permissions = [$permissions];
        }
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    public function hasAllPermissions($permissions)
    {
        if (!is_array($permissions)) {
            $permissions = [$permissions];
        }
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }
        return true;
    }

    // Methods
    public function activate()
    {
        $this->update(['is_active' => true]);
        return $this;
    }

    public function deactivate()
    {
        if ($this->is_system) {
            throw new \Exception('System level tidak dapat dinonaktifkan');
        }
        $this->update(['is_active' => false]);
        return $this;
    }

    public function toggleStatus()
    {
        if ($this->is_system && $this->is_active) {
            throw new \Exception('System level tidak dapat dinonaktifkan');
        }
        $this->update(['is_active' => !$this->is_active]);
        return $this;
    }

    public function delete()
    {
        if ($this->is_system) {
            throw new \Exception('System level tidak dapat dihapus');
        }

        // Check if there are users using this level
        if ($this->users()->exists()) {
            throw new \Exception('Tidak dapat menghapus level yang masih digunakan oleh user');
        }

        return parent::delete();
    }

    /**
     * Static method untuk create default levels
     */
    public static function createDefaultLevels()
{
    $defaultLevels = [
        [
            'name' => 'Super Administrator',
            'description' => 'Level tertinggi dengan akses penuh ke seluruh sistem',
            'priority' => 100,
            'is_system' => true,
            'permissions' => [
                // Full access to everything
                'users.view', 'users.create', 'users.edit', 'users.delete', 'users.restore', 'users.force_delete',
                'users.toggle_status', 'users.export', 'users.statistics', 'users.bulk_action',

                'user_levels.view', 'user_levels.create', 'user_levels.edit', 'user_levels.delete',
                'user_levels.restore', 'user_levels.force_delete', 'user_levels.toggle_status',
                'user_levels.permissions', 'user_levels.export', 'user_levels.statistics',
                'user_levels.bulk_action', 'user_levels.create_defaults',

                'mitras.view', 'mitras.create', 'mitras.edit', 'mitras.delete', 'mitras.duplicate',
                'mitras.export', 'mitras.statistics', 'mitras.bulk_action', 'mitras.colors', 'mitras.points_summary',

                'points.view', 'points.create', 'points.edit', 'points.delete', 'points.upload_kmz',
                'points.export', 'points.statistics', 'points.bulk_action', 'points.map_data', 'points.update_coordinates',

                'routing.nearest_points', 'routing.points_in_radius', 'routing.calculate_route',
                'routing.optimal_route', 'routing.coverage_analysis', 'routing.gap_analysis',

                'maps.view', 'maps.points', 'maps.search', 'maps.statistics', 'maps.fullscreen', 'maps.export',

                'coverage.view', 'coverage.points', 'coverage.nearest', 'coverage.calculate',
                'coverage.gap_analysis', 'coverage.route',

                'reports.view', 'reports.create', 'reports.export', 'reports.analytics',
                'reports.statistics', 'reports.charts',

                'settings.view', 'settings.edit', 'settings.system', 'settings.database',
                'settings.backup', 'settings.maintenance',

                'system.logs', 'system.monitoring', 'system.cache', 'system.queue',
                'system.scheduler', 'system.debug',

                'dashboard.view', 'dashboard.statistics', 'dashboard.widgets', 'dashboard.export'
            ]
        ],
        [
            'name' => 'Administrator',
            'description' => 'Administrator dengan akses luas namun terbatas pada beberapa fungsi sistem',
            'priority' => 80,
            'is_system' => true,
            'permissions' => [
                'users.view', 'users.create', 'users.edit', 'users.delete', 'users.toggle_status',
                'users.export', 'users.statistics', 'users.bulk_action',

                'user_levels.view',

                'mitras.view', 'mitras.create', 'mitras.edit', 'mitras.delete', 'mitras.duplicate',
                'mitras.export', 'mitras.statistics', 'mitras.bulk_action', 'mitras.colors', 'mitras.points_summary',

                'points.view', 'points.create', 'points.edit', 'points.delete', 'points.upload_kmz',
                'points.export', 'points.statistics', 'points.bulk_action', 'points.map_data', 'points.update_coordinates',

                'routing.nearest_points', 'routing.points_in_radius', 'routing.calculate_route',
                'routing.optimal_route', 'routing.coverage_analysis', 'routing.gap_analysis',

                'maps.view', 'maps.points', 'maps.search', 'maps.statistics', 'maps.fullscreen', 'maps.export',

                'coverage.view', 'coverage.points', 'coverage.nearest', 'coverage.calculate',
                'coverage.gap_analysis', 'coverage.route',

                'reports.view', 'reports.create', 'reports.export', 'reports.analytics',
                'reports.statistics', 'reports.charts',

                'settings.view', 'settings.edit',

                'dashboard.view', 'dashboard.statistics', 'dashboard.widgets', 'dashboard.export'
            ]
        ],
        [
            'name' => 'Manager',
            'description' => 'Manager dengan akses ke manajemen mitra dan analisis',
            'priority' => 60,
            'permissions' => [
                'users.view',

                'mitras.view', 'mitras.create', 'mitras.edit', 'mitras.export', 'mitras.statistics', 'mitras.points_summary',

                'points.view', 'points.create', 'points.edit', 'points.upload_kmz',
                'points.export', 'points.statistics', 'points.map_data',

                'routing.nearest_points', 'routing.points_in_radius', 'routing.calculate_route',
                'routing.optimal_route', 'routing.coverage_analysis', 'routing.gap_analysis',

                'maps.view', 'maps.points', 'maps.search', 'maps.statistics', 'maps.fullscreen', 'maps.export',

                'coverage.view', 'coverage.points', 'coverage.nearest', 'coverage.calculate',
                'coverage.gap_analysis', 'coverage.route',

                'reports.view', 'reports.export', 'reports.analytics', 'reports.statistics', 'reports.charts',

                'dashboard.view', 'dashboard.statistics', 'dashboard.widgets'
            ]
        ],
        [
            'name' => 'Operator',
            'description' => 'Operator dengan akses terbatas untuk operasional sehari-hari',
            'priority' => 40,
            'permissions' => [
                'mitras.view', 'mitras.export', 'mitras.statistics', 'mitras.points_summary',

                'points.view', 'points.create', 'points.edit', 'points.export', 'points.statistics', 'points.map_data',

                'routing.nearest_points', 'routing.points_in_radius', 'routing.calculate_route',

                'maps.view', 'maps.points', 'maps.search', 'maps.statistics', 'maps.fullscreen',

                'coverage.view', 'coverage.points', 'coverage.nearest', 'coverage.calculate', 'coverage.route',

                'reports.view', 'reports.statistics',

                'dashboard.view', 'dashboard.statistics'
            ]
        ],
        [
            'name' => 'User',
            'description' => 'User biasa dengan akses viewing saja',
            'priority' => 20,
            'is_system' => true,
            'permissions' => [
                'mitras.view', 'mitras.statistics',
                'points.view', 'points.statistics', 'points.map_data',
                'maps.view', 'maps.points', 'maps.search', 'maps.statistics',
                'coverage.view', 'coverage.points',
                'reports.view',
                'dashboard.view'
            ]
        ]
    ];

    foreach ($defaultLevels as $levelData) {
        static::firstOrCreate(
            ['name' => $levelData['name']],
            $levelData
        );
    }
}

}
