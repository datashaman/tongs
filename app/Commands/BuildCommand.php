<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Pipeline\Hub;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use LaravelZero\Framework\Commands\Command;

class BuildCommand extends Command
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
        $files = $hub->pipe($this->prepareFiles())->then(function ($files) {
            return $files->map(function ($file) {
                $destination = config('tongs.destination', 'build');
                $disk = Storage::disk($destination);

                $disk->put($file['path'], $file['contents']);

                return $file;
            });
        });

        $this->info(
            $files->count() .
                ' files written to ' .
                Storage::disk(config('tongs.destination', 'build'))->path(''),
        );
    }

    protected function prepareFiles(): Collection
    {
        $source = config('tongs.source', 'source');
        $disk = Storage::disk($source);

        return collect($disk->allFiles())->mapWithKeys(function ($path) use (
            $disk
        ) {
            $file = [
                'contents' => $disk->get($path),
                'mode' => $this->app['files']->chmod($disk->path($path)),
                'stat' => stat($disk->path($path)),
                'path' => $path,
            ];

            return [
                $path => $file,
            ];
        });
    }
}
