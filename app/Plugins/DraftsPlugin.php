<?php

declare(strict_types=1);

namespace Datashaman\Tongs\Plugins;

use Illuminate\Support\Arr;

final class DraftsPlugin extends Plugin
{
    public function handle(array $files, callable $next): array
    {
        $files = array_filter(
            function ($file) {
                return $file['draft'] ?? false;
            },
            $files
        );

        return $next($files);
    }
}
