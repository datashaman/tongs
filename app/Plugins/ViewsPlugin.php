<?php

declare(strict_types=1);

namespace Datashaman\Tongs\Plugins;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;

final class ViewsPlugin extends Plugin
{
    public function __construct(array $config, array $options = [])
    {
        parent::__construct($config, $options);

        $this->registerViewFinder();
        $this->registerBladeCompiler();
    }

    protected function registerViewFinder()
    {
        app()->bind('view.finder', function ($app) {
            $paths = Arr::get(
                $this->options,
                'paths',
                [
                    'resources/views',
                ]
            );

            return new FileViewFinder($app['files'], $paths);
        });
    }

    protected function registerBladeCompiler()
    {
        app()->singleton('blade.compiler', function () {
            $compiled = Arr::get($this->options, 'compiled', 'storage/framework/views');

            return new BladeCompiler(
                app()->get('files'), $compiled
            );
        });
    }

    public function handle(Collection $files, callable $next): Collection
    {
        $files = $files
            ->map(
                function ($file) {
                    $view = Arr::get($file, 'data.view');

                    if ($view) {
                        $file['contents'] = $this->view($view, $file);
                    }

                    return $file;
                }
            );

        return $next($files);
    }

    /**
     * @param string $view
     * @param array $data
     *
     * @return string
     */
    protected function view(
        string $view,
        array $data = []
    ): string {
        return app(Factory::class)->make($view, $data)->render();
    }
}