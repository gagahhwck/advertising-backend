<?php

namespace App\Http\Controllers;

use App\Models\Advertising\Content;
use App\Models\Advertising\MediaFile;
use Illuminate\Http\Request;

class MediaFileController extends Controller
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
            'content_id'    => ['required','exists:contents,id'],
            'orientation'   => ['required','in:potrait,landscape,both'],
            'file_path'     => ['required','file'],
            'thumbnail'     => ['nullable','file']
        ]);

        if ($request->hasFile('file_path')) {
            $content = Content::findOrFail($data['content_id']);
            if (!$content) {
                return response()->json([
                    'success' => true,
                    'message' => 'Content Not Found',
                ],404);
            }
            $file = $request->file('file_path');
            $data['file_name'] = $file->getClientOriginalName();
            $extension = strtolower($file->getClientOriginalExtension());
            $videoExtensions = ['mp4', 'mov', 'avi', 'mkv', 'wmv', 'webm'];
            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'];

            if (in_array($extension, $videoExtensions, true)) {
                $data['media_type'] = 'video';
            } elseif (in_array($extension, $imageExtensions, true)) {
                $data['media_type'] = 'image';
            }

            $folder_media = 'ads/'.$content->event->title.'/'.$content->title;
            $folder_thumbnail = 'ads/'.$content->event->title.'/'.$content->title.'/thumbnail';
            $data['file_path'] = $file->store($folder_media, 's3');

            if ($request->hasFile('thumbnail')) {
                $data['thumbnail'] = $request->file('thumbnail')->store($folder_thumbnail, 's3');
            }
        }

        $mediaFile = MediaFile::create($data);

        return response()->json([
            'success'   => true,
            'message'   => $mediaFile->thumbnail ? 'Media Successfully Created with Thumbnail' : 'Media Successfully Created'
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
    public function destroy(string $id)
    {
        //
    }
}
