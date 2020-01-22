<?php

namespace App\Pipes;

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
     * @var FilesystemAdapter
     */
    protected $source;

    /**
     * @var Factory
     */
    protected $factory;

    public function __construct(array $options = [])
    {
        $this->options = collect($options);
        $this->source = Storage::disk(config('tongs.source', 'source'));
        $this->factory = resolve(Factory::class);
    }

    protected function view($view, $data = [])
    {
        return $this->factory->make($view, $data)->render();
    }
}
