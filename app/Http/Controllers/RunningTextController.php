<?php

namespace App\Http\Controllers;

use App\Events\RunningTextCreated;
use App\Events\RunningTextUpdated;
use App\Models\Advertising\RunningText;
use App\Models\Assets\LocationAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RunningTextController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return RunningText::list();
    }

    /**
     * Display all of the resource.
     */
    public function running(Request $request)
    {
        $query = RunningText::where('is_active', 1)
            ->where('start_at', '<=', now())
            ->where('end_at', '>=', now());

        if ($request->has('location_id')) {
            $locationIds = $request->input('location_id');

            if (is_array($locationIds)) {
                $query->whereIn('location_id', $locationIds);
            } else {
                $query->where('location_id', $locationIds);
            }
        }

        $response = $query->orderBy('priority', 'asc')->get();

        return response()->json($response);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'location_id'   => ['nullable', 'integer', function ($attribute, $value, $fail) {
                if (!LocationAsset::where('id', $value)->exists()) {
                    $fail("The selected {$attribute} is invalid.");
                }
            }],
            'event_id'      => ['nullable','integer','exists:events,id'],
            'text'          => ['required', function ($attribute, $value, $fail) {
                if (str_word_count($value) > 75) {
                    $fail('The '.$attribute.' may not have more than 75 words.');
                }
            }],
            'priority'      => ['required','boolean'],
            'is_active'     => ['required','boolean']
        ]);

        $data['created_by'] = Auth::user()->username;

        $text = RunningText::create($data);
        event(new RunningTextCreated($text));

        return response()->json([
            'success'   => true,
            'message'   => 'Running Text Successfully Created',
            'data'      => $text
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, RunningText $running_text)
    {
        if ($request->has('include') && is_array($request->query('include'))) {
            $running_text->load($request->query('include'));
        }

        return response()->json([
            'success' => true,
            'data' => $running_text
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RunningText $running_text)
    {
        $data = $request->validate([
            'location_id'   => ['nullable', 'integer', function ($attribute, $value, $fail) {
                if (!LocationAsset::where('id', $value)->exists()) {
                    $fail("The selected {$attribute} is invalid.");
                }
            }],
            'event_id'      => ['nullable','integer','exists:events,id'],
            'text'          => ['required', function ($attribute, $value, $fail) {
                if (str_word_count($value) > 75) {
                    $fail('The '.$attribute.' may not have more than 75 words.');
                }
            }],
            'priority'      => ['required','boolean'],
            'is_active'     => ['required','boolean']
        ]);

        $data['updated_by'] = Auth::user()->username;
        $running_text->update($data);
        Event(new RunningTextUpdated($running_text));
        return response()->json([
            'success'   => true,
            'message'   => 'Running Text Successfully Updated',
            'data'      => $running_text
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RunningText $running_text)
    {
        $running_text->delete();
        event(new RunningTextUpdated($running_text, 'deleted'));
        return response()->json([
            'success'   => true,
            'message'   => 'Running Text Successfully Deleted',
        ]);
    }
}
