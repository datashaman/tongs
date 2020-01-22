<?php

declare(strict_types=1);

namespace App\Commands;

use Illuminate\Contracts\Pipeline\Hub;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use LaravelZero\Framework\Commands\Command;

final class BuildCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'build';

    /**
     * @var string
     */
    protected $description = 'Build static site.';

    public function handle(Hub $hub)
    {
        $files = $this->runPipe($hub);

        $this->info(
            $files->count() .
                ' files written to ' .
                Storage::disk(config('tongs.destination', 'build'))->path(''),
        );
    }

    protected function runPipe(Hub $hub): Collection
    {
        return $hub->pipe($this->prepareFiles())->then(
            static function ($files) {
                return $files->map(
                    static function ($file) {
                        $destination = config('tongs.destination', 'build');
                        $disk = Storage::disk($destination);

                        $disk->put($file['path'], $file['contents']);

                        return $file;
                    }
                );
            }
        );
    }

    protected function prepareFiles(): Collection
    {
        $source = config('tongs.source', 'source');
        $disk = Storage::disk($source);

        return collect($disk->allFiles())->mapWithKeys(
            static function ($path) use ($disk) {
                return [
                    $path => [
                        'contents' => $disk->get($path),
                        'mode' => File::chmod($disk->path($path)),
                        'stat' => stat($disk->path($path)),
                        'path' => $path,
                    ],
                ];
            }
        );
    }
}
