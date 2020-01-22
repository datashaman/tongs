<?php

declare(strict_types=1);

namespace Datashaman\Tongs\Providers;

final class ViewServiceProvider extends \Illuminate\View\ViewServiceProvider
{
    /**
     * @return void
     */
    public function register()
    {
        $this->registerFactory();
        $this->registerEngineResolver();
    }
}
