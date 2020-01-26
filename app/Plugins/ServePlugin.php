<?php

declare(strict_types=1);

namespace Datashaman\Tongs\Plugins;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

final class ServePlugin extends Plugin
{
    public function __construct(array $options = [])
    {
        $options = $this->normalize($options);

        parent::__construct($options);
    }

    public function handle(Collection $files, callable $next): Collection
    {
        $tongs = $this->tongs();

        $tongs->built(
            function (Collection $files) use ($tongs) {
                $destination = $tongs->destination();

                passthru("php -S {$this->options['host']}:{$this->options['port']} -t {$destination}");
            }
        );

        return $next($files);
    }

    protected function normalize(array $options): array
    {
        $defaults = [
            'host' => '127.0.0.1',
            'port' => 8000,
        ];

        return array_merge(
            $defaults,
            $options
        );
    }
}
