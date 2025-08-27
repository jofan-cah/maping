<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mitra extends Model
{
    use HasFactory;

    protected $primaryKey = 'mitra_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'mitra_id',
        'nama_pt',
        'warna_pt',
        'icon_pt',

    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($mitra) {
            if (empty($mitra->mitra_id)) {
                // Generate ID dengan format MTR + tahun + nomor urut
                $year = date('y');
                $lastNumber = static::where('mitra_id', 'like', "MTR{$year}%")
                    ->orderBy('mitra_id', 'desc')
                    ->first();

                if ($lastNumber) {
                    $number = (int)substr($lastNumber->mitra_id, -3) + 1;
                } else {
                    $number = 1;
                }

                $mitra->mitra_id = sprintf('MTR%s%03d', $year, $number);
            }

            // Set default warna jika tidak ada
            if (empty($mitra->warna_pt)) {
                $mitra->warna_pt = '#3B82F6'; // Default blue
            }
        });
    }

    // Relationships
    public function mitraTurunans()
    {
        return $this->hasMany(MitraTurunan::class, 'mitra_id', 'mitra_id');
    }

    // Accessors
    public function getIconUrlAttribute()
    {
        if ($this->icon_pt) {
            return asset('storage/' . $this->icon_pt);
        }
        return null;
    }

    public function getTotalPointsAttribute()
    {
        return $this->mitraTurunans()->count();
    }

    // Methods
    public function addPoint($data)
    {
        return $this->mitraTurunans()->create($data);
    }
}
