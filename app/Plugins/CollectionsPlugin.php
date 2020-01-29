<?php

declare(strict_types=1);

namespace Datashaman\Tongs\Plugins;

use DateTime;
use Illuminate\Support\Arr;

function fileCmp($a, $b) {
    if ($a instanceof DateTime) {
        $a = $a->format(DateTime::ATOM);
    }

    if ($b instanceof DateTime) {
        $b = $b->format(DateTime::ATOM);
    }

    return strcmp($a, $b);
}

final class CollectionsPlugin extends Plugin
{
    public function handle(array $files, callable $next): array
    {
        $metadata = $this->tongs()->metadata();

        $key = $this->options->get('key', 'collections');
        $metadata[$key] = function () use ($files) {
            return $this->collections($files);
        };

        $this->tongs()->metadata($metadata);

        return $next($files);
    }

    protected function collections(array $files): array
    {
        $collections = [];

        foreach ($this->options->keys() as $key) {
            $collections[$key] = [];
        }

        foreach ($files as $path => $file) {
            if (isset($file['collection'])) {
                foreach ((array) $file['collection'] as $key) {
                    if (!isset($this->options[$key])) {
                        $this->options[$key] = [];
                        $collections[$key] = [];
                    }

                    $collections[$key][$path] = $file;
                }
            }

            foreach ($this->options as $key => $defn) {
                if (is_string($defn)) {
                    $defn = ['pattern' => $defn];
                }

                $patterns = (array) ($defn['pattern'] ?? []);

                foreach ($patterns as $pattern) {
                    if (fnmatch($pattern, $path)) {
                        $collections[$key][$path] = $file;
                    }
                }
            }
        }

        foreach ($this->options as $key => $settings) {
            if (count($collections[$key]) > 1) {
                $sort = $settings['sortBy'] ?? 'date';
                $reverse = $settings['reverse'] ?? true;

                if ($reverse) {
                    uasort(
                        $collections[$key],
                        function ($b, $a) use ($sort) {
                            return fileCmp($a[$sort], $b[$sort]);
                        }
                    );
                } else {
                    uasort(
                        $collections[$key],
                        function ($a, $b) use ($sort) {
                            return fileCmp($a[$sort], $b[$sort]);
                        }
                    );
                }
            }

            if (isset($settings['limit'])) {
                $collections[$key] = array_slice(
                    $collections[$key],
                    0,
                    $settings['limit']
                );
            }

            $count = count($collections[$key]);

            if ($count > 1) {
                foreach ($collections[$key] as $index => &$file) {
                    if ($index === 0 && $count > 1) {
                        $file['next'] = $collections[$key][1];
                    } else if ($index === $count - 1 && $count > 1) {
                        $file['previous'] = $collections[$key][$index - 1];
                    } else {
                        $file['previous'] = $collections[$key][$index - 1];
                        $file['next'] = $collections[$key][$index + 1];
                    }
                }
            }
        }

        return $collections;
    }
}
