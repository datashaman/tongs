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
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = collect($options);
    }

    public function tongs(Tongs $tongs = null)
    {
        if (is_null($tongs)) {
            return $this->tongs;
        }

        $this->tongs = $tongs;

        return $this;
    }
}
