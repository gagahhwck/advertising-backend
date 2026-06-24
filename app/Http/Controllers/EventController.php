<?php

namespace App\Http\Controllers;

use App\Models\Advertising\Event;
use App\Models\Advertising\Schedule;
use Carbon\Carbon;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    /**
    * @response array{"current_page": int, "data": object[], "first_page_url": "string", "from": int, "last_page": int, "last_page_url": "string", "links": object[], "next_page_url": "string", "path": "string", "per_page": int, "prev_page_url": "string", "to": int, "total": int}
    */
    #[QueryParameter('q', type: 'string', format: 'text', description: 'Search query')]
    #[QueryParameter('include', type: 'array<string>', format: 'csv', example: '["creator",contents"]', description: '<p style="margin-bottom:0">Relationships to include:</p>
        <ul style="margin-top:0px;">
        <li>creator</li>
        <li>updater</li>
        <li>contents</li>
        </ul>
    ')]
    #[QueryParameter('joins', type: 'array<string>', format: 'csv', example: '["table,on1,condition,on2,type"]', description: 'Relationships to join')]
    #[QueryParameter('fields', type: 'array<string>', format: 'csv', example: '["id","name","created_at"]', description: 'Columns to select similar to SQL queries')]
    #[QueryParameter('sort_by', type: 'string', format: 'text', description: 'Column to sort by.<br>
        if want to sort from "joins" use dot notation (e.g. contents.title).<br>
        if want to sort from "include" use arrow notation (e.g. contents->title)
    ')]
    #[QueryParameter('sort_type', type: 'string', format: 'text', description: 'Sort order (asc or desc)')]
    #[QueryParameter('per_page', type: 'integer', format: 'int32', description: 'Number of items per page')]
    #[QueryParameter('page', type: 'integer', format: 'int32', description: 'Page number')]
    #[QueryParameter('filters', type: 'object', format: 'json', example: '{"name":"test"}', description: 'Column filters as key-value pairs if want to filters on "include" use arrow notation (e.g. template->name)')]
    #[QueryParameter('wheres', type: 'array<string>', format: 'csv', description: 'Where conditions as array of strings', example: '["column,operator,value"]')]
    public function index()
    {
        return Event::list();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'                 => ['required','unique:events,title'],
            'description'           => ['nullable','string'],
            'event_category_id'     => ['required', 'exists:event_categories,id'],
            'schedule'              => ['sometimes', 'array'],
            'schedule.*.start_time' => ['required_with:schedule', 'date_format:Y-m-d H:i:s'],
            'schedule.*.end_time'   => ['required_with:schedule', 'date_format:Y-m-d H:i:s'],
            'start_date'            => ['required_without:schedule','date_format:Y-m-d H:i:s'],
            'end_date'              => ['required_without:schedule','date_format:Y-m-d H:i:s'],
        ],[
            'title' => 'The Title is required minimum 2 Character',
            'schedule.array' => 'The schedule field must be an array.',
            'schedule.*.start_time.required_with' => 'The schedule.*.start_time field is required when schedule is present.',
            'schedule.*.start_time.date_format' => 'The schedule.*.start_time does not match the format Y-m-d H:i:s.',
            'schedule.*.end_time.required_with' => 'The schedule.*.end_time field is required when schedule is present.',
            'schedule.*.end_time.date_format' => 'The schedule.*.end_time does not match the format Y-m-d H:i:s.',
            'start_date.required_without' => 'Provide start_date and end_date when schedule is not provided.',
            'end_date.required_without' => 'Provide start_date and end_date when schedule is not provided.',
        ]);

        $schedulesToCheck = [];
        if (!empty($data['schedule']) && is_array($data['schedule'])) {
            $schedulesToCheck = $data['schedule'];
        } else {
            $schedulesToCheck = [[
                'start_time' => $data['start_date'] ?? null,
                'end_time' => $data['end_date'] ?? null,
            ]];
        }

        if ($conflict = $this->validateScheduleConflicts($schedulesToCheck, null)) {
            return response()->json([
                'success' => false,
                'message' => $conflict,
            ], 422);
        }

        $data['created_by'] = Auth::user()->username;

        $event = Event::create($data);

        // Persist schedules: use provided schedule array or single start_date/end_date
        if (!empty($data['schedule']) && is_array($data['schedule'])) {
            foreach ($data['schedule'] as $schedule) {
                $event->schedules()->create([
                    'event_id' => $event->id,
                    'start_at' => $schedule['start_time'],
                    'end_at'   => $schedule['end_time'],
                ]);
            }
        } else {
            $event->schedules()->create([
                'event_id' => $event->id,
                'start_at' => $data['start_date'] ?? null,
                'end_at'   => $data['end_date'] ?? null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Event Successfully Created',
            'eventid' => $event->id,  
            'data'    => $event
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Event $event)
    {
        if ($request->has('include') && is_array($request->query('include'))) {
            $event->load($request->query('include'));
        }

        return response()->json([
            'success' => true,
            'data' => $event
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $event = Event::findOrFail($id);

        $data = $request->validate([
            'title'                 => ['required','min:2','unique:events,title,' . $event->id],
            'description'           => ['nullable','string'],
            'event_category_id'     => ['required', 'exists:event_categories,id'],
            'schedule'              => ['sometimes', 'array'],
            'schedule.*.start_time' => ['required_with:schedule', 'date_format:Y-m-d H:i:s'],
            'schedule.*.end_time'   => ['required_with:schedule', 'date_format:Y-m-d H:i:s'],
        ],[
            'title.required' => 'The Title is required minimum 2 Character',
            'title.min' => 'The Title is required minimum 2 Character',
            'schedule' => 'The schedule field must be an array.',
            'schedule.*.start_time.required_with' => 'The schedule.*.start_time field is required when schedule is present.',
            'schedule.*.start_time.date_format' => 'The schedule.*.start_time does not match the format Y-m-d H:i:s.',
            'schedule.*.end_time.required_with' => 'The schedule.*.end_time field is required when schedule is present.',
            'schedule.*.end_time.date_format' => 'The schedule.*.end_time does not match the format Y-m-d H:i:s.',
        ]);

        if (isset($data['schedule'])) {
            if ($conflict = $this->validateScheduleConflicts($data['schedule'], $event->id)) {
                return response()->json([
                    'success' => false,
                    'message' => $conflict,
                ], 422);
            }
        }

        $data['updated_by'] = Auth::user()->username;

        $event->update($data);

        if (isset($data['schedule'])) {
            $event->schedules()->delete();

            foreach ($data['schedule'] as $schedule) {
                $event->schedules()->create([
                    'event_id' => $event->id,
                    'start_at' => $schedule['start_time'],
                    'end_at'   => $schedule['end_time'],
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Event Successfully Updated',
            'eventid' => $event->id,
            'data'    => $event
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $event = Event::findOrFail($id);

        $event->schedules()->delete();
        $event->delete();

        return response()->json([
            'success' => true,
            'message' => 'Event Successfully Deleted'
        ]);
    }

    protected function validateScheduleConflicts(array $schedules, ?int $excludeEventId = null): ?string
    {
        $parsedSchedules = [];

        foreach ($schedules as $index => $schedule) {
            if (empty($schedule['start_time']) || empty($schedule['end_time'])) {
                return 'Each schedule entry must include both start_time and end_time.';
            }

            $start = Carbon::createFromFormat('Y-m-d H:i:s', $schedule['start_time']);
            $end = Carbon::createFromFormat('Y-m-d H:i:s', $schedule['end_time']);

            if ($start->gte($end)) {
                return "Schedule item #{$index} start_time must be before end_time.";
            }

            $parsedSchedules[] = [
                'start' => $start,
                'end' => $end,
            ];
        }

        foreach ($parsedSchedules as $i => $current) {
            foreach ($parsedSchedules as $j => $other) {
                if ($i === $j) {
                    continue;
                }

                if ($current['start']->lt($other['end']) && $other['start']->lt($current['end'])) {
                    return "Schedule entries at index {$i} and {$j} overlap each other.";
                }
            }
        }

        foreach ($parsedSchedules as $schedule) {
            $query = Schedule::where('start_at', '<', $schedule['end'])
                ->where('end_at', '>', $schedule['start']);

            if ($excludeEventId) {
                $query->where('event_id', '!=', $excludeEventId);
            }

            if ($query->exists()) {
                return sprintf(
                    'Schedule %s - %s conflicts with an existing event schedule.',
                    $schedule['start']->format('Y-m-d H:i:s'),
                    $schedule['end']->format('Y-m-d H:i:s')
                );
            }
        }

        return null;
    }
}
