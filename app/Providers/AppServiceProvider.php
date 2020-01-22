<?php

namespace App\Providers;

use App\Pipes;
use Illuminate\Contracts\Pipeline\Hub;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * @var array
     */
    protected $pipes = [
        // Note this omits files from the build
        Pipes\DraftsPipe::class,

        // Note this changes .md filenames to .html
        [
            Pipes\MarkdownPipe::class,
            [
                'breaksEnabled' => true,
            ],
        ],

        // Note this changes .sass and .scss filenames to .css
        [
            Pipes\SassPipe::class,
            [
                'outputStyle' => 'expanded',
            ],
        ],

        // CollectionsPipe::class,

        // This should run last
        Pipes\ViewPipe::class,
    ];

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(Hub $hub)
    {
        $hub->defaults(function ($pipeline, $object) {
            $pipes = collect($this->pipes)
                ->map(function ($pipe) {
                    if (is_array($pipe) && is_array($pipe[1])) {
                        [$class, $options] = $pipe;
                        $pipe = new $class($options);
                    }

                    return $pipe;
                })
                ->all();
            return $pipeline->send($object)->through($pipes);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
