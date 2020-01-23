<?php

declare(strict_types=1);

namespace Datashaman\Tongs\Commands;

use Datashaman\Tongs\PackageManifest;
use Datashaman\Tongs\Tongs;
use Illuminate\Filesystem\Filesystem;
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
        $plugins = $this->plugins();
        $tongs = new Tongs(getcwd());

        if (Arr::has($config, 'source')) {
            $tongs->source($config['source']);
        }

        if (Arr::has($config, 'destination')) {
            $tongs->destination($config['destination']);
        }

        if (Arr::has($config, 'metadata')) {
            $tongs->metadata($config['metadata']);
        }

        if (Arr::has($config, 'clean')) {
            $tongs->clean($config['clean']);
        }

        if (Arr::has($config, 'frontmatter')) {
            $tongs->frontmatter($config['frontmatter']);
        }

        if (Arr::has($config, 'ignore')) {
            $tongs->ignore($config['ignore']);
        }

        collect(Arr::get($config, 'plugins', []))
            ->each(
                function ($options, $key) use ($plugins, $tongs) {
                    $class = Arr::get($plugins, $key, $key);

                    $plugin = $options === true
                        ? new $class($tongs)
                        : new $class($tongs, $options);

                    $tongs->use($plugin);
                }
            );

        $files = $tongs->build();

        $this->info('Successfully built ' . $files->count() . ' files to ' . $tongs->destination());
    }

    protected function plugins(): array
    {
        $manifest = new PackageManifest(
            new Filesystem, $this->app->basePath(), $this->app->getCachedPackagesPath()
        );

        $manifest->build();

        return $manifest->plugins();
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

}
