<?php

namespace App\Http\Controllers;

use App\Models\Advertising\Template;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TemplateController extends Controller
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
        return Template::list();
    }

    /**
     * Store a newly created resource in storage.
     *
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                  => ['required'],
            'type'                  => ['required','in:flower_board,advertisement'],
            'landscape_background'  => ['required','image','mimes:jpg,png,jpeg,webp'],
            'portrait_background'   => ['required','image','mimes:jpg,png,jpeg,webp'],
            'template_json'         => ['nullable','json']
        ],[
            'type'                  => 'The Type must be one of the following: flower_board,advertisement'
        ]);

        $template_folder = 'advertising/template';
        $landscapeFile = $request->file('landscape_background');
        $portraitFile = $request->file('portrait_background');

        if ($landscapeFile) {
            $data['landscape_background'] = $landscapeFile->store($template_folder, 's3');
        }

        if ($portraitFile) {
            $data['portrait_background'] = $portraitFile->store($template_folder, 's3');
        }

        $data['created_by'] = Auth::user()->username;

        $template = Template::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Template Successfully Created',
            'data'    => $template
        ]);
    }

    /**
    * Display the specified resource.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  Template  $template
    * @return \Illuminate\Http\Response
    */
    #[QueryParameter('include', type: 'array<string>', format: 'csv', description: 'Relationships to include', example: '["creator"]')]
    public function show(Request $request, Template $template)
    {
        if ($request->has('include') && is_array($request->query('include'))) {
            $template->load($request->query('include'));
        }
        return response()->json([
            'success' => true,
            'data'    => $template
        ],200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Template $template)
    {
        foreach (['landscape_background', 'portrait_background', 'template_json'] as $field) {
            if ($request->has($field) && $request->input($field) === '') {
                $request->merge([$field => null]);
            }
        }

        $data = $request->validate([
            'name'                  => ['required','string'],
            'type'                  => ['required','in:flower_board,advertisement'],
            'landscape_background'  => ['nullable','image','mimes:jpg,png,jpeg,webp'],
            'portrait_background'   => ['nullable','image','mimes:jpg,png,jpeg,webp'],
            'template_json'         => ['nullable','json'],
        ],[
            'type'                  => 'The Type must be one of the following: flower_board,advertisement'
        ]);

        $template_folder = 'advertising/template';
        $landscapeFile = $request->file('landscape_background');
        $portraitFile = $request->file('portrait_background');

        if ($landscapeFile) {
            if ($template->landscape_background && Storage::disk('s3')->exists($template->landscape_background)) {
                Storage::disk('s3')->delete($template->landscape_background);
            }

            $filename_landscape = pathinfo($landscapeFile->getClientOriginalName(), PATHINFO_FILENAME) . ' - ' . date('YmdHis') . '.' . $landscapeFile->getClientOriginalExtension();
            $data['landscape_background'] = $landscapeFile->storeAs($template_folder, $filename_landscape, 's3');
        }

        if ($portraitFile) {
            if ($template->portrait_background && Storage::disk('s3')->exists($template->portrait_background)) {
                Storage::disk('s3')->delete($template->portrait_background);
            }

            $filename_portrait = pathinfo($portraitFile->getClientOriginalName(), PATHINFO_FILENAME) . ' - ' . date('YmdHis') . '.' . $portraitFile->getClientOriginalExtension();
            $data['portrait_background'] = $portraitFile->storeAs($template_folder, $filename_portrait, 's3');
        }

        $data['updated_by'] = now();

        $template->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Template Successfully Updated',
            'data'    => $template
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Template $template)
    {
        if (Storage::disk('s3')->exists($template->landscape_background)) {
            Storage::disk('s3')->delete($template->landscape_background);
        }
        if (Storage::disk('s3')->exists($template->portrait_background)) {
            Storage::disk('s3')->delete($template->portrait_background);
        }
        $template->delete();
        return response()->json([
            'success' => true,
            'message' => 'Template Successfully Deleted'
        ]);
    }
}
