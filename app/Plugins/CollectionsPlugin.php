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

        $key = $this->options->get('key', 'collections');
        $metadata[$key] = function () use ($files) {
            return $this->collections($files);
        };

        $this->tongs()->metadata($metadata);

        return $next($files);
    }

    protected function collections(Collection $files): Collection
    {
        $keys = $this->options->keys();

        $collections = $this->options
            ->map(
                function() {
                    return [];
                }
            );

        $collections = $files
            ->keys()
            ->reduce(
                function ($collections, $path) use ($files, $keys) {
                    $file = $files[$path];

                    $metadata = collect(Arr::get($file, 'collection'))
                        ->reduce(
                            function ($collections, $key) use ($file, $keys) {
                                if ($keys->contains($key)) {
                                    $collection = $collections[$key];
                                } else {
                                    $keys->push($key);
                                    $collection = [];
                                }

                                $collection[] = $file;
                                $collections[$key] = $collection;

                                return $collections;
                            },
                            $collections
                        );

                    $collections = collect($this->options)
                        ->keys()
                        ->reduce(
                            function ($collections, $key) use (&$file, $path) {
                                $defn = $this->options[$key];

                                if (is_string($defn)) {
                                    $defn = [
                                        'pattern' => $defn,
                                    ];
                                }

                                $patterns = (array) $defn['pattern'];

                                if ($patterns) {
                                    foreach ($patterns as $pattern) {
                                        if (fnmatch($pattern, $path)) {
                                            $collection = $collections[$key];
                                            $collection[] = $file;
                                            $collections[$key] = $collection;
                                        }
                                    }
                                }

                                return $collections;
                            },
                            $collections
                        );

                    return $collections;
                },
                $collections
            );

        foreach ($keys as $key) {
            $settings = $this->options[$key];
            $sort = Arr::get($settings, 'sortBy', 'date');
            $collection = collect($collections[$key]);

            if (Arr::get($settings, 'reverse', true)) {
                $collection = $collection->sortByDesc($sort)->values();
            } else {
                $collection = $collection->sortBy($sort)->values();
            }

            $collection = Arr::get($settings, 'limit')
                ? $collection->take($settings['limit'])
                : $collection;

            $count = $collection->count();

            $collection = $collection->map(
                function ($file, $index) use ($collection, $count) {
                    if ($index === 0 && $count > 1) {
                        $file['next'] = $collection[1];
                    } else if ($index === $count - 1 && $count > 1) {
                        $file['previous'] = $collection[$index - 1];
                    } else {
                        $file['previous'] = $collection[$index - 1];
                        $file['next'] = $collection[$index + 1];
                    }

                    return $file;
                }
            );

            $collections[$key] = $collection->all();
        }

        return $collections;
    }
}
