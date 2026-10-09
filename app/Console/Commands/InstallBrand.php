<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;

/**
 * Prepares the institution artwork: a header sized version of the full logo
 * lockup, and a square crest cropped from it for use as the favicon.
 *
 * Existing settings are left alone unless --force is given, so an uploaded
 * replacement is never overwritten by re-seeding.
 */
class InstallBrand extends Command
{
    protected $signature = 'portal:install-brand
                            {--source= : Path to the logo lockup (defaults to the bundled one)}
                            {--force : Overwrite the logo settings even when they are already set}';

    protected $description = 'Generate the header logo and favicon from the institution artwork';

    /** The crest sits to the left of the wordmark in the bundled lockup. */
    protected const CREST = ['x' => 16, 'y' => 19, 'width' => 446, 'height' => 526];

    protected const LOGO_HEIGHT = 200;

    protected const CREST_SIZE = 256;

    public function handle(): int
    {
        $source = $this->option('source') ?: database_path('seeders/assets/institution-logo.jpg');

        if (! is_file($source)) {
            $this->error('Logo artwork not found: '.$source);

            return self::FAILURE;
        }

        $sheet = $this->read($source);

        if (! $sheet) {
            $this->error('The logo artwork could not be read.');

            return self::FAILURE;
        }

        media_disk()->makeDirectory('uploads/brand');

        $logoPath = 'uploads/brand/institution-logo.png';
        $crestPath = 'uploads/brand/institution-crest.png';

        $this->writeLogo($sheet, $logoPath);
        $this->writeCrest($sheet, $crestPath);

        imagedestroy($sheet);

        $written = [
            'site_logo' => $logoPath,
            'site_logo_footer' => $logoPath,
            'favicon' => $crestPath,
        ];

        $changed = 0;

        foreach ($written as $key => $value) {
            $setting = Setting::where('key', $key)->first();

            if (! $setting) {
                continue;
            }

            if (filled($setting->value) && ! $this->option('force')) {
                $this->line('  kept existing '.$key);

                continue;
            }

            $setting->update(['value' => $value]);
            $changed++;
        }

        Setting::flush();

        $this->info('Brand artwork written, '.$changed.' settings updated.');

        return self::SUCCESS;
    }

    protected function read(string $path)
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'png' => @imagecreatefrompng($path),
            'gif' => @imagecreatefromgif($path),
            'webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => @imagecreatefromjpeg($path),
        } ?: null;
    }

    protected function writeLogo($sheet, string $path): void
    {
        $sourceWidth = imagesx($sheet);
        $sourceHeight = imagesy($sheet);
        $height = min(self::LOGO_HEIGHT, $sourceHeight);
        $width = (int) round($sourceWidth * ($height / $sourceHeight));

        $logo = imagecreatetruecolor($width, $height);
        imagefill($logo, 0, 0, imagecolorallocate($logo, 255, 255, 255));
        imagecopyresampled($logo, $sheet, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight);

        $this->put($logo, $path);
    }

    protected function writeCrest($sheet, string $path): void
    {
        $box = self::CREST;
        $side = max($box['width'], $box['height']);

        // Centre the crest on a square canvas so the favicon is not squashed.
        $square = imagecreatetruecolor($side, $side);
        imagefill($square, 0, 0, imagecolorallocate($square, 255, 255, 255));
        imagecopy(
            $square,
            $sheet,
            (int) (($side - $box['width']) / 2),
            (int) (($side - $box['height']) / 2),
            $box['x'],
            $box['y'],
            $box['width'],
            $box['height']
        );

        $crest = imagecreatetruecolor(self::CREST_SIZE, self::CREST_SIZE);
        imagefill($crest, 0, 0, imagecolorallocate($crest, 255, 255, 255));
        imagecopyresampled($crest, $square, 0, 0, 0, 0, self::CREST_SIZE, self::CREST_SIZE, $side, $side);
        imagedestroy($square);

        $this->put($crest, $path);
    }

    protected function put($image, string $path): void
    {
        ob_start();
        imagepng($image, null, 9);
        $binary = (string) ob_get_clean();

        imagedestroy($image);

        media_disk()->put($path, $binary);
        $this->line('  wrote '.$path.' ('.number_format(strlen($binary)).' bytes)');
    }
}
