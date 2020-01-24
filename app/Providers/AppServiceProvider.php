<?php

declare(strict_types=1);

namespace Datashaman\Tongs\Providers;

use Datashaman\Tongs\PackageManifest;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind(
            \Illuminate\Foundation\PackageManifest::class,
            PackageManifest::class
        );
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
