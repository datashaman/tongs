<?php

namespace App\Pipes;

use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Factory;

class Pipe
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var FilesystemAdapter
     */
    protected $source;

    /**
     * @var Factory
     */
    protected $factory;

    public function __construct(
        array $options = [],
        Filesystem $filesystem,
        Factory $factory
    ) {
        $this->options = $options;
        $this->filesystem = $filesystem;
        $this->source = Storage::disk(config('tongs.source', 'source'));
        $this->factory = $factory;
    }

    protected function view($view, $data = [])
    {
        return $this->factory->make($view, $data)->render();
    }
}
