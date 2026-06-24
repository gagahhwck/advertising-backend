<?php

namespace App\Http\Controllers;

use App\Models\Advertising\EventLocation;
use App\Models\Assets\LocationAsset;
use Illuminate\Http\Request;

class EventLocationController extends Controller
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
            'event_id'          => ['required','integer','exists:events,id'],
            'event_location'    => ['required', 'array', 'min:1'],
            'event_location.*'  => ['required', 'integer', function ($attribute, $value, $fail) {
                if (!LocationAsset::where('id', $value)->exists()) {
                    $fail("The selected {$attribute} is invalid.");
                }
            }],
        ]);

        $contentLocations = collect($data['event_location'])->map(function ($locationId) use ($data) {
            return EventLocation::create([
                'event_id' => $data['event_id'],
                'location_id' => $locationId,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Event Location Successfully Created',
            'data'    => $contentLocations
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(EventLocation $contentLocation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EventLocation $event_location)
    {
        $data = $request->validate([
            'event_location' => ['required', 'array', 'min:1'],
            'event_location.*' => ['required', 'integer', function ($attribute, $value, $fail) {
                if (!LocationAsset::where('id', $value)->exists()) {
                    $fail("The selected {$attribute} is invalid.");
                }
            }],
        ]);

        $locationIds = array_unique($data['event_location']);

        $existingLocationIds = EventLocation::where('event_id', $event_location->event_id)
            ->whereIn('location_id', $locationIds)
            ->pluck('location_id')
            ->all();

        $created = [];

        foreach ($locationIds as $locationId) {
            if (!in_array($locationId, $existingLocationIds, true)) {
                $created[] = EventLocation::create([
                    'event_id' => $event_location->event_id,
                    'location_id' => $locationId,
                ]);
            }
        }

        return response()->json([
            'success'   => true,
            'message'   => 'Event Location Successfully Updated',
            'data'      => $created,
            'skipped'   => $existingLocationIds,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EventLocation $event_location)
    {
        $event_location->delete();

        return response()->json([
            'success'   => true,
            'message'   => 'Event Location Successfully Updated',
        ]);
    }
}
