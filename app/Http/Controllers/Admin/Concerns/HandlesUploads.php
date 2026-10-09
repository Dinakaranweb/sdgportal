<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

trait HandlesUploads
{
    /**
     * Resolve the value for an image or file field.
     *
     * Three inputs are supported per field, in order of precedence: a newly
     * uploaded file, a path picked from the media library, and a "remove"
     * checkbox. When none are present the existing value is kept.
     */
    protected function resolveUpload(Request $request, string $field, string $folder, ?string $current = null): ?string
    {
        if ($request->boolean($field.'_remove')) {
            $this->deleteManagedFile($current);

            return null;
        }

        if ($request->hasFile($field)) {
            $path = $this->storeUpload($request->file($field), $folder);
            $this->deleteManagedFile($current);

            return $path;
        }

        $picked = trim((string) $request->input($field.'_path'));

        if (filled($picked)) {
            return $picked;
        }

        return $current;
    }

    protected function storeUpload($file, string $folder): string
    {
        $name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $name = Str::limit($name ?: 'file', 60, '');
        $fileName = $name.'-'.Str::lower(Str::random(6)).'.'.strtolower($file->getClientOriginalExtension());

        return $file->storeAs('uploads/'.$folder, $fileName, media_disk_name());
    }

    /**
     * Remove a file the record owns, leaving media library items untouched.
     */
    protected function deleteManagedFile(?string $path): void
    {
        if (blank($path) || Str::startsWith($path, ['http://', 'https://', '//'])) {
            return;
        }

        if (Str::startsWith($path, 'uploads/library/')) {
            return;
        }

        if (media_disk()->exists($path)) {
            media_disk()->delete($path);
        }
    }

    /**
     * Strip the empty rows out of a repeatable field before it is stored.
     */
    protected function cleanRows(mixed $rows): array
    {
        if (! is_array($rows)) {
            return [];
        }

        $clean = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                if (filled($row)) {
                    $clean[] = $row;
                }

                continue;
            }

            $row = array_map(fn ($value) => is_string($value) ? trim($value) : $value, $row);

            if (collect($row)->filter(fn ($value) => filled($value))->isNotEmpty()) {
                $clean[] = $row;
            }
        }

        return array_values($clean);
    }
}
