<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

/**
 * Reports on the things that differ between a desktop install and a hosting
 * account, which is where a portal that behaves itself locally usually comes
 * unstuck: the address the pages are written with, whether the stylesheets and
 * scripts can be found and stamped, and whether an old compiled copy of the
 * views or the configuration is being served instead of the current one.
 *
 * Run it on the server after an update. Nothing is changed; the closing lines
 * say what to do about anything it reports.
 */
class CheckInstall extends Command
{
    protected $signature = 'portal:check';

    protected $description = 'Check an installation for the problems that only appear once the portal is hosted';

    /**
     * The portal's own stylesheets and scripts, which have to reach the browser
     * freshly after every update.
     */
    protected array $assets = [
        'assets/css/site.css',
        'assets/css/admin.css',
        'assets/js/site.js',
        'assets/js/admin.js',
        'assets/js/editor.js',
    ];

    protected array $problems = [];

    protected array $notes = [];

    public function handle(): int
    {
        $this->addresses();
        $this->assetFiles();
        $this->compiledViews();
        $this->cachedConfig();
        $this->uploads();

        return $this->report();
    }

    /**
     * The address the portal writes into its pages. A secure site whose pages
     * carry plain http addresses loses every stylesheet and script, because a
     * browser will not load those alongside a secure page.
     */
    protected function addresses(): void
    {
        $this->components->twoColumnDetail('<options=bold>Addresses</>', '');

        $configured = (string) config('app.url');
        $this->components->twoColumnDetail('APP_URL', $configured);
        $this->components->twoColumnDetail('Asset address', (string) (config('app.asset_url') ?: 'follows the request'));
        $this->components->twoColumnDetail('Environment', (string) config('app.env'));
        $this->components->twoColumnDetail('Debug messages', config('app.debug') ? 'on' : 'off');

        $desktop = str_contains($configured, 'localhost') || str_contains($configured, '127.0.0.1');

        if ($desktop && app()->environment('local')) {
            $this->notes[] = 'This is a desktop install, so the hosting checks below are relaxed. Run this on the server for the answers that matter.';
        } elseif ($desktop) {
            $this->problems[] = 'APP_URL still points at a desktop address ('.$configured.'). Set it to the live address, with https if the site is secure.';
        }

        if (str_starts_with($configured, 'http://') && app()->environment('production')) {
            $this->notes[] = 'APP_URL is not https. If the site is reached over https, the pages will ask for stylesheets and scripts over plain http and the browser will refuse them.';
        }

        if (config('app.debug') && app()->environment('production')) {
            $this->problems[] = 'Debug messages are on in production, which shows visitors the inner workings of the portal on any error. Set APP_DEBUG=false.';
        }

        // A secure request as it arrives through a hosting proxy, put through
        // the portal's own proxy middleware so that what is reported is the
        // configuration in use rather than an imitation of it.
        $request = Request::create('http://'.($request_host = parse_url($configured ?: 'http://localhost', PHP_URL_HOST) ?: 'localhost').'/', 'GET', [], [], [], [
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'REMOTE_ADDR' => '10.0.0.1',
        ]);

        $secure = false;

        app(TrustProxies::class)->handle($request, function (Request $passed) use (&$secure) {
            $secure = $passed->isSecure();

            return new Response;
        });

        $this->components->twoColumnDetail('Behind a proxy, reads as', $secure ? 'https' : 'http');

        if (! $secure) {
            $this->problems[] = 'A secure request arriving through the hosting proxy is still read as plain http, so every stylesheet and script address will be written wrongly and the browser will refuse them. The proxy is not being trusted.';
        }

        unset($request_host);
    }

    /**
     * Whether each stylesheet and script can be found on disk, and so whether
     * its address can be stamped with a version the browser will notice.
     */
    protected function assetFiles(): void
    {
        $this->newLine();
        $this->components->twoColumnDetail('<options=bold>Stylesheets and scripts</>', '');

        $unstamped = [];

        foreach ($this->assets as $path) {
            $file = portal_asset_file($path);
            $url = portal_asset($path);

            if (! $file) {
                $unstamped[] = $path;
                $this->components->twoColumnDetail($path, '<fg=yellow>not found on disk</>');

                continue;
            }

            $this->components->twoColumnDetail($path, 'changed '.date('j M Y H:i', filemtime($file)));
            $this->line('   <fg=gray>'.$url.'</>');
        }

        if ($unstamped) {
            $this->problems[] = count($unstamped).' file(s) could not be found on disk, so the version stamp falls back to the deployment time: '.implode(', ', $unstamped).'. Check that the public folder was uploaded in full.';
        }
    }

