<?php

namespace Datashaman\Tongs\Plugins;

use Illuminate\Support\Collection;

class TapPlugin extends Plugin
{
    public function handle(Collection $files, callable $next): Collection
    {
        dump($files);

        return $next($files);
    }
}
