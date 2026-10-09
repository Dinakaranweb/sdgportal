<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Copies the portal's uploaded files from one storage disk to another.
 *
 * Switching FILESYSTEM_DISK changes where new uploads go, but files already
 * written stay where they were. Run this once before or after the switch so
 * that everything already published keeps resolving.
 */
class SyncMedia extends Command
{
    protected $signature = 'portal:sync-media
                            {--from=public : Disk to copy from}
                            {--to=s3 : Disk to copy to}
                            {--path=uploads : Folder to copy}
                            {--overwrite : Replace files that already exist on the target}
                            {--dry-run : List what would be copied without writing anything}';

    protected $description = 'Copy uploaded files between storage disks';

    public function handle(): int
    {
        $from = $this->option('from');
        $to = $this->option('to');

        if ($from === $to) {
            $this->error('The source and target disks are the same.');

            return self::FAILURE;
        }

        foreach ([$from, $to] as $disk) {
            if (! config('filesystems.disks.'.$disk)) {
                $this->error('No disk named "'.$disk.'" is configured.');

                return self::FAILURE;
            }
        }

        try {
            $source = Storage::disk($from);
            $target = Storage::disk($to);
            $files = $source->allFiles($this->option('path'));
        } catch (\Throwable $e) {
            $this->error('Could not read from "'.$from.'": '.$e->getMessage());

            return self::FAILURE;
        }

        if (! $files) {
            $this->info('Nothing to copy from '.$from.'.');

            return self::SUCCESS;
        }

        $dryRun = $this->option('dry-run');
        $copied = 0;
        $skipped = 0;
        $failed = 0;

        $this->info(count($files).' '.str('file')->plural(count($files)).' found on "'.$from.'".');
        $bar = $this->output->createProgressBar(count($files));
        $bar->start();

        foreach ($files as $file) {
            $bar->advance();

            try {
                // A dry run reports only what the source holds, so it works
                // even when the target is not reachable yet.
                if (! $dryRun) {
                    if (! $this->option('overwrite') && $target->exists($file)) {
                        $skipped++;

                        continue;
                    }

                    // Streamed so that large documents do not have to be held
                    // in memory all at once.
                    $stream = $source->readStream($file);

                    if ($stream === false || $stream === null) {
                        $failed++;

                        continue;
                    }

                    $target->writeStream($file, $stream);

                    if (is_resource($stream)) {
                        fclose($stream);
                    }
                }

                $copied++;
            } catch (\Throwable $e) {
                $failed++;
                $this->newLine();
                $this->warn('  '.$file.' — '.$e->getMessage());
            }
        }

        $bar->finish();
        $this->newLine(2);

        $this->info(($dryRun ? 'Would copy ' : 'Copied ').$copied.' to "'.$to.'".');

        if ($skipped) {
            $this->line('  '.$skipped.' already present (use --overwrite to replace).');
        }

        if ($failed) {
            $this->warn('  '.$failed.' could not be copied.');
        }

        if (! $dryRun && $copied) {
            $this->newLine();
            $this->line('Set FILESYSTEM_DISK='.$to.' in .env, then run: php artisan config:clear');
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
