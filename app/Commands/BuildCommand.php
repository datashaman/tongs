<?php

declare(strict_types=1);

namespace Datashaman\Tongs\Commands;

use Datashaman\Tongs\PackageManifest;
use Datashaman\Tongs\Tongs;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Arr;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\File;
use LaravelZero\Framework\Commands\Command;
use LaravelZero\Framework\Contracts\Providers\ComposerContract;
use Symfony\Component\Process\Process;

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
        $config = $this->config();
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

        $overrides = Arr::get($config, 'overrides', []);

        collect(Arr::get($config, 'plugins', []))
            ->each(
                function ($options, $key) use ($overrides, $plugins, $tongs) {
                    $class = Arr::get($overrides, $key, Arr::get($plugins, $key));

                    if (!$class) {
                        $this->error('Plugin not found: ' . $key);
                        exit(-1);
                    }

                    $plugin = $options === true ? new $class() : new $class($options);
                    $tongs->use($plugin);
                }
            );

        $files = $tongs->build();

        $this->info('Successfully built ' . count($files) . ' files');
    }

    protected function config(): array
    {
        $configFile = $this->option('config', 'tongs.json');

        if (!File::exists($configFile)) {
            $this->error('Cannot find file ' . $configFile);
            exit(-1);
        }

        $defaults = ['source' => 'src', 'destination' => 'build'];

        $config = json_decode(File::get($configFile), true, 512, JSON_THROW_ON_ERROR);

        return array_merge($defaults, $config);
    }

    protected function plugins(): array
    {
        $cwd = getcwd();

        if (file_exists("{$cwd}/vendor/autoload.php")) {
            require_once("{$cwd}/vendor/autoload.php");
        }

        $basePath = File::exists("{$cwd}/vendor/composer/installed.json")
            ? $cwd
            : base_path();
        $packagesPath = "{$cwd}/.cache/packages.php";

        $manifest = new PackageManifest(
            new Filesystem(),
            $basePath,
            $packagesPath
        );

        $manifest->build();

        $plugins = $manifest->plugins();

        if (File::exists("{$cwd}/composer.json")) {
            $plugins = array_merge(
                $plugins,
                json_decode(File::get("{$cwd}/composer.json"), true)
            );
        }

        return $plugins;
    }
}
