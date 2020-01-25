<?php

declare(strict_types=1);

namespace Datashaman\Tongs\Plugins;

use Illuminate\Support\Collection;

final class CollectionsPlugin extends Plugin
{
    public function handle(Collection $files, callable $next): Collection
    {
        $metadata = $this->tongs->metadata();

        $keys = $this->options->keys();

        $keys
            ->each(
                function ($key) use (&$metadata) {
                    $metadata[$key] = [];
                }
            );

        $files
            ->each(
                function ($file) use (&$metadata) {
                    collect(Arr::get($file, 'collection'))
                        ->each(
                            function ($key) use (&$metadata) {
                                $metadata[$key] = $metadata[$key] ?? [];
                                array_push($metadata[$key], $file);
                            }
                        );

                    collect($this->options)
                        ->each(
                            static function ($defn, $key) use ($file, &$metadata): void {
                                if (is_string($defn)) {
                                    $defn = [
                                        'pattern' => $defn,
                                    ];
                                }

                                if (Arr::get($defn, 'pattern') && fnmatch($defn['pattern'], $file['path'])) {
                                    $metadata[$key] = $metadata[$key] ?? [];
                                    array_push($metadata[$key], $file);
                                }
                            }
                        );
                }
            );

        $keys
            ->each(
                function ($key) use (&$metadata) {
                    $metadata[$key] = $metadata[$key] ?? [];
                }
            );

        $metadata['collections'] = [];

        $keys
            ->each(
                function ($key) use (&$metadata) {
                    $metadata['collections'][$key] = $metadata[$key];
                }
            );

        $this->tongs->metadata($metadata);

        return $next($files);
    }
}
