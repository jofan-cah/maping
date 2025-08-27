<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MitraTurunan extends Model
{
    use HasFactory;

    protected $primaryKey = 'mitra_turunan_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'mitra_turunan_id',
        'mitra_id',
        'koordinat',
        'nama_point',
        'deskripsi',
        'nama_file',
        'type_point',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($mitraTurunan) {
            if (empty($mitraTurunan->mitra_turunan_id)) {
                // Generate random 8 digit angka
                do {
                    $randomId = 'TRN' . mt_rand(10000000, 99999999);
                } while (static::where('mitra_turunan_id', $randomId)->exists());

                $mitraTurunan->mitra_turunan_id = $randomId;
            }
        });
    }


    // Relationships
    public function mitra()
    {
        return $this->belongsTo(Mitra::class, 'mitra_id', 'mitra_id');
    }

    // Scopes
    public function scopeByMitra($query, $mitraId)
    {
        return $query->where('mitra_id', $mitraId);
    }

    public function scopeByKoordinat($query, $koordinat)
    {
        return $query->where('koordinat', 'like', '%' . $koordinat . '%');
    }

    public function scopeHasFile($query)
    {
        return $query->whereNotNull('nama_file');
    }

    public function scopeByNamaPoint($query, $namaPoint)
    {
        return $query->where('nama_point', 'like', '%' . $namaPoint . '%');
    }

    // Accessors
    public function getFileUrlAttribute()
    {
        if ($this->nama_file) {
            return asset('storage/' . $this->nama_file);
        }
        return null;
    }

    public function getLatitudeAttribute()
    {
        // Parsing koordinat jika dalam format "lat,lng"
        if ($this->koordinat && str_contains($this->koordinat, ',')) {
            return trim(explode(',', $this->koordinat)[0]);
        }
        return null;
    }

    public function getLongitudeAttribute()
    {
        // Parsing koordinat jika dalam format "lat,lng"
        if ($this->koordinat && str_contains($this->koordinat, ',')) {
            return trim(explode(',', $this->koordinat)[1]);
        }
        return null;
    }

    public function getMitraNameAttribute()
    {
        return $this->mitra ? $this->mitra->nama_pt : 'Unknown';
    }

    public function getMitraColorAttribute()
    {
        return $this->mitra ? $this->mitra->warna_pt : '#3B82F6';
    }

    // Methods
    public function updateKoordinat($latitude, $longitude)
    {
        $this->update(['koordinat' => $latitude . ',' . $longitude]);
        return $this;
    }

    public function uploadFile($file)
    {
        if ($file) {
            $path = $file->store('mitra_files', 'public');
            $this->update(['nama_file' => $path]);
            return $path;
        }
        return null;
    }

    public function deleteFile()
    {
        if ($this->nama_file && Storage::disk('public')->exists($this->nama_file)) {
            Storage::disk('public')->delete($this->nama_file);
            $this->update(['nama_file' => null]);
            return true;
        }
        return false;
    }
}
