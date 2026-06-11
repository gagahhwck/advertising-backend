<?php

namespace App\Http\Controllers;

use App\Models\Advertising\EventCategory;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\Request;

class EventCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return EventCategory::list();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255','unique:event_categories,name'],
            'is_active' => ['required','boolean']
        ],[
            'name.required' => 'The Name is Required',
            'name.unique'  => 'The Name is already exist, try another Name',
            'is_active' => 'The Status is Required'
        ]);

        $category = EventCategory::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Category Successfully Created',
            'data'    => $category
        ], 200);
    }

    /**
    * Display the specified resource.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  EventCategory  $eventCategory
    * @return \Illuminate\Http\Response
    */
    #[QueryParameter('include', type: 'array<string>', format: 'csv', description: 'Relationships to include', example: '["creator"]')]
    public function show(Request $request, EventCategory $eventCategory)
    {
        if ($request->has('include') && is_array($request->query('include'))) {
            $eventCategory->load($request->query('include'));
        }

        return response()->json([
            'success' => true,
            'data' => $eventCategory
        ]);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EventCategory $eventCategory)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'is_active' => ['required','boolean']
        ],[
            'name.required' => 'The Name is Required',
            'is_active' => 'The Status is Required'
        ]);

        $eventCategory->update($data);
        return response()->json([
            'success' => true,
            'message' => 'Category Successfully Updated'
        ], 200);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EventCategory $eventCategory)
    {
        if ($eventCategory->events()->exists()) {
            return response()->json([
                'message' => 'The Category have existing events',
            ], 403);
        }

        $eventCategory->delete();
        return response()->json([
            'success' => true,
            'message' => 'Category Successfully Deleted'
        ], 200);
    }
}
