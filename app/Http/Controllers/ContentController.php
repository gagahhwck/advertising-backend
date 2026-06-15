<?php

namespace App\Http\Controllers;

use App\Models\Advertising\Content;
use App\Models\Advertising\Event;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ContentController extends Controller
{
    /**
    * @response array{"current_page": int, "data": object[], "first_page_url": "string", "from": int, "last_page": int, "last_page_url": "string", "links": object[], "next_page_url": "string", "path": "string", "per_page": int, "prev_page_url": "string", "to": int, "total": int}
    */
    #[QueryParameter('q', type: 'string', format: 'text', description: 'Search query')]
    #[QueryParameter('include', type: 'array<string>', format: 'csv', example: '["creator","template","event","media_files"]', description: '<p style="margin-bottom:0">Relationships to include:</p>
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
        return Content::list();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'template_id'           => ['nullable','integer', 'exists:templates,id'],
            'event_id'              => ['required','integer','exists:events,id'],
            'title'                 => ['nullable', 'string', 'max:255'],
            'description'           => ['nullable', 'string'],
            'display_duration'      => ['nullable', 'integer', 'min:10'],
            'priority'              => ['nullable', 'integer', 'min:1'],
            'is_active'             => ['required', 'boolean'],
            'status'                => ['required', 'in:draft,pending,approved,rejected,scheduled,active,expired'],
        ]);

        // Event title for content title if empty
        if (empty($data['title']) && !empty($data['event_id'])) {
            $event = Event::findOrFail($data['event_id']);
            $count = Content::where('event_id', $event->id)->count() + 1;
            $data['title'] = $event->title.' #'.$count;
        }        

        $data['display_duration'] = $data['display_duration'] ?? 10;
        $data['priority'] = $data['priority'] ?? 1;
        $data['created_by'] = Auth::user()->username ?? 'system';

        $content = Content::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Content Successfully saved',
            'data'    => $content 
        ]);
        
    }

    /**
     * Display the specified resource.
     */
    public function show(Content $content)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Content $content)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Content $content)
    {
        $data = $request->validate([
            'template_id'           => ['nullable','integer', 'exists:templates,id'],
            'event_id'              => ['required','integer','exists:events,id'],
            'title'                 => ['required', 'string', 'max:255'],
            'description'           => ['nullable', 'string'],
            'orientation'           => ['required', 'in:portrait,landscape,both'],
            'display_duration'      => ['nullable', 'integer', 'min:10'],
            'priority'              => ['nullable', 'integer', 'min:1'],
            'auto_resize'           => ['required', 'boolean'],
            'is_active'             => ['required', 'boolean'],
            'status'                => ['required', 'in:draft,pending,approved,rejected,scheduled,active,expired'],
        ]);

        // preserve existing values if not provided
        $data['display_duration'] = $data['display_duration'] ?? $content->display_duration ?? 10;
        $data['priority'] = $data['priority'] ?? $content->priority ?? 1;
        $data['updated_by'] = Auth::user()->username ?? 'system';

        $content->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Content Successfully updated',
            'data'    => $content
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Content $content)
    {
        // delete media files from storage (support either 'path' or 'file_path' column)
        foreach ($content->media_files as $media) {
            $filePath = $media->path ?? $media->file_path ?? null;
            if ($filePath && Storage::disk('s3')->exists($filePath)) {
                Storage::disk('s3')->delete($filePath);
            }
            // remove media record
            $media->delete();
        }

        // remove related records
        if ($content->content_locations()) {
            $content->content_locations()->delete();
        }
        if ($content->content_receipts()) {
            $content->content_receipts()->delete();
        }

        // delete the content itself
        $content->delete();

        return response()->json([
            'success' => true,
            'message' => 'Content Successfully deleted'
        ]);
    }
}
