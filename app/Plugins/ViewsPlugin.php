<?php

declare(strict_types=1);

namespace Datashaman\Tongs\Plugins;

use Datashaman\Tongs\Tongs;
use Illuminate\Support\Arr;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;

final class ViewsPlugin extends Plugin
{
    public function __construct(array $options = [])
    {
        parent::__construct($options);

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
        app()->singleton('blade.compiler', function ($app) {
            $compiled = Arr::get($this->options, 'compiled', 'storage/framework/views');

            return new BladeCompiler(
                $app['files'],
                $compiled
            );
        });
    }

    public function handle(array $files, callable $next): array
    {
        foreach ($files as &$file) {
            if (isset($file['view'])) {
                $locals = array_merge(
                    $this->tongs()->metadata(),
                    $file
                );

                $file['contents'] = $this->view($file['view'], $locals);
            }
        }

        return $next($files);
    }

    /**
     * @param string $view
     * @param array $locals
     *
     * @return string
     */
    protected function view(
        string $view,
        array $locals = []
    ): string {
        return app(Factory::class)->make($view, $locals)->render();
    }
}
