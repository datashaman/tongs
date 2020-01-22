<?php

declare(strict_types=1);

namespace App\Pipes;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Factory;

abstract class Pipe
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

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = collect($options);
        $this->source = Storage::disk(config('tongs.source', 'source'));
        $this->factory = resolve(Factory::class);
    }

    /**
     * @param string $view
     * @param array $data
     *
     * @return string
     */
    protected function view(
        string $view,
        array $data = []
    ): string {
        return $this->factory->make($view, $data)->render();
    }
}
