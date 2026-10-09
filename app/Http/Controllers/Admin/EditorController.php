<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Receives images dropped or pasted into the rich text editor. Storing them as
 * files keeps the saved markup small, where the editor would otherwise inline
 * the whole picture as a data URI.
 */
class EditorController extends Controller
{
    public function upload(Request $request): JsonResponse
    {
        // Answered in JSON whatever happens, because the editor reads the
        // response directly and cannot follow a redirect to the form.
        $validator = validator($request->all(), [
            'file' => ['required', 'image', 'max:8192'],
        ], [
            'file.image' => 'Only image files can be placed in the text.',
            'file.max' => 'That image is larger than the 8 MB limit.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first('file'),
            ], 422);
        }

        $file = $request->file('file');

        $base = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'image';
        $name = Str::limit($base, 60, '').'-'.Str::lower(Str::random(6)).'.'.strtolower($file->getClientOriginalExtension());
        $path = $file->storeAs('uploads/editor', $name, media_disk_name());

        // Recorded in the library so the file can be found and reused later.
        Media::create([
            'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'file_name' => $name,
            'path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'collection' => 'editor',
            'uploaded_by' => $request->user()?->id,
        ]);

        return response()->json([
            'url' => media_url($path),
            'path' => $path,
        ]);
    }
}