    /**
     * Compiled views older than the templates they came from. The portal then
     * serves the previous version of a page however often it is reloaded,
     * which is the usual reason an update appears to have had no effect.
     */
    protected function compiledViews(): void
    {
        $this->newLine();
        $this->components->twoColumnDetail('<options=bold>Compiled views</>', '');

        $compiled = glob(storage_path('framework/views/*.php')) ?: [];
        $this->components->twoColumnDetail('Compiled files', (string) count($compiled));

        if (! $compiled) {
            $this->components->twoColumnDetail('State', 'nothing cached, every page is read fresh');

            return;
        }

        $newestSource = 0;

        foreach ((array) config('view.paths', []) as $directory) {
            foreach ($this->bladeFiles($directory) as $file) {
                $newestSource = max($newestSource, filemtime($file));
            }
        }

        $newestCompiled = max(array_map('filemtime', $compiled));

        $this->components->twoColumnDetail('Newest template', $newestSource ? date('j M Y H:i', $newestSource) : 'none found');
        $this->components->twoColumnDetail('Newest compiled copy', date('j M Y H:i', $newestCompiled));

        if ($newestSource > $newestCompiled) {
            $this->problems[] = 'The compiled views are older than the templates, so updated pages are not being shown. Clear them.';
        }
    }

    /**
     * Cached configuration, which keeps its own copy of the settings and
     * ignores later changes to the environment file.
     */
    protected function cachedConfig(): void
    {
        $this->newLine();
        $this->components->twoColumnDetail('<options=bold>Cached settings and routes</>', '');

        foreach (['config.php' => 'Settings', 'routes-v7.php' => 'Routes', 'events.php' => 'Events'] as $file => $label) {
            $path = base_path('bootstrap/cache/'.$file);
            $this->components->twoColumnDetail($label, is_file($path) ? 'cached '.date('j M Y H:i', filemtime($path)) : 'not cached');
        }

        $config = base_path('bootstrap/cache/config.php');
        $env = base_path('.env');

        if (is_file($config) && is_file($env) && filemtime($env) > filemtime($config)) {
            $this->problems[] = 'The environment file has been changed since the settings were cached, so the portal is running on the older settings. Clear them.';
        }
    }

    /**
     * Where uploads are kept, and whether they can actually be reached.
     */
    protected function uploads(): void
    {
        $this->newLine();
        $this->components->twoColumnDetail('<options=bold>Uploads</>', '');

        $disk = media_disk_name();
        $this->components->twoColumnDetail('Stored on', $disk);

        if ($disk === 'public') {
            $link = public_path('storage');
            $target = storage_path('app/public');

            if (is_link($link) || is_dir($link)) {
                $this->components->twoColumnDetail('Public link', is_link($link) ? 'linked' : 'a real folder');
            } else {
                $this->problems[] = 'Uploads are kept locally but public/storage is missing, so no uploaded image or document will load. Run: php artisan storage:link';
            }

            if (! is_dir($target)) {
                $this->notes[] = 'storage/app/public does not exist yet, so nothing has been uploaded on this server.';
            }

            return;
        }

        try {
            Storage::disk($disk)->files('uploads');
            $this->components->twoColumnDetail('Reachable', 'yes');
        } catch (\Throwable $e) {
            $this->problems[] = 'The '.$disk.' disk could not be read: '.$e->getMessage();
        }
    }

    /**
     * Every template under a view directory.
     *
     * @return array<int, string>
     */
    protected function bladeFiles(string $directory): array
    {
        if (! is_dir($directory)) {
            return [];
        }

        $found = [];
        $items = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS));

        foreach ($items as $item) {
            if ($item->isFile() && str_ends_with($item->getFilename(), '.blade.php')) {
                $found[] = $item->getPathname();
            }
        }

        return $found;
    }

    protected function report(): int
    {
        $this->newLine();

        foreach ($this->notes as $note) {
            $this->components->warn($note);
        }

        if (! $this->problems) {
            $this->components->info('Nothing to fix.');

            return self::SUCCESS;
        }

        foreach ($this->problems as $problem) {
            $this->components->error($problem);
        }

        $this->line('  Clearing the caches: <options=bold>php artisan optimize:clear</>');
        $this->line('  Without a terminal, delete the files in <options=bold>storage/framework/views</>');
        $this->line('  and in <options=bold>bootstrap/cache</> apart from .gitignore.');
        $this->newLine();

        return self::FAILURE;
    }
}
