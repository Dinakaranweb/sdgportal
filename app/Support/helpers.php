<?php

use App\Models\Setting;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

if (! function_exists('setting')) {
    /**
     * Read a value from the portal settings table.
     */
    function setting(string $key, mixed $default = null): mixed
    {
        return Setting::value($key, $default);
    }
}

if (! function_exists('media_disk_name')) {
    /**
     * The disk every upload is written to and read from.
     *
     * This follows FILESYSTEM_DISK, so moving the portal's files to S3 is a
     * configuration change rather than a code change.
     */
    function media_disk_name(): string
    {
        return (string) config('filesystems.default', 'public');
    }
}

if (! function_exists('media_disk')) {
    /**
     * The configured storage disk, ready to read, write or delete.
     */
    function media_disk(): Filesystem
    {
        return Storage::disk(media_disk_name());
    }
}

if (! function_exists('media_url')) {
    /**
     * Public URL for a stored file, passing absolute URLs straight through.
     */
    function media_url(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '//', 'data:'])) {
            return $path;
        }

        $path = ltrim($path, '/');
        $disk = media_disk_name();

        // Files held on this server are served through the public/storage link.
        // Building the address from the current request root keeps them working
        // in a sub directory, behind a proxy, or on a different host name.
        if (config('filesystems.disks.'.$disk.'.driver') === 'local') {
            return asset('storage/'.$path);
        }

        // Anything remote, S3 included, knows how to address its own files.
        return Storage::disk($disk)->url($path);
    }
}

if (! function_exists('opens_away')) {
    /**
     * Whether a link should open in a new tab.
     *
     * PDFs do, so they are read in the browser's viewer without losing the
     * page behind them, and so does anything on another site.
     */
    function opens_away(?string $url): bool
    {
        return is_pdf($url) || is_external_url($url);
    }
}

if (! function_exists('is_pdf')) {
    /**
     * True when a path or URL points at a PDF.
     */
    function is_pdf(?string $path): bool
    {
        if (blank($path)) {
            return false;
        }

        $clean = strtok((string) $path, '?#');

        return strtolower((string) pathinfo((string) $clean, PATHINFO_EXTENSION)) === 'pdf';
    }
}

if (! function_exists('portal_url')) {
    /**
     * Resolve a link entered in the control panel.
     *
     * Absolute addresses, mail and telephone links and page anchors are passed
     * through untouched. A site relative path such as `/goals/sdg-13` is
     * resolved against the application root, so links keep working when the
     * portal is installed in a sub directory.
     */
    function portal_url(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (Str::startsWith($value, ['http://', 'https://', '//', 'mailto:', 'tel:', '#', 'data:'])) {
            return $value;
        }

        return url($value);
    }
}

if (! function_exists('embed_url')) {
    /**
     * Convert a YouTube or Vimeo watch link into its embeddable form.
     */
    function embed_url(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        if (preg_match('~youtu\.be/([A-Za-z0-9_-]{6,})~', $url, $m)) {
            return 'https://www.youtube.com/embed/'.$m[1];
        }

        if (preg_match('~youtube\.com/.*[?&]v=([A-Za-z0-9_-]{6,})~', $url, $m)) {
            return 'https://www.youtube.com/embed/'.$m[1];
        }

        if (preg_match('~youtube\.com/(embed|shorts)/([A-Za-z0-9_-]{6,})~', $url, $m)) {
            return 'https://www.youtube.com/embed/'.$m[2];
        }

        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $m)) {
            return 'https://player.vimeo.com/video/'.$m[1];
        }

        return $url;
    }
}

if (! function_exists('clean_html')) {
    /**
     * Allow a conservative set of tags in editor supplied markup.
     */
    function clean_html(?string $html): string
    {
        if (blank($html)) {
            return '';
        }

        $allowed = '<p><br><strong><b><em><i><u><s><ul><ol><li><a><h2><h3><h4><h5><blockquote>'
            .'<table><thead><tbody><tr><th><td><span><div><img><figure><figcaption><hr><sup><sub><small><code><pre>';

        $html = preg_replace('~<\s*(script|style|iframe|object|embed|form)[^>]*>.*?<\s*/\s*\1\s*>~is', '', $html);
        $html = preg_replace('~\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)~i', '', (string) $html);

        return strip_tags((string) $html, $allowed);
    }
}

if (! function_exists('embed_html')) {
    /**
     * Embed markup keeps iframes and figures, which is what the services used
     * for maps, dashboards and charts need, but never inline script.
     */
    function embed_html(?string $html): string
    {
        if (blank($html)) {
            return '';
        }

        $html = preg_replace('~<\s*script[^>]*>.*?<\s*/\s*script\s*>~is', '', $html);
        $html = preg_replace('~<\s*/?\s*(script|object|embed|form|base|meta|link)\b[^>]*>~i', '', (string) $html);
        $html = preg_replace('~\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)~i', '', (string) $html);
        $html = preg_replace('~(href|src)\s*=\s*(["\']?)\s*javascript:~i', '$1=$2about:blank', (string) $html);

        return (string) $html;
    }
}

if (! function_exists('rich_text')) {
    /**
     * Render editor text, wrapping plain text in paragraphs when needed.
     */
    function rich_text(?string $text): string
    {
        if (blank($text)) {
            return '';
        }

        $hasMarkup = (bool) preg_match('~<(p|ul|ol|h[2-6]|table|div|blockquote|figure)\b~i', $text);

        if ($hasMarkup) {
            return clean_html($text);
        }

        return collect(preg_split('/\R{2,}/', trim($text)))
            ->filter()
            ->map(fn ($para) => '<p>'.nl2br(e(trim($para))).'</p>')
            ->implode('');
    }
}

if (! function_exists('excerpt')) {
    function excerpt(?string $text, int $length = 160): string
    {
        return Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags((string) $text))), $length);
    }
}

if (! function_exists('is_external_url')) {
    function is_external_url(?string $url): bool
    {
        if (blank($url) || ! Str::startsWith($url, ['http://', 'https://', '//'])) {
            return false;
        }

        $host = parse_url($url, PHP_URL_HOST);

        return $host && $host !== parse_url(config('app.url'), PHP_URL_HOST);
    }
}
