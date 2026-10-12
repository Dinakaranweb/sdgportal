<?php

namespace App\Console\Commands;

use App\Models\Goal;
use App\Support\Palette;
use Illuminate\Console\Command;

/**
 * Cuts the goal artwork sheet into one image per goal and stores each one on
 * the configured disk, then points the matching goal record at it.
 *
 * The sheet is a six by three grid of square tiles: goals 1 to 17 in reading
 * order, with the combined emblem in the last cell. The grid is measured from
 * the image rather than hard coded, so a replacement sheet at a different size
 * works without touching this file.
 *
 * Two sizes are written for every goal: a full tile for banners and the goal
 * grid, and a small thumbnail for the dense strips in the footer and sidebar.
 */
class SliceGoalTiles extends Command
{
    protected $signature = 'portal:slice-goal-tiles
                            {--source= : Path to the artwork sheet (defaults to the bundled one)}
                            {--size=512 : Pixel size of each full tile}
                            {--thumb=96 : Pixel size of each thumbnail}
                            {--no-assign : Write the files but leave the goal records alone}
                            {--colours : Also take each goal colour from its tile, so the page theme matches the artwork}
                            {--force : Replace logos that have been changed by hand}';

    protected $description = 'Cut the goal artwork sheet into individual tile images';

    public function handle(): int
    {
        $source = $this->option('source') ?: database_path('seeders/assets/global-goals.png');

        if (! is_file($source)) {
            $this->error('Artwork sheet not found: '.$source);

            return self::FAILURE;
        }

        if (! function_exists('imagecreatefrompng')) {
            $this->error('The GD extension is required to slice the artwork.');

            return self::FAILURE;
        }

        $sheet = @imagecreatefrompng($source);

        if (! $sheet) {
            $this->error('The artwork sheet could not be read as a PNG.');

            return self::FAILURE;
        }

        $grid = $this->measure($sheet);

        if (! $grid) {
            imagedestroy($sheet);
            $this->error('Could not find a six by three grid of tiles in that sheet.');

            return self::FAILURE;
        }

        [$columns, $rows, $tile] = $grid;
        $this->line(sprintf('  grid: %d x %d tiles of %dpx', count($columns), count($rows), $tile));

        media_disk()->makeDirectory('uploads/goals');
        media_disk()->makeDirectory('uploads/goals/thumbs');
        media_disk()->makeDirectory('uploads/brand');

        $size = max(64, (int) $this->option('size'));
        $thumbSize = max(32, (int) $this->option('thumb'));

        $written = 0;
        $assigned = 0;
        $cell = 0;

        foreach ($rows as $top) {
            foreach ($columns as $left) {
                $cell++;

                if ($cell > 17) {
                    // The last cell holds the combined emblem, not a goal.
                    $this->writeTile($sheet, $left, $top, $tile, $size, 'uploads/brand/global-goals.png');
                    $written++;

                    continue;
                }

                $path = 'uploads/goals/sdg-'.$cell.'.png';
                $thumbPath = 'uploads/goals/thumbs/sdg-'.$cell.'.png';

                $this->writeTile($sheet, $left, $top, $tile, $size, $path);
                $this->writeTile($sheet, $left, $top, $tile, $thumbSize, $thumbPath);
                $written += 2;

                if (! $this->option('no-assign')) {
                    // A logo someone has uploaded themselves is left in place
                    // unless the slice is explicitly forced.
                    $assigned += Goal::where('code', (string) $cell)
                        ->when(! $this->option('force'), fn ($q) => $q->where(
                            fn ($i) => $i->whereNull('logo')->orWhere('logo', '')->orWhere('logo', $path)
                        ))
                        ->update(['logo' => $path]);
                }

                if ($this->option('colours')) {
                    $this->applyColour($sheet, $left, $top, (string) $cell);
                }
            }
        }

        imagedestroy($sheet);

        $this->info($written.' images written, '.$assigned.' goals updated.');

        return self::SUCCESS;
    }

