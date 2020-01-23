<?php

declare(strict_types=1);

namespace Datashaman\Tongs\Plugins;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

final class DraftsPlugin extends Plugin
{
    public function handle(Collection $files, callable $next): Collection
    {
        $files = $files->reject(
            static function ($file) {
                return Arr::get($file, 'draft');
            }
        );

        return $next($files);
    }
}
