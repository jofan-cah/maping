<?php

namespace App\Http\Controllers;

use App\Models\Mitra;
use App\Models\MitraTurunan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MitraTurunanController extends Controller
{
    /**
     * Display a listing of mitra turunans with AJAX support
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = MitraTurunan::with('mitra');

            // Search functionality
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nama_point', 'like', "%{$search}%")
                      ->orWhere('mitra_turunan_id', 'like', "%{$search}%")
                      ->orWhere('koordinat', 'like', "%{$search}%")
                      ->orWhere('type_point', 'like', "%{$search}%")
                      ->orWhereHas('mitra', function($mq) use ($search) {
                          $mq->where('nama_pt', 'like', "%{$search}%");
                      });
                });
            }

            // Filter by mitra
            if ($request->has('mitra_id') && !empty($request->mitra_id)) {
                $query->where('mitra_id', $request->mitra_id);
            }

            // Filter by type_point
            if ($request->has('type_point') && !empty($request->type_point)) {
                $query->where('type_point', $request->type_point);
            }

            // Filter by has file
            if ($request->has('has_file') && $request->has_file !== '') {
                if ($request->has_file === '1') {
                    $query->whereNotNull('nama_file');
                } else {
                    $query->whereNull('nama_file');
                }
            }

            // Sorting
            $sortField = $request->get('sort_field', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            $query->orderBy($sortField, $sortDirection);

            // Pagination
            $perPage = $request->get('per_page', 10);
            $points = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $points->items(),
                'pagination' => [
                    'current_page' => $points->currentPage(),
                    'last_page' => $points->lastPage(),
                    'per_page' => $points->perPage(),
                    'total' => $points->total(),
                    'from' => $points->firstItem(),
                    'to' => $points->lastItem(),
                ]
            ]);
        }

        // Load mitras for filter
        $mitras = Mitra::orderBy('nama_pt')->get();

        // Load distinct type_points for filter
        $typePoints = MitraTurunan::select('type_point')
            ->whereNotNull('type_point')
            ->distinct()
            ->orderBy('type_point')
            ->pluck('type_point');

        return view('turunans.indexMitraTurunan', compact('mitras', 'typePoints'));
    }

    /**
     * Store a newly created point via AJAX
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mitra_id' => 'required|exists:mitras,mitra_id',
            'koordinat' => 'required|string', // lat,lng format
            'nama_point' => 'nullable|string|max:255',
            'type_point' => 'nullable|string|max:100',
            'deskripsi' => 'nullable|string',
            'nama_file' => 'nullable|file|max:10240', // 10MB max
        ], [
            'mitra_id.required' => 'Mitra wajib dipilih',
            'mitra_id.exists' => 'Mitra tidak ditemukan',
            'koordinat.required' => 'Koordinat wajib diisi',
            'koordinat.regex' => 'Format koordinat harus: latitude,longitude (contoh: -6.2088,106.8456)',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $pointData = [
                'mitra_id' => $request->mitra_id,
                'koordinat' => $request->koordinat,
                'nama_point' => $request->nama_point,
                'type_point' => $request->type_point,
                'deskripsi' => $request->deskripsi,
            ];

            // Handle file upload
            if ($request->hasFile('nama_file')) {
                $file = $request->file('nama_file');
                $path = $file->store('mitra_files', 'public');
                $pointData['nama_file'] = $path;
            }

            $point = MitraTurunan::create($pointData);
            $point->load('mitra');

            return response()->json([
                'success' => true,
                'message' => 'Point berhasil dibuat',
                'data' => $point
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat point: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified point via AJAX
     */
    public function show(Request $request, $pointId)
    {
        try {
            $point = MitraTurunan::with('mitra')->where('mitra_turunan_id', $pointId)->firstOrFail();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $point
                ]);
            }

            return view('turunans.showMitraTurunan', compact('point'));

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Point tidak ditemukan'
                ], 404);
            }

            return redirect()->route('mitra-turunans.index')->with('error', 'Point tidak ditemukan');
        }
    }

    /**
     * Update the specified point via AJAX
     */
    public function update(Request $request, $pointId)
    {
        try {
            $point = MitraTurunan::where('mitra_turunan_id', $pointId)->firstOrFail();

            $validator = Validator::make($request->all(), [
                'mitra_id' => 'required|exists:mitras,mitra_id',
                'koordinat' => 'required|string|regex:/^-?\d+\.?\d*,-?\d+\.?\d*$/',
                'nama_point' => 'nullable|string|max:255',
                'type_point' => 'nullable|string|max:100',
                'deskripsi' => 'nullable|string',
                'nama_file' => 'nullable|file|max:10240',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $pointData = [
                'mitra_id' => $request->mitra_id,
                'koordinat' => $request->koordinat,
                'nama_point' => $request->nama_point,
                'type_point' => $request->type_point,
                'deskripsi' => $request->deskripsi,
            ];

            // Handle file upload
            if ($request->hasFile('nama_file')) {
                // Delete old file
                if ($point->nama_file && Storage::disk('public')->exists($point->nama_file)) {
                    Storage::disk('public')->delete($point->nama_file);
                }

                $file = $request->file('nama_file');
                $path = $file->store('mitra_files', 'public');
                $pointData['nama_file'] = $path;
            }

            $point->update($pointData);
            $point->load('mitra');

            return response()->json([
                'success' => true,
                'message' => 'Point berhasil diperbarui',
                'data' => $point
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui point: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified point via AJAX
     */
    public function destroy(Request $request, $pointId)
    {
        try {
            $point = MitraTurunan::where('mitra_turunan_id', $pointId)->firstOrFail();

            // Delete associated file
            if ($point->nama_file && Storage::disk('public')->exists($point->nama_file)) {
                Storage::disk('public')->delete($point->nama_file);
            }

            $point->delete();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Point berhasil dihapus'
                ]);
            }

            return redirect()->route('mitra-turunans.index')->with('success', 'Point berhasil dihapus');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat menghapus point: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('mitra-turunans.index')->with('error', 'Terjadi kesalahan saat menghapus point');
        }
    }

    /**
     * Upload KMZ/KML file and extract points
     */
    public function uploadKmz(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mitra_id' => 'required|exists:mitras,mitra_id',
            'kmz_file' => [
                'required',
                'file',
                'max:10240', // 10MB max
                function ($attribute, $value, $fail) {
                    $allowedExtensions = ['kmz', 'kml'];
                    $extension = strtolower($value->getClientOriginalExtension());
                    if (!in_array($extension, $allowedExtensions)) {
                        $fail('File harus berformat KMZ atau KML.');
                    }
                }
            ],
            'skip_duplicates' => 'nullable|in:on,off,1,0,true,false',
            'validate_coordinates' => 'nullable|in:on,off,1,0,true,false',
            'auto_detect_type' => 'nullable|in:on,off,1,0,true,false', // New option
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $file = $request->file('kmz_file');
            $mitraId = $request->mitra_id;
            $skipDuplicates = $request->has('skip_duplicates') && in_array($request->skip_duplicates, ['on', '1', 1, true]);
            $validateCoordinates = $request->has('validate_coordinates') && in_array($request->validate_coordinates, ['on', '1', 1, true]);
            $autoDetectType = $request->has('auto_detect_type') && in_array($request->auto_detect_type, ['on', '1', 1, true]);

            // Get mitra info
            $mitra = Mitra::find($mitraId);
            if (!$mitra) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mitra tidak ditemukan'
                ], 404);
            }

            $filename = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('temp_kmz', $filename, 'public');
            $fullPath = storage_path('app/public/' . $filePath);

            // Extract data from file
            $extractedData = [];
            if (strtolower($file->getClientOriginalExtension()) === 'kmz') {
                $extractedData = $this->extractKmzData($fullPath, $mitra, $autoDetectType);
            } else {
                $extractedData = $this->extractKmlData($fullPath, $mitra, $autoDetectType);
            }

            if (empty($extractedData)) {
                Storage::disk('public')->delete($filePath);
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data point yang ditemukan dalam file'
                ], 400);
            }

            // Filter duplicates if requested
            if ($skipDuplicates) {
                $extractedData = $this->filterDuplicatePoints($extractedData, $mitraId);
            }

            // Validate Indonesian coordinates if requested
            if ($validateCoordinates) {
                $extractedData = $this->validateIndonesianCoordinates($extractedData);
            }

            // Import to database
            $importedCount = 0;
            $skippedCount = 0;
            $typeDetectedCount = 0;
            $errors = [];

            foreach ($extractedData as $data) {
                try {
                    MitraTurunan::create($data);
                    $importedCount++;
                    if (!empty($data['type_point'])) {
                        $typeDetectedCount++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Error importing {$data['nama_point']}: " . $e->getMessage();
                    $skippedCount++;
                }
            }

            // Clean up temp file
            Storage::disk('public')->delete($filePath);

            $message = "KMZ berhasil diproses untuk mitra {$mitra->nama_pt}. ";
            $message .= "Berhasil import {$importedCount} dari " . count($extractedData) . " point.";

            if ($autoDetectType && $typeDetectedCount > 0) {
                $message .= " {$typeDetectedCount} point berhasil terdeteksi type-nya.";
            }

            if ($skippedCount > 0) {
                $message .= " {$skippedCount} point dilewati.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'imported_count' => $importedCount,
                    'skipped_count' => $skippedCount,
                    'type_detected_count' => $typeDetectedCount,
                    'total_found' => count($extractedData),
                    'mitra' => $mitra->nama_pt,
                    'errors' => $errors
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Extract data from KMZ file
     */
    private function extractKmzData($filePath, $mitra, $autoDetectType = false)
    {
        $zip = new \ZipArchive;

        if ($zip->open($filePath) !== TRUE) {
            throw new \Exception('Cannot open KMZ file');
        }

        $kmlContent = null;

        // Find KML file inside KMZ
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            if (pathinfo($filename, PATHINFO_EXTENSION) === 'kml') {
                $kmlContent = $zip->getFromIndex($i);
                break;
            }
        }

        $zip->close();

        if (!$kmlContent) {
            throw new \Exception('No KML file found in KMZ');
        }

        return $this->parseKmlContent($kmlContent, $mitra, $autoDetectType);
    }

    /**
     * Extract data from KML file
     */
    private function extractKmlData($filePath, $mitra, $autoDetectType = false)
    {
        $kmlContent = file_get_contents($filePath);
        return $this->parseKmlContent($kmlContent, $mitra, $autoDetectType);
    }

    /**
     * Parse KML content and extract points
     */
    private function parseKmlContent($kmlContent, $mitra, $autoDetectType = false)
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);

        if (!$dom->loadXML($kmlContent)) {
            throw new \Exception('Invalid KML format');
        }

        $extractedData = [];
        $placemarks = $dom->getElementsByTagName('Placemark');

        foreach ($placemarks as $placemark) {
            $name = '';
            $description = '';
            $coordinates = '';

            // Get name
            $nameNode = $placemark->getElementsByTagName('name')->item(0);
            if ($nameNode) {
                $name = trim($nameNode->nodeValue);
            }

            // Get description
            $descNode = $placemark->getElementsByTagName('description')->item(0);
            if ($descNode) {
                $description = trim($descNode->nodeValue);
            }

            // Get coordinates
            $coordNodes = $placemark->getElementsByTagName('coordinates');
            if ($coordNodes->length > 0) {
                $coordinates = trim($coordNodes->item(0)->nodeValue);
            }

            // Parse and validate coordinates
            if ($coordinates && $name) {
                $coords = $this->parseCoordinates($coordinates);
                if ($coords) {
                    $pointData = [
                        'mitra_id' => $mitra->mitra_id,
                        'nama_point' => $name,
                        'koordinat' => $coords['lat'] . ',' . $coords['lng'],
                        'deskripsi' => $description ?: null,
                        'type_point' => null,
                    ];

                    // Auto-detect type_point if enabled
                    if ($autoDetectType) {
                        $detectedType = $this->detectPointType($name, $description);
                        if ($detectedType) {
                            $pointData['type_point'] = $detectedType;
                        }
                    }

                    $extractedData[] = $pointData;
                }
            }
        }

        return $extractedData;
    }

    /**
     * Detect point type from name and description
     */
    private function detectPointType($name, $description = '')
    {
        // Convert to lowercase for case-insensitive matching
        $searchText = strtolower($name . ' ' . $description);

        // Define type patterns with keywords
        $typePatterns = [
            'tiang' => ['tiang', 'pole', 'tower', 'menara'],
            'odp' => ['odp', 'optical distribution point', 'distribution point'],
            'olt' => ['olt', 'optical line terminal', 'line terminal'],
            'onu' => ['onu', 'optical network unit', 'network unit'],
            'odc' => ['odc', 'optical distribution cabinet', 'cabinet'],
            'joint' => ['joint', 'sambungan', 'splice'],
            'manhole' => ['manhole', 'manhol', 'lubang'],
            'hanhole' => ['hanhole', 'hanhol'],
            'closure' => ['closure', 'penutup'],
            'splitter' => ['splitter', 'pembagi'],
        ];

        // Check each pattern
        foreach ($typePatterns as $type => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($searchText, $keyword) !== false) {
                    return $type;
                }
            }
        }

        return null; // No type detected
    }

    /**
     * Parse coordinate string from KML
     */
    private function parseCoordinates($coordinateString)
    {
        // KML format: longitude,latitude,altitude atau longitude,latitude
        $coords = explode(',', trim($coordinateString));

        if (count($coords) >= 2) {
            $lng = floatval(trim($coords[0]));
            $lat = floatval(trim($coords[1]));

            // Validate coordinate range
            if ($lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180) {
                return [
                    'lat' => $lat,
                    'lng' => $lng
                ];
            }
        }

        return null;
    }

    /**
     * Filter duplicate points based on coordinates
     */
    private function filterDuplicatePoints($extractedData, $mitraId)
    {
        // Get existing coordinates for this mitra
        $existingCoords = MitraTurunan::where('mitra_id', $mitraId)
            ->pluck('koordinat')
            ->toArray();

        return array_filter($extractedData, function ($data) use ($existingCoords) {
            return !in_array($data['koordinat'], $existingCoords);
        });
    }

    /**
     * Validate coordinates are within Indonesian territory
     */
    private function validateIndonesianCoordinates($extractedData)
    {
        // Indonesia coordinate bounds
        $bounds = [
            'min_lat' => -11.0,  // Pulau Rote
            'max_lat' => 6.0,    // Pulau Weh
            'min_lng' => 95.0,   // Pulau Weh
            'max_lng' => 141.0   // Papua
        ];

        return array_filter($extractedData, function ($data) use ($bounds) {
            $coords = explode(',', $data['koordinat']);
            if (count($coords) !== 2) return false;

            $lat = floatval($coords[0]);
            $lng = floatval($coords[1]);

            return $lat >= $bounds['min_lat'] && $lat <= $bounds['max_lat'] &&
                   $lng >= $bounds['min_lng'] && $lng <= $bounds['max_lng'];
        });
    }

    /**
     * Get points data for mapping/API
     */
    public function getMapData(Request $request)
    {
        try {
            $query = MitraTurunan::with('mitra');

            // Filter by mitra if specified
            if ($request->has('mitra_id') && !empty($request->mitra_id)) {
                $query->where('mitra_id', $request->mitra_id);
            }

            // Filter by type_point if specified
            if ($request->has('type_point') && !empty($request->type_point)) {
                $query->where('type_point', $request->type_point);
            }

            // Filter by bounds (for map viewport)
            if ($request->has('bounds')) {
                $bounds = $request->bounds;
                $query->whereRaw("
                    SUBSTRING_INDEX(koordinat, ',', 1) BETWEEN ? AND ? AND
                    SUBSTRING_INDEX(koordinat, ',', -1) BETWEEN ? AND ?
                ", [
                    $bounds['south'], $bounds['north'],
                    $bounds['west'], $bounds['east']
                ]);
            }

            $points = $query->get()->map(function ($point) {
                $coords = explode(',', $point->koordinat);
                return [
                    'id' => $point->mitra_turunan_id,
                    'name' => $point->nama_point,
                    'type' => $point->type_point,
                    'latitude' => floatval($coords[0] ?? 0),
                    'longitude' => floatval($coords[1] ?? 0),
                    'koordinat' => $point->koordinat,
                    'description' => $point->deskripsi,
                    'has_file' => !is_null($point->nama_file),
                    'file_url' => $point->file_url,
                    'mitra' => [
                        'id' => $point->mitra->mitra_id,
                        'name' => $point->mitra->nama_pt,
                        'color' => $point->mitra->warna_pt,
                        'icon' => $point->mitra->icon_url,
                    ],
                    'google_maps_url' => "https://www.google.com/maps?q={$point->koordinat}",
                    'created_at' => $point->created_at->toISOString(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $points
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading map data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Find nearest points to given coordinates
     */
    public function findNearestPoints(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'limit' => 'integer|min:1|max:50',
            'radius_km' => 'numeric|min:0.1|max:100',
            'mitra_id' => 'nullable|exists:mitras,mitra_id',
            'type_point' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $lat = $request->latitude;
            $lng = $request->longitude;
            $limit = $request->get('limit', 10);
            $radiusKm = $request->get('radius_km', 10);

            $query = MitraTurunan::with('mitra')
                ->selectRaw("
                    *,
                    (6371 * acos(cos(radians(?)) * cos(radians(SUBSTRING_INDEX(koordinat, ',', 1)))
                    * cos(radians(SUBSTRING_INDEX(koordinat, ',', -1)) - radians(?))
                    + sin(radians(?)) * sin(radians(SUBSTRING_INDEX(koordinat, ',', 1))))) AS distance_km
                ", [$lat, $lng, $lat]);

            if ($request->has('mitra_id') && !empty($request->mitra_id)) {
                $query->where('mitra_id', $request->mitra_id);
            }

            if ($request->has('type_point') && !empty($request->type_point)) {
                $query->where('type_point', $request->type_point);
            }

            $points = $query->having('distance_km', '<', $radiusKm)
                ->orderBy('distance_km', 'asc')
                ->limit($limit)
                ->get()
                ->map(function ($point) {
                    $coords = explode(',', $point->koordinat);
                    return [
                        'id' => $point->mitra_turunan_id,
                        'name' => $point->nama_point,
                        'type' => $point->type_point,
                        'latitude' => floatval($coords[0] ?? 0),
                        'longitude' => floatval($coords[1] ?? 0),
                        'distance_km' => round($point->distance_km, 2),
                        'mitra' => [
                            'id' => $point->mitra->mitra_id,
                            'name' => $point->mitra->nama_pt,
                            'color' => $point->mitra->warna_pt,
                        ]
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $points,
                'query_point' => [
                    'latitude' => $lat,
                    'longitude' => $lng
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error finding nearest points: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update point coordinates (for drag & drop)
     */
    public function updateCoordinates(Request $request, $pointId)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $point = MitraTurunan::where('mitra_turunan_id', $pointId)->firstOrFail();
            $newCoordinat = $request->latitude . ',' . $request->longitude;

            $point->update(['koordinat' => $newCoordinat]);

            return response()->json([
                'success' => true,
                'message' => 'Koordinat berhasil diperbarui',
                'data' => [
                    'id' => $point->mitra_turunan_id,
                    'koordinat' => $newCoordinat,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating coordinates: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk actions for points
     */
    public function bulkAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:delete',
            'point_ids' => 'required|array|min:1',
            'point_ids.*' => 'exists:mitra_turunans,mitra_turunan_id'
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
            $pointIds = $request->point_ids;
            $count = 0;

            switch ($action) {
                case 'delete':
                    foreach ($pointIds as $pointId) {
                        $point = MitraTurunan::where('mitra_turunan_id', $pointId)->first();
                        if ($point) {
                            // Delete associated file
                            if ($point->nama_file && Storage::disk('public')->exists($point->nama_file)) {
                                Storage::disk('public')->delete($point->nama_file);
                            }
                            $point->delete();
                            $count++;
                        }
                    }
                    $message = "Berhasil menghapus {$count} point";
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
     * Get statistics for points
     */
    public function statistics(Request $request)
    {
        try {
            $stats = [
                'total_points' => MitraTurunan::count(),
                'points_with_files' => MitraTurunan::whereNotNull('nama_file')->count(),
                'points_without_files' => MitraTurunan::whereNull('nama_file')->count(),
                'recent_points' => MitraTurunan::where('created_at', '>=', now()->subDays(7))->count(),
            ];

            // Points by mitra
            $pointsByMitra = Mitra::withCount('mitraTurunans')
                ->orderBy('mitra_turunans_count', 'desc')
                ->limit(10)
                ->get()
                ->map(function($mitra) {
                    return [
                        'mitra_id' => $mitra->mitra_id,
                        'mitra_name' => $mitra->nama_pt,
                        'points_count' => $mitra->mitra_turunans_count,
                        'color' => $mitra->warna_pt
                    ];
                });

            // Points by type
            $pointsByType = MitraTurunan::select('type_point')
                ->selectRaw('COUNT(*) as count')
                ->whereNotNull('type_point')
                ->groupBy('type_point')
                ->orderBy('count', 'desc')
                ->get()
                ->map(function($item) {
                    return [
                        'type' => $item->type_point,
                        'count' => $item->count
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'statistics' => $stats,
                    'points_by_mitra' => $pointsByMitra,
                    'points_by_type' => $pointsByType
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
     * Export points to CSV
     */
    public function export(Request $request)
    {
        try {
            $query = MitraTurunan::with('mitra');

            // Apply filters
            if ($request->has('mitra_id') && !empty($request->mitra_id)) {
                $query->where('mitra_id', $request->mitra_id);
            }

            if ($request->has('type_point') && !empty($request->type_point)) {
                $query->where('type_point', $request->type_point);
            }

            $points = $query->get();
            $filename = 'mitra_points_export_' . date('Y-m-d_H-i-s') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($points) {
                $file = fopen('php://output', 'w');

                // CSV headers
                fputcsv($file, [
                    'Point ID',
                    'Mitra',
                    'Nama Point',
                    'Type Point',
                    'Koordinat',
                    'Latitude',
                    'Longitude',
                    'Deskripsi',
                    'Has File',
                    'Created At'
                ]);

                // CSV data
                foreach ($points as $point) {
                    $coords = explode(',', $point->koordinat);
                    fputcsv($file, [
                        $point->mitra_turunan_id,
                        $point->mitra->nama_pt,
                        $point->nama_point,
                        $point->type_point,
                        $point->koordinat,
                        $coords[0] ?? '',
                        $coords[1] ?? '',
                        $point->deskripsi,
                        $point->nama_file ? 'Yes' : 'No',
                        $point->created_at->format('Y-m-d H:i:s')
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat export: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate route between two points (Foundation for LRM)
     */
    public function calculateRoute(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'point_a_id' => 'required|exists:mitra_turunans,mitra_turunan_id',
            'point_b_id' => 'required|exists:mitra_turunans,mitra_turunan_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $pointA = MitraTurunan::with('mitra')->findOrFail($request->point_a_id);
            $pointB = MitraTurunan::with('mitra')->findOrFail($request->point_b_id);

            $coordsA = explode(',', $pointA->koordinat);
            $coordsB = explode(',', $pointB->koordinat);

            $latA = floatval($coordsA[0]);
            $lngA = floatval($coordsA[1]);
            $latB = floatval($coordsB[0]);
            $lngB = floatval($coordsB[1]);

            // Calculate distance using Haversine formula
            $earthRadius = 6371; // km

            $dLat = deg2rad($latB - $latA);
            $dLng = deg2rad($lngB - $lngA);

            $a = sin($dLat/2) * sin($dLat/2) +
                cos(deg2rad($latA)) * cos(deg2rad($latB)) *
                sin($dLng/2) * sin($dLng/2);
            $c = 2 * atan2(sqrt($a), sqrt(1-$a));

            $distance = $earthRadius * $c; // Distance in km

            // Calculate bearing
            $dLng = deg2rad($lngB - $lngA);
            $y = sin($dLng) * cos(deg2rad($latB));
            $x = cos(deg2rad($latA)) * sin(deg2rad($latB)) -
                sin(deg2rad($latA)) * cos(deg2rad($latB)) * cos($dLng);
            $bearing = atan2($y, $x);
            $bearing = fmod((rad2deg($bearing) + 360), 360);

            return response()->json([
                'success' => true,
                'data' => [
                    'point_a' => [
                        'id' => $pointA->mitra_turunan_id,
                        'name' => $pointA->nama_point,
                        'type' => $pointA->type_point,
                        'coordinates' => $pointA->koordinat,
                        'latitude' => $latA,
                        'longitude' => $lngA,
                        'mitra' => $pointA->mitra->nama_pt
                    ],
                    'point_b' => [
                        'id' => $pointB->mitra_turunan_id,
                        'name' => $pointB->nama_point,
                        'type' => $pointB->type_point,
                        'coordinates' => $pointB->koordinat,
                        'latitude' => $latB,
                        'longitude' => $lngB,
                        'mitra' => $pointB->mitra->nama_pt
                    ],
                    'route_info' => [
                        'distance_km' => round($distance, 3),
                        'distance_m' => round($distance * 1000, 1),
                        'bearing_degrees' => round($bearing, 2),
                        'straight_line' => true, // For now, we only calculate straight line
                    ],
                    'google_maps_directions_url' => "https://www.google.com/maps/dir/{$pointA->koordinat}/{$pointB->koordinat}",
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error calculating route: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get points within radius (for coverage analysis)
     */
    public function getPointsInRadius(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius_km' => 'required|numeric|min:0.1|max:50',
            'mitra_id' => 'nullable|exists:mitras,mitra_id',
            'type_point' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $lat = $request->latitude;
            $lng = $request->longitude;
            $radiusKm = $request->radius_km;

            $query = MitraTurunan::with('mitra')
                ->selectRaw("
                    *,
                    (6371 * acos(cos(radians(?)) * cos(radians(SUBSTRING_INDEX(koordinat, ',', 1)))
                    * cos(radians(SUBSTRING_INDEX(koordinat, ',', -1)) - radians(?))
                    + sin(radians(?)) * sin(radians(SUBSTRING_INDEX(koordinat, ',', 1))))) AS distance_km
                ", [$lat, $lng, $lat]);

            if ($request->has('mitra_id') && !empty($request->mitra_id)) {
                $query->where('mitra_id', $request->mitra_id);
            }

            if ($request->has('type_point') && !empty($request->type_point)) {
                $query->where('type_point', $request->type_point);
            }

            $points = $query->having('distance_km', '<=', $radiusKm)
                ->orderBy('distance_km', 'asc')
                ->get()
                ->map(function ($point) {
                    $coords = explode(',', $point->koordinat);
                    return [
                        'id' => $point->mitra_turunan_id,
                        'name' => $point->nama_point,
                        'type' => $point->type_point,
                        'latitude' => floatval($coords[0] ?? 0),
                        'longitude' => floatval($coords[1] ?? 0),
                        'distance_km' => round($point->distance_km, 3),
                        'mitra' => [
                            'id' => $point->mitra->mitra_id,
                            'name' => $point->mitra->nama_pt,
                            'color' => $point->mitra->warna_pt,
                        ]
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'center_point' => [
                        'latitude' => $lat,
                        'longitude' => $lng,
                    ],
                    'radius_km' => $radiusKm,
                    'total_points' => $points->count(),
                    'points' => $points
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting points in radius: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Suggest optimal route between multiple points (Advanced LRM feature)
     */
    public function suggestOptimalRoute(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'point_ids' => 'required|array|min:2|max:10',
            'point_ids.*' => 'exists:mitra_turunans,mitra_turunan_id',
            'start_point_id' => 'required|exists:mitra_turunans,mitra_turunan_id',
            'algorithm' => 'in:nearest_neighbor,shortest_path',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $pointIds = $request->point_ids;
            $startPointId = $request->start_point_id;
            $algorithm = $request->get('algorithm', 'nearest_neighbor');

            $points = MitraTurunan::with('mitra')
                ->whereIn('mitra_turunan_id', $pointIds)
                ->get()
                ->keyBy('mitra_turunan_id');

            if (!$points->has($startPointId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Start point must be included in point_ids'
                ], 400);
            }

            // Simple nearest neighbor algorithm
            $route = $this->calculateNearestNeighborRoute($points, $startPointId);

            return response()->json([
                'success' => true,
                'data' => [
                    'algorithm' => $algorithm,
                    'start_point_id' => $startPointId,
                    'total_points' => count($pointIds),
                    'route' => $route['path'],
                    'total_distance_km' => $route['total_distance'],
                    'estimated_time_hours' => round($route['total_distance'] * 0.5, 2), // Rough estimate
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error calculating optimal route: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate nearest neighbor route (simple TSP solution)
     */
    private function calculateNearestNeighborRoute($points, $startPointId)
    {
        $visited = [$startPointId];
        $currentPoint = $points[$startPointId];
        $totalDistance = 0;
        $route = [];

        // Add start point to route
        $coords = explode(',', $currentPoint->koordinat);
        $route[] = [
            'id' => $currentPoint->mitra_turunan_id,
            'name' => $currentPoint->nama_point,
            'type' => $currentPoint->type_point,
            'latitude' => floatval($coords[0]),
            'longitude' => floatval($coords[1]),
            'mitra' => $currentPoint->mitra->nama_pt,
            'distance_from_previous' => 0
        ];

        while (count($visited) < $points->count()) {
            $nearestDistance = INF;
            $nearestPointId = null;

            $currentCoords = explode(',', $currentPoint->koordinat);
            $currentLat = floatval($currentCoords[0]);
            $currentLng = floatval($currentCoords[1]);

            // Find nearest unvisited point
            foreach ($points as $pointId => $point) {
                if (in_array($pointId, $visited)) continue;

                $pointCoords = explode(',', $point->koordinat);
                $pointLat = floatval($pointCoords[0]);
                $pointLng = floatval($pointCoords[1]);

                $distance = $this->calculateDistance($currentLat, $currentLng, $pointLat, $pointLng);

                if ($distance < $nearestDistance) {
                    $nearestDistance = $distance;
                    $nearestPointId = $pointId;
                }
            }

            if ($nearestPointId) {
                $visited[] = $nearestPointId;
                $totalDistance += $nearestDistance;
                $currentPoint = $points[$nearestPointId];

                $coords = explode(',', $currentPoint->koordinat);
                $route[] = [
                    'id' => $currentPoint->mitra_turunan_id,
                    'name' => $currentPoint->nama_point,
                    'type' => $currentPoint->type_point,
                    'latitude' => floatval($coords[0]),
                    'longitude' => floatval($coords[1]),
                    'mitra' => $currentPoint->mitra->nama_pt,
                    'distance_from_previous' => round($nearestDistance, 3)
                ];
            }
        }

        return [
            'path' => $route,
            'total_distance' => round($totalDistance, 3)
        ];
    }

    /**
     * Calculate distance between two coordinates using Haversine formula
     */
    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat/2) * sin($dLat/2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLng/2) * sin($dLng/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $earthRadius * $c;
    }

    /**
     * Get available point types for dropdown/filter
     */
    public function getPointTypes(Request $request)
    {
        try {
            $types = MitraTurunan::select('type_point')
                ->whereNotNull('type_point')
                ->distinct()
                ->orderBy('type_point')
                ->pluck('type_point');

            return response()->json([
                'success' => true,
                'data' => $types
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting point types: ' . $e->getMessage()
            ], 500);
        }
    }
}
