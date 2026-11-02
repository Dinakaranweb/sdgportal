<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesUploads;
use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Support\Uploads;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Receives images dropped or pasted into the rich text editor. Storing them as
 * files keeps the saved markup small, where the editor would otherwise inline
 * the whole picture as a data URI.
 */
class EditorController extends Controller
{
    use HandlesUploads;

    public function upload(Request $request): JsonResponse
    {
        // Answered in JSON whatever happens, because the editor reads the
        // response directly and cannot follow a redirect to the form.
        $validator = validator($request->all(), [
            'file' => Uploads::imageRules(true),
        ], [
            'file.mimes' => 'Only image files can be placed in the text.',
            'file.max' => 'That image is larger than the '.Uploads::limitInWords().' limit.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first('file'),
            ], 422);
        }

        $file = $request->file('file');

        $path = $this->storeUpload($file, 'editor');

        // Recorded in the library so the file can be found and reused later.
        Media::create([
            'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'file_name' => basename($path),
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
