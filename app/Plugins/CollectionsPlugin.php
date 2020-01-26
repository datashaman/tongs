<?php

declare(strict_types=1);

namespace Datashaman\Tongs\Plugins;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

final class CollectionsPlugin extends Plugin
{
    public function handle(Collection $files, callable $next): Collection
    {
        $metadata = $this->tongs()->metadata();

        $keys = $this->options->keys();

        foreach ($keys as $key) {
            $metadata[$key] = [];
        }

        $files
            ->each(
                function ($file, $path) use ($keys, &$metadata) {
                    collect(Arr::get($file, 'collection'))
                        ->each(
                            function ($key) use ($file, $keys, &$metadata) {
                                if (!$keys->contains($key)) {
                                    $keys->push($key);
                                    $metadata[$key] = [];
                                }
                                $metadata[$key][] = $file;
                            }
                        );

                    collect($this->options)
                        ->each(
                            static function ($defn, $key) use ($file, &$metadata, $path): void {
                                if (is_string($defn)) {
                                    $defn = [
                                        'pattern' => $defn,
                                    ];
                                }

                                if (Arr::get($defn, 'pattern') && fnmatch($defn['pattern'], $path)) {
                                    $metadata[$key][] = $file;
                                }
                            }
                        );
                }
            );

        $metadata['collections'] = [];

        foreach ($keys as $key) {
            $metadata['collections'][$key] = $metadata[$key];
        }

        $this->tongs()->metadata($metadata);

        return $next($files);
    }
}
