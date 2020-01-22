<?php

declare(strict_types=1);

namespace Datashaman\Tongs\Plugins;

use Illuminate\Support\Collection;

final class CollectionsPlugin extends Plugin
{
    public function handle(Collection $files, callable $next): Collection
    {
        $metadata = $this->tongs->metadata();

        $this->options
            ->keys()
            ->each(
                function ($key) use (&$metadata) {
                    $metadata[$key] = [];
                }
            );

        $files = $files
            ->map(
                function ($file) {
                    collect($this->options)
                        ->each(
                            static function ($defn, $name): void {
                                if (is_string($defn)) {
                                    $defn = [
                                        'match' => $defn,
                                    ];
                                }

                                $matches = fnmatch($defn['match'], $file['path']);

                                if ($matches) {
                                    collect($matches)
                                        ->each(
                                            function ($match) {
                                                dd($match);
                                            }
                                        );
                                }
                            }
                        );
                }
            );

        return $next($files);
    }
}
