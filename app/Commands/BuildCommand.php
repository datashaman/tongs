<?php

declare(strict_types=1);

namespace Datashaman\Tongs\Commands;

use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use LaravelZero\Framework\Commands\Command;

final class BuildCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'build {--config=tongs.json}';

    /**
     * @var string
     */
    protected $description = 'Build static site.';

    public function handle()
    {
        $config = $this->getConfig();
        $files = $this->runPipeline($config);

        $this->info(
            $files->count() .
                ' files written to ' .
                Arr::get($config, 'destination', 'build'),
        );
    }

    protected function getConfig(): array
    {
        $configFile = $this->option('config', 'tongs.json');

        if (!File::exists($configFile)) {
            $this->error('Cannot find file ' . $configFile);
            exit(-1);
        }

        $defaults = ['source' => 'src', 'destination' => 'build'];

        $config = json_decode(File::get($configFile), true);
        return array_merge($defaults, $config);
    }

    protected function runPipeline(array $config): Collection
    {
        $plugins = collect(Arr::get($config, 'plugins', []))
            ->map(
                static function ($options, $class) use ($config) {
                    if ($options === true) {
                        return new $class($config);
                    }

                    return new $class($config, $options);
                }
            )
            ->all();

        $files = $this->prepareFiles($config);

        return (new Pipeline($this->app))
            ->send($files)
            ->through($plugins)
            ->then(
                static function ($files) use ($config) {
                    return $files->map(
                        static function ($file) use ($config) {
                            $fullPath = "${config['destination']}/${file['path']}";
                            File::makeDirectory(
                                File::dirname($fullPath),
                                0755,
                                true,
                                true
                            );
                            File::put($fullPath, $file['contents']);

                            return $file;
                        }
                    );
                }
            );
    }

    protected function prepareFiles(array $config): Collection
    {
        $source = Arr::get($config, 'source', 'src');
        $allFiles = File::allFiles($source);

        return collect($allFiles)->mapWithKeys(
            static function ($file) {
                $path = $file->getRelativePathname();

                return [
                    $path => [
                        'contents' => $file->getContents(),
                        'path' => $path,
                    ],
                ];
            }
        );
    }
}
