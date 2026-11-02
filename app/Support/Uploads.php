<?php

namespace App\Support;

/**
 * What the portal accepts when something is uploaded, in one place.
 *
 * The same limits are needed by the block editor, the media library, the rich
 * text editor and the file inputs in the forms, so they are declared here once
 * rather than repeated as literals in each of them.
 */
class Uploads
{
    /**
     * The largest upload the portal will take, in kilobytes.
     */
    public const MAX_KILOBYTES = 51200;

    /**
     * Picture formats. Everything a browser can display is accepted, vector
     * drawings included.
     */
    public static function imageExtensions(): array
    {
        return ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'avif', 'bmp', 'ico', 'tif', 'tiff'];
    }

    /**
     * Document formats, for reports, policies and certificates.
     */
    public static function documentExtensions(): array
    {
        return ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'csv', 'txt', 'zip'];
    }

    public static function allExtensions(): array
    {
        return array_values(array_unique([...static::documentExtensions(), ...static::imageExtensions()]));
    }

    /**
     * Validation rules for a field that takes a picture.
     *
     * The framework's own image rule is not used: it leaves out vector
     * drawings and the newer formats, which the portal accepts.
     */
    public static function imageRules(bool $required = false): array
    {
        return [
            $required ? 'required' : 'nullable',
            'file',
            'mimes:'.implode(',', static::imageExtensions()),
            'max:'.static::MAX_KILOBYTES,
        ];
    }

    /**
     * Validation rules for a field that takes a document or a picture.
     */
    public static function fileRules(bool $required = false): array
    {
        return [
            $required ? 'required' : 'nullable',
            'file',
            'mimes:'.implode(',', static::allExtensions()),
            'max:'.static::MAX_KILOBYTES,
        ];
    }

    /**
     * The accept attribute for a picture input. Both the extensions and the
     * wildcard are given, because file pickers differ in which they honour.
     */
    public static function imageAccept(): string
    {
        return static::accept(static::imageExtensions()).',image/*';
    }

    /**
     * The accept attribute for an input that takes a document or a picture.
     */
    public static function fileAccept(): string
    {
        return static::accept(static::allExtensions()).',image/*';
    }

    protected static function accept(array $extensions): string
    {
        return implode(',', array_map(fn ($extension) => '.'.$extension, $extensions));
    }

    /**
     * How large an upload may be, said in words for a form hint. Where the
     * server is set to accept less than the portal allows, the server's figure
     * is the one quoted, because that is the limit that will actually bite.
     */
    public static function limitInWords(): string
    {
        $allowed = min(static::MAX_KILOBYTES, static::serverKilobytes() ?: static::MAX_KILOBYTES);

        return round($allowed / 1024).' MB';
    }

    /**
     * The smaller of the two server settings that cap an upload, in kilobytes,
     * or null where neither is set.
     */
    public static function serverKilobytes(): ?int
    {
        $limits = array_filter([
            static::toKilobytes((string) ini_get('upload_max_filesize')),
            static::toKilobytes((string) ini_get('post_max_size')),
        ]);

        return $limits ? (int) min($limits) : null;
    }

    /**
     * A php.ini size such as 50M or 2048K, in kilobytes.
     */
    public static function toKilobytes(string $value): ?int
    {
        $value = trim($value);

        if ($value === '' || $value === '0' || $value === '-1') {
            return null;
        }

        $number = (float) $value;
        $unit = strtoupper(substr($value, -1));

        return match ($unit) {
            'G' => (int) ($number * 1024 * 1024),
            'M' => (int) ($number * 1024),
            'K' => (int) $number,
            default => (int) ($number / 1024),
        };
    }
}
