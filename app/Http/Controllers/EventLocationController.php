<?php

namespace App\Http\Controllers;

use App\Models\Advertising\EventLocation;
use App\Models\Assets\LocationAsset;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\Request;

class EventLocationController extends Controller
{
    /**
    * @response array{"current_page": int, "data": object[], "first_page_url": "string", "from": int, "last_page": int, "last_page_url": "string", "links": object[], "next_page_url": "string", "path": "string", "per_page": int, "prev_page_url": "string", "to": int, "total": int}
    */
    #[QueryParameter('q', type: 'string', format: 'text', description: 'Search query')]
    #[QueryParameter('include', type: 'array<string>', format: 'csv', example: '["creator","location","event","event.contents.media_files"]', description: '<p style="margin-bottom:0">Relationships to include:</p>
        <ul style="margin-top:0px;">
        <li>creator</li>
        <li>updater</li>
        <li>deleter</li>
        <li>template</li>
        <li>event</li>
        <li>media_files</li>
        <li>payments</li>
        <li>playback_logs</li>
        <li>content_locations</li>
        </ul>
    ')]
    #[QueryParameter('joins', type: 'array<string>', format: 'csv', example: '["table,on1,condition,on2,type"]', description: 'Relationships to join')]
    #[QueryParameter('fields', type: 'array<string>', format: 'csv', example: '["id","name","created_at"]', description: 'Columns to select similar to SQL queries')]
    #[QueryParameter('sort_by', type: 'string', format: 'text', description: 'Column to sort by.<br>
        if want to sort from "joins" use dot notation (e.g. template.name).<br>
        if want to sort from "include" use arrow notation (e.g. template->name)
    ')]
    #[QueryParameter('sort_type', type: 'string', format: 'text', description: 'Sort order (asc or desc)')]
    #[QueryParameter('per_page', type: 'integer', format: 'int32', description: 'Number of items per page')]
    #[QueryParameter('page', type: 'integer', format: 'int32', description: 'Page number')]
    #[QueryParameter('filters', type: 'object', format: 'json', example: '{"name":"test"}', description: 'Column filters as key-value pairs if want to filters on "include" use arrow notation (e.g. template->name)')]
    #[QueryParameter('wheres', type: 'array<string>', format: 'csv', description: 'Where conditions as array of strings', example: '["column,operator,value"]')]
    public function index()
    {
        return EventLocation::list();
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
