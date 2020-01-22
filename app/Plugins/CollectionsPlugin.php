<?php

declare(strict_types=1);

namespace Datashaman\Tongs\Plugins;

use Illuminate\Support\Collection;

final class CollectionsPlugin extends Plugin
{
    public function handle(Collection $files, callable $next): Collection
    {
        $files = $files
            ->map(
                function ($file, $path) {
                    collect($this->options)
                        ->each(
                            static function ($defn): void {
                                if (fnmatch($defn['match'], $path)) {
                                    // add metadata for collections
                                }
                            }
                        );
                }
            );

        return $next($files);
    }
}