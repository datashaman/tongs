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
        Pipes\MarkdownPipe::class,

        // Note this changes .sass and .scss filenames to .css
        Pipes\SassPipe::class,

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
            return $pipeline
                ->send($object)
                ->through($this->pipes);
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
