<?php

namespace Datashaman\Tongs\Plugins;

class TapPlugin extends Plugin
{
    public function handle(array $files, callable $next): array
    {
        dump($files);

        return $next($files);
    }
}
