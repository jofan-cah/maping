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
                    'users.view', 'users.create', 'users.edit', 'users.delete', 'users.export',
                    'user_levels.view', 'user_levels.create', 'user_levels.edit', 'user_levels.delete',
                    'content.view', 'content.create', 'content.edit', 'content.delete', 'content.publish',
                    'settings.view', 'settings.edit', 'settings.system',
                    'reports.view', 'reports.export', 'reports.analytics',
                    'system.backup', 'system.logs', 'system.maintenance'
                ]
            ],
            [
                'name' => 'Administrator',
                'description' => 'Administrator dengan akses luas namun terbatas pada beberapa fungsi sistem',
                'priority' => 80,
                'is_system' => true,
                'permissions' => [
                    'users.view', 'users.create', 'users.edit', 'users.delete', 'users.export',
                    'user_levels.view',
                    'content.view', 'content.create', 'content.edit', 'content.delete', 'content.publish',
                    'settings.view', 'settings.edit',
                    'reports.view', 'reports.export'
                ]
            ],
            [
                'name' => 'Editor',
                'description' => 'Dapat mengelola konten dan melihat laporan dasar',
                'priority' => 60,
                'permissions' => [
                    'users.view',
                    'content.view', 'content.create', 'content.edit', 'content.delete', 'content.publish',
                    'reports.view'
                ]
            ],
            [
                'name' => 'User',
                'description' => 'User biasa dengan akses terbatas',
                'priority' => 20,
                'is_system' => true,
                'permissions' => ['users.view']
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