    /**
     * Read the flat background colour out of a tile and store it on the goal,
     * so the banner and the rest of the page theme match the artwork.
     */
    protected function applyColour($sheet, int $left, int $top, string $code): void
    {
        $goal = Goal::where('code', $code)->first();

        if (! $goal) {
            return;
        }

        // A few pixels in from the corner is always flat tile background.
        $colour = imagecolorat($sheet, $left + 6, $top + 6);
        $hex = sprintf('#%02x%02x%02x', ($colour >> 16) & 0xFF, ($colour >> 8) & 0xFF, $colour & 0xFF);

        if ($hex === $goal->color) {
            return;
        }

        $this->line('  goal '.$code.': '.$goal->color.' -> '.$hex);

        $goal->update([
            'color' => $hex,
            'color_dark' => Palette::shade($hex, -24),
            'color_light' => Palette::shade($hex, 86),
            'text_color' => Palette::readableInk($hex),
        ]);
    }

    /**
     * Find the tile grid by looking for the bands of strong colour that the
     * tiles form against the sheet's pale background.
     *
     * @return array{0: list<int>, 1: list<int>, 2: int}|null
     */
    protected function measure($sheet): ?array
    {
        $width = imagesx($sheet);
        $height = imagesy($sheet);
        $step = max(1, (int) floor($width / 960));

        $saturated = function (int $x, int $y) use ($sheet): bool {
            $colour = imagecolorat($sheet, $x, $y);
            $r = ($colour >> 16) & 0xFF;
            $g = ($colour >> 8) & 0xFF;
            $b = $colour & 0xFF;

            return (max($r, $g, $b) - min($r, $g, $b)) > 25;
        };

        // Rows first: a band of tiles colours most of the sheet's width, while
        // a line of heading text colours very little of it.
        $rowHits = [];
        $samplesPerRow = (int) ceil($width / $step);

        for ($y = 0; $y < $height; $y++) {
            $hits = 0;
            for ($x = 0; $x < $width; $x += $step) {
                if ($saturated($x, $y)) {
                    $hits++;
                }
            }
            $rowHits[$y] = $hits > $samplesPerRow * 0.35;
        }

        $rowBands = $this->bands($rowHits, (int) round($height * 0.05));

        if (count($rowBands) !== 3) {
            return null;
        }

        // Columns are measured inside the first band, which is always a full
        // row of six coloured tiles.
        [$top, $bottom] = $rowBands[0];
        $columnHits = [];
        $samplesPerColumn = (int) ceil(($bottom - $top) / $step);

        for ($x = 0; $x < $width; $x++) {
            $hits = 0;
            for ($y = $top; $y <= $bottom; $y += $step) {
                if ($saturated($x, $y)) {
                    $hits++;
                }
            }
            $columnHits[$x] = $hits > $samplesPerColumn * 0.35;
        }

        $columnBands = $this->bands($columnHits, (int) round($width * 0.05));

        if (count($columnBands) !== 6) {
            return null;
        }

        $tile = (int) round(collect($rowBands)->merge($columnBands)
            ->map(fn ($band) => $band[1] - $band[0] + 1)
            ->avg());

        return [
            array_map(fn ($band) => $band[0], $columnBands),
            array_map(fn ($band) => $band[0], $rowBands),
            $tile,
        ];
    }

    /**
     * Collapse a run of true values into [start, end] pairs.
     *
     * @param  array<int, bool>  $flags
     * @return list<array{0:int,1:int}>
     */
    protected function bands(array $flags, int $minimumLength): array
    {
        $bands = [];
        $start = null;

        foreach ($flags as $index => $on) {
            if ($on && $start === null) {
                $start = $index;
            }

            if (! $on && $start !== null) {
                if ($index - $start >= $minimumLength) {
                    $bands[] = [$start, $index - 1];
                }
                $start = null;
            }
        }

        if ($start !== null && count($flags) - $start >= $minimumLength) {
            $bands[] = [$start, count($flags) - 1];
        }

        return $bands;
    }

    protected function writeTile($sheet, int $left, int $top, int $tile, int $size, string $path): void
    {
        $canvas = imagecreatetruecolor($size, $size);

        // Flat white behind the artwork, matching the sheet's own surround.
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
        imagecopyresampled($canvas, $sheet, 0, 0, $left, $top, $size, $size, $tile, $tile);

        ob_start();
        imagepng($canvas, null, 9);
        $binary = (string) ob_get_clean();

        imagedestroy($canvas);

        media_disk()->put($path, $binary);
    }
}
