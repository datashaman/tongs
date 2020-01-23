<?php

declare(strict_types=1);

namespace Datashaman\Tongs\Commands;

use Datashaman\Tongs\PackageManifest;
use Datashaman\Tongs\Tongs;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Env;
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

    /**
     * Normalize a relative or absolute path to a cache file.
     *
     * @param  string  $key
     * @param  string  $default
     * @return string
     */
    protected function normalizeCachePath($key, $default)
    {
        if (is_null($env = Env::get($key))) {
            return $this->bootstrapPath($default);
        }

        return Str::startsWith($env, '/')
                ? $env
                : $this->basePath($env);
    }

    /**
     * Get the path to the bootstrap directory.
     *
     * @param  string  $path Optionally, a path to append to the bootstrap path
     * @return string
     */
    public function bootstrapPath($path = '')
    {
        return getcwd().DIRECTORY_SEPARATOR.'bootstrap'.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    /**
     * Get the path to the cached packages.php file.
     *
     * @return string
     */
    public function getCachedPackagesPath()
    {
        return $this->normalizeCachePath('APP_PACKAGES_CACHE', 'cache/packages.php');
    }

    protected function plugins(): array
    {
        $manifest = new PackageManifest(
            new Filesystem, getcwd(), $this->getCachedPackagesPath()
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
