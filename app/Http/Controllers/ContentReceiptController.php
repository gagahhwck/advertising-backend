<?php

namespace App\Http\Controllers;

use App\Models\Advertising\Content;
use App\Models\Advertising\ContentReceipts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContentReceiptController extends Controller
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
            'content_id'    => ['required', 'integer', 'exists:contents,id'],
            'title'         => ['nullable', 'string'],
            'description'   => ['nullable', 'string'],
            'to'            => ['required', 'array'],
            'from'          => ['nullable','string']
        ]);

        $content = Content::findOrFail($data['content_id']);

        $rows = [];

        foreach ($data['to'] as $recipient) {
            $rows[] = ContentReceipts::create([
                'content_id'  => $data['content_id'],
                'title'       => $data['title'] ?? $content->title,
                'description' => $data['description'] ?? $content->description,
                'to'          => $recipient,
                'from'        => $data['from'] ?? env('MAIL_USERNAME'),
                'status'      => 'created',
                'created_by'  => Auth::user()->username
            ]);
        }

        // foreach ($rows as $row) {
            
        // }

        return response()->json([
            'message' => 'Notification queued successfully',
            'total'   => count($rows),
        ]); 
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy()
    {
        // 
    }
}
