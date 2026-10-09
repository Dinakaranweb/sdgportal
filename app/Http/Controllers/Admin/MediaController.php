<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    public function index(Request $request): View
    {
        $media = Media::query()
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->string('q')->trim().'%';
                $q->where(fn ($inner) => $inner->where('name', 'like', $term)->orWhere('file_name', 'like', $term));
            })
            ->latest()
            ->paginate(36)
            ->withQueryString();

        return view('admin.media.index', [
            'media' => $media,
            'filters' => $request->only('q'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'files' => ['required', 'array', 'max:20'],
            'files.*' => ['file', 'max:20480', 'mimes:jpg,jpeg,png,gif,webp,svg,pdf,doc,docx,xls,xlsx,ppt,pptx,csv,txt,zip'],
            'alt_text' => ['nullable', 'string', 'max:190'],
        ]);

        $stored = 0;

        foreach ($request->file('files', []) as $file) {
            $base = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'file';
            $fileName = Str::limit($base, 60, '').'-'.Str::lower(Str::random(6)).'.'.strtolower($file->getClientOriginalExtension());
            $path = $file->storeAs('uploads/library', $fileName, media_disk_name());

            Media::create([
                'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'file_name' => $fileName,
                'path' => $path,
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'collection' => 'library',
                'alt_text' => $request->input('alt_text'),
                'uploaded_by' => $request->user()->id,
            ]);

            $stored++;
        }

        return back()->with('status', $stored.' '.Str::plural('file', $stored).' uploaded.');
    }

    public function destroy(Media $medium): RedirectResponse
    {
        $medium->delete();

        return back()->with('status', 'File deleted. Any page still pointing at it will show a broken image.');
    }
}
