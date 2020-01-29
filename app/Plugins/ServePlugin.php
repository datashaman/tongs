<?php

declare(strict_types=1);

namespace Datashaman\Tongs\Plugins;

final class ServePlugin extends Plugin
{
    public function __construct(array $options = [])
    {
        $options = $this->normalize($options);

        parent::__construct($options);
    }

    public function handle(array $files, callable $next): array
    {
        $tongs = $this->tongs();

        $tongs->built(
            function (array $files) use ($tongs) {
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
