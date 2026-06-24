<?php

namespace App\Http\Controllers;

use App\Models\Advertising\Content;
use App\Models\Advertising\MediaFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            'orientation'   => ['required','in:portrait,landscape,both'],
            'file_path'     => ['required','file'],
            'thumbnail'     => ['nullable','file']
        ]);

        // hanya boleh punya 2 konten
        $existingMedia = MediaFile::where('content_id', $data['content_id'])
            ->where('orientation', $data['orientation'])
            ->count();
        
        if ($existingMedia >= 2) {
            return response()->json([
                'success' => false,
                'message' => 'Content already has 2 media files for this orientation',
            ], 422);
        }

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
        $mediaFile = MediaFile::findOrFail($id);

        $data = $request->validate([
            'content_id'    => ['sometimes','exists:contents,id'],
            'orientation'   => ['sometimes','in:portrait,landscape,both'],
            'file_path'     => ['nullable','file'],
            'thumbnail'     => ['nullable','file']
        ]);

        // Check if content_id or orientation is being updated
        if (isset($data['content_id']) || isset($data['orientation'])) {
            $contentId = $data['content_id'] ?? $mediaFile->content_id;
            $orientation = $data['orientation'] ?? $mediaFile->orientation;

            // hanya boleh punya 2 konten
            $existingMedia = MediaFile::where('content_id', $contentId)
                ->where('orientation', $orientation)
                ->where('id', '!=', $id)
                ->count();
            
            if ($existingMedia >= 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Content already has 2 media files for this orientation',
                ], 422);
            }
        }

        if ($request->hasFile('file_path')) {
            $content = Content::findOrFail($data['content_id'] ?? $mediaFile->content_id);
            if (!$content) {
                return response()->json([
                    'success' => false,
                    'message' => 'Content Not Found',
                ], 404);
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
            $data['file_path'] = $file->store($folder_media, 's3');
        }

        if ($request->hasFile('thumbnail')) {
            $content = Content::findOrFail($data['content_id'] ?? $mediaFile->content_id);
            $folder_thumbnail = 'ads/'.$content->event->title.'/'.$content->title.'/thumbnail';
            $data['thumbnail'] = $request->file('thumbnail')->store($folder_thumbnail, 's3');
        }

        $mediaFile->update($data);

        return response()->json([
            'success'   => true,
            'message'   => 'Media Successfully Updated'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $id = $request->route('id') ?? $request->input('id');

        if (!$id) {
            return response()->json([
                'success' => false,
                'message' => 'Media File id is required',
            ], 400);
        }

        $mediaFile = MediaFile::find($id);

        if (!$mediaFile) {
            return response()->json([
                'success' => false,
                'message' => 'Media File Not Found',
            ], 404);
        }

        if ($mediaFile->file_path && Storage::disk('s3')->exists($mediaFile->file_path)) {
            Storage::disk('s3')->delete($mediaFile->file_path);
        }

        if ($mediaFile->thumbnail && Storage::disk('s3')->exists($mediaFile->thumbnail)) {
            Storage::disk('s3')->delete($mediaFile->thumbnail);
        }

        $mediaFile->delete();

        return response()->json([
            'success' => true,
            'message' => 'Media Successfully Deleted',
        ]);
    }
}
