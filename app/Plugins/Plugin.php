<?php

declare(strict_types=1);

namespace Datashaman\Tongs\Plugins;

use Datashaman\Tongs\Tongs;
use Illuminate\Filesystem\FilesystemAdapter;

abstract class Plugin
{
    /**
     * @var Tongs
     */
    protected $tongs;

    /**
     * @var array
     */
    protected $options;

    /**
     * @param Tongs $tongs
     * @param array $options
     */
    public function __construct(Tongs $tongs, array $options = [])
    {
        $this->tongs = $tongs;
        $this->options = collect($options);
    }
}
