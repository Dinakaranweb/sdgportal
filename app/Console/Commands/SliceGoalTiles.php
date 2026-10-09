<?php

namespace App\Console\Commands;

use App\Models\Goal;
use Illuminate\Console\Command;

/**
 * Cuts the supplied goal artwork sheet into one image per goal and stores each
 * one on the public disk, then points the matching goal record at it.
 *
 * The sheet is a six by three grid of square tiles: goals 1 to 17 in reading
 * order, with the combined emblem in the last cell.
 */
class SliceGoalTiles extends Command
{
    protected $signature = 'portal:slice-goal-tiles
                            {--source= : Path to the artwork sheet (defaults to the bundled one)}
                            {--no-assign : Write the files but leave the goal records alone}
                            {--force : Replace logos that have been changed by hand}';

    protected $description = 'Cut the goal artwork sheet into individual tile images';

    /** Top left corner of each column and row, and the tile size, in pixels. */
    protected const COLUMNS = [4, 145, 287, 427, 569, 709];

    protected const ROWS = [276, 417, 558];

    protected const TILE = 128;

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

        media_disk()->makeDirectory('uploads/goals');
        media_disk()->makeDirectory('uploads/brand');

        $written = 0;
        $assigned = 0;
        $cell = 0;

        foreach (self::ROWS as $top) {
            foreach (self::COLUMNS as $left) {
                $cell++;

                // The eighteenth cell holds the combined emblem rather than a goal.
                $path = $cell <= 17
                    ? 'uploads/goals/sdg-'.$cell.'.png'
                    : 'uploads/brand/global-goals.png';

                $this->writeTile($sheet, $left, $top, $path);
                $written++;

                if ($cell <= 17 && ! $this->option('no-assign')) {
                    // A logo someone has uploaded themselves is left in place
                    // unless the slice is explicitly forced.
                    $assigned += Goal::where('code', (string) $cell)
                        ->when(! $this->option('force'), fn ($q) => $q->where(
                            fn ($i) => $i->whereNull('logo')->orWhere('logo', '')->orWhere('logo', $path)
                        ))
                        ->update(['logo' => $path]);
                }
            }
        }

        imagedestroy($sheet);

        $this->info($written.' tiles written to storage, '.$assigned.' goals updated.');

        return self::SUCCESS;
    }

    protected function writeTile($sheet, int $left, int $top, string $path): void
    {
        $tile = imagecreatetruecolor(self::TILE, self::TILE);

        // Keep the flat white surround of the source rather than inventing
        // transparency, so the tiles sit cleanly on any page background.
        imagefill($tile, 0, 0, imagecolorallocate($tile, 255, 255, 255));
        imagecopy($tile, $sheet, 0, 0, $left, $top, self::TILE, self::TILE);

        ob_start();
        imagepng($tile, null, 9);
        $binary = (string) ob_get_clean();

        imagedestroy($tile);

        media_disk()->put($path, $binary);
    }
}
