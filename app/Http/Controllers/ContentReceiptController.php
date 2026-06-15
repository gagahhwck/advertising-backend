<?php

namespace App\Http\Controllers;

use App\Jobs\ReceiptNotificationJob;
use App\Models\Advertising\ContentReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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
            'title'         => ['required', 'array', 'min:1'],
            'title.*'       => ['required', 'string', 'max:255'],
            'description'   => ['required', 'array', 'min:1'],
            'description.*' => ['required', 'string'],
            'to'            => ['required', 'array', 'min:1'],
            'to.*'          => ['required', 'string', 'exists:sso.dbo.users,username'],
        ]);

        $from = Auth::user()->username;

        $created = [];

        $count = count($data['title']);
        if ($count !== count($data['description']) || $count !== count($data['to'])) {
            Validator::make([], [])->after(function ($validator) {
                $validator->errors()->add('title', 'The title, description, and to fields must have the same number of items.');
            })->validate();
        }

        for ($index = 0; $index < $count; $index++) {
            $payload = [
                'content_id'  => $data['content_id'],
                'title'       => $data['title'][$index],
                'description' => $data['description'][$index],
                'to'          => $data['to'][$index],
                'from'        => $from,
            ];

            $contentReceipt = ContentReceipt::create($payload);
            ReceiptNotificationJob::dispatch($contentReceipt);
            $created[] = $contentReceipt;
        }

        return response()->json(['data' => $created], 201);
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
    public function destroy(ContentReceipt $contentReceipt)
    {
        $contentReceipt->delete();

        return response()->json(['message' => 'Delete Successfully'],200);
    }
}
