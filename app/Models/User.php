<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Primary key custom
     */
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'password',
        'profile_picture',
        'last_login_at',
        'is_active',
        'user_level_id',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->user_id)) {
                $user->user_id = 'USR-' . strtoupper(Str::random(10));
            }

            // Set default user level jika tidak ada
            if (empty($user->user_level_id)) {
                $defaultLevel = UserLevel::where('name', 'User')->first();
                if ($defaultLevel) {
                    $user->user_level_id = $defaultLevel->user_level_id;
                }
            }
        });
    }

    // Relationships
    public function userLevel()
    {
        return $this->belongsTo(UserLevel::class, 'user_level_id', 'user_level_id');
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

    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    public function scopeUnverified($query)
    {
        return $query->whereNull('email_verified_at');
    }

    public function scopeByLevel($query, $levelName)
    {
        return $query->whereHas('userLevel', function ($q) use ($levelName) {
            $q->where('name', $levelName);
        });
    }

    public function scopeMinLevel($query, $minPriority)
    {
        return $query->whereHas('userLevel', function ($q) use ($minPriority) {
            $q->where('priority', '>=', $minPriority);
        });
    }

    // Accessors
    public function getProfilePictureUrlAttribute()
    {
        if ($this->profile_picture) {
            return asset('storage/' . $this->profile_picture);
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
    }

    public function getStatusTextAttribute()
    {
        return $this->is_active ? 'Aktif' : 'Non-Aktif';
    }

    public function getLevelNameAttribute()
    {
        return $this->userLevel ? $this->userLevel->name : 'No Level';
    }

    public function getLevelPriorityAttribute()
    {
        return $this->userLevel ? $this->userLevel->priority : 0;
    }

    // Permission methods
    // public function hasPermission($permission)
    // {
    //     if (!$this->is_active) {
    //         return false;
    //     }

    //     return $this->userLevel && $this->userLevel->hasPermission($permission);
    // }

    // public function hasAnyPermission($permissions)
    // {
    //     if (!$this->is_active) {
    //         return false;
    //     }

    //     return $this->userLevel && $this->userLevel->hasAnyPermission($permissions);
    // }

    // public function hasAllPermissions($permissions)
    // {
    //     if (!$this->is_active) {
    //         return false;
    //     }

    //     return $this->userLevel && $this->userLevel->hasAllPermissions($permissions);
    // }

    public function isSuperAdmin()
    {
        return $this->userLevel && $this->userLevel->name === 'Super Administrator';
    }

    public function isAdmin()
    {
        return $this->userLevel && in_array($this->userLevel->name, ['Super Administrator', 'Administrator']);
    }

    public function hasMinLevel($minPriority)
    {
        return $this->level_priority >= $minPriority;
    }

    // User level management
    public function assignLevel($levelId)
    {
        $this->update(['user_level_id' => $levelId]);
        return $this;
    }

    public function removeLevel()
    {
        $defaultLevel = UserLevel::where('name', 'User')->first();
        $this->update(['user_level_id' => $defaultLevel ? $defaultLevel->user_level_id : null]);
        return $this;
    }

    // Methods
    public function activate()
    {
        $this->update(['is_active' => true]);
        return $this;
    }

    public function deactivate()
    {
        $this->update(['is_active' => false]);
        return $this;
    }

    public function toggleStatus()
    {
        $this->update(['is_active' => !$this->is_active]);
        return $this;
    }

    public function updateLastLogin()
    {
        $this->update(['last_login_at' => now()]);
        return $this;
    }

    public function delete()
    {
        $this->deactivate();
        return parent::delete();
    }

    public function restore()
    {
        $restored = parent::restore();
        if ($restored) {
            $this->activate();
        }
        return $restored;
    }

    public function hasPermission($permission)
    {
        if (!$this->userLevel) {
            return false;
        }

        return $this->userLevel->hasPermission($permission);
    }

    // public function hasPermission($permission)
    // {
    //     if (!$this->userLevel) {
    //         return false;
    //     }

    //     return $this->userLevel->hasPermission($permission);
    // }

    /**
     * Check if user has any of the given permissions
     */
    public function hasAnyPermission($permissions)
    {
        if (!$this->userLevel) {
            return false;
        }

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

    /**
     * Check if user has all of the given permissions
     */
    public function hasAllPermissions($permissions)
    {
        if (!$this->userLevel) {
            return false;
        }

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
}
