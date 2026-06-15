<?php

namespace App\Http\Controllers;

use App\Models\Advertising\ContentLocation;
use App\Models\Assets\LocationAsset;
use Illuminate\Http\Request;

class ContentLocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'content_id'    => ['required','integer','exists:contents,id'],
            'content_locations' => ['required', 'array', 'min:1'],
            'content_locations.*' => ['required', 'integer', function ($attribute, $value, $fail) {
                if (!LocationAsset::where('id', $value)->exists()) {
                    $fail("The selected {$attribute} is invalid.");
                }
            }],
        ]);

        $contentLocations = collect($data['content_locations'])->map(function ($locationId) use ($data) {
            return ContentLocation::create([
                'content_id' => $data['content_id'],
                'location_id' => $locationId,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Content Locations Successfully Created',
            'data'    => $contentLocations
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(ContentLocation $contentLocation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ContentLocation $contentLocation)
    {
        $data = $request->validate([
            'content_locations' => ['required', 'array', 'min:1'],
            'content_locations.*' => ['required', 'integer', function ($attribute, $value, $fail) {
                if (!LocationAsset::where('id', $value)->exists()) {
                    $fail("The selected {$attribute} is invalid.");
                }
            }],
        ]);

        $locationIds = array_unique($data['content_locations']);

        $existingLocationIds = ContentLocation::where('content_id', $contentLocation->content_id)
            ->whereIn('location_id', $locationIds)
            ->pluck('location_id')
            ->all();

        $created = [];

        foreach ($locationIds as $locationId) {
            if (!in_array($locationId, $existingLocationIds, true)) {
                $created[] = ContentLocation::create([
                    'content_id' => $contentLocation->content_id,
                    'location_id' => $locationId,
                ]);
            }
        }

        return response()->json([
            'success'   => true,
            'message'   => 'Content Locations Successfully Updated',
            'data'      => $created,
            'skipped'   => $existingLocationIds,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ContentLocation $contentLocation)
    {
        $contentLocation->delete();

        return response()->json([
            'success'   => true,
            'message'   => 'Content Location Successfully Updated',
        ]);
    }
}
