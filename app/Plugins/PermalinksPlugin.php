<?php

declare(strict_types=1);

namespace Datashaman\Tongs\Plugins;

use Datashaman\Tongs\Tongs;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class PermalinksPlugin extends Plugin
{
    /**
     * @var array
     */
    protected $linksets = [];

    /**
     * @var array
     */
    protected $defaultLinkset;

    /**
     * @var array
     */
    protected $dupes;

    public function __construct(Tongs $tongs, $options = null)
    {
        $options = $this->normalize($options);

        parent::__construct($tongs, $options);

        $this->linksets = $options['linksets'];

        $this->defaultLinkset = collect($this->linksets)
            ->first(
                function ($ls) {
                    return (bool) Arr::get($ls, 'isDefault');
                }
            );

        if ($this->defaultLinkset === false) {
            $this->defaultLinkset = $options;
        }

        $this->dupes = [];
    }

    protected function normalize($options): array
    {
        if (is_string($options)) {
            $options = [
                'pattern' => $options,
            ];
        }

        $options = $options ?: [];

        $options['date'] = $options['date'] ?: 'Y/m/d';

        if (!Arr::has($options, 'relative')) {
            $options['relative'] = true;
        }

        $options['linksets'] = $options['linksets'] ?: [];

        return $options;
    }

    public function handle(Collection $files, callable $next): Collection
    {
        $files = $files
            ->map(
                function ($file, $path) {
                    return $file;
                }
            );

        return $next($files);
    }
}
