<?php

declare(strict_types=1);

namespace Datashaman\Tongs\Plugins;

use Illuminate\Filesystem\FilesystemAdapter;

abstract class Plugin
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var array
     */
    protected $options;

    /**
     * @param array $config
     * @param array $options
     */
    public function __construct(array $config, array $options = [])
    {
        $this->config = collect($config);
        $this->options = collect($options);
    }
}
