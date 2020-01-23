<?php

declare(strict_types=1);

namespace Datashaman\Tongs;

use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\Finder;
use Webuni\FrontMatter\FrontMatter;

class Tongs
{
    /**
     * @var array<Plugin>
     */
    protected $plugins = [];

    /**
     * @var array<string>
     */
    protected $ignores = [];

    /**
     * @var directory
     */
    protected $directory;

    /**
     * @var array
     */
    protected $metadata = [];

    /**
     * @var string
     */
    protected $source = 'src';

    /**
     * @var string
     */
    protected $destination = 'build';

    /**
     * @var bool
     */
    protected $clean = true;

    /**
     * @var bool
     */
    protected $frontmatter = true;

    /**
     * @var
    /**
     * @param string $directory The working directory path.
     */
    public function __construct(string $directory)
    {
        $this->directory = $directory;
    }

    /**
     * @param Plugins\Plugin $plugin
     *
     * @return self
     */
    public function use(Plugins\Plugin $plugin): self
    {
        array_push($this->plugins, $plugin);

        return $this;
    }

    /** * @param string $directory
     *
     * @return string|self
     */
    public function directory(string $directory = null)
    {
        if (is_null($directory)) {
            return realpath($this->directory);
        }

        $this->directory = $directory;

        return $this;
    }

    /**
     * @param array $metadata
     *
     * @return array|self
     */
    public function metadata(array $metadata = null)
    {
        if (is_null($metadata)) {
            return $this->metadata;
        }

        $this->metadata = $metadata;

        return $this;
    }

    /**
     * @param string $source
     *
     * @return string|self
     */
    public function source(string $path = null)
    {
        if (is_null($path)) {
            return $this->path($this->source);
        }

        $this->source = $path;

        return $this;
    }

    /**
     * @param string $destination
     *
     * @return string|self
     */
    public function destination(string $path = null)
    {
        if (is_null($path)) {
            return $this->path($this->destination);
        }

        $this->destination = $path;

        return $this;
    }

    /**
     * @param null|bool $clean
     *
     * @return bool|self
     */
    public function clean(bool $clean = null)
    {
        if (is_null($clean)) {
            return $this->clean;
        }

        $this->clean = $clean;

        return $this;
    }

    /**
     * @param null|bool $frontmatter
     *
     * @return bool|self
     */
    public function frontmatter(bool $frontmatter = null)
    {
        if (is_null($frontmatter)) {
            return $this->frontmatter;
        }

        $this->frontmatter = $frontmatter;

        return $this;
    }

    /**
     * @param array<string>|string $files
     *
     * @return array<string>|self
     */
    public function ignore($files = null)
    {
        if (is_null($files)) {
            return $this->ignores;
        }

        if (is_string($files)) {
            $files = [$files];
        }

        $this->ignores = array_merge(
            $this->ignores,
            $files
        );

        return $this;
    }

    /**
     * @return string
     */
    public function path(...$paths)
    {
        if (count($paths) && $paths[0][0] !== '/') {
            array_unshift($paths, $this->directory());
        }

        return implode(DIRECTORY_SEPARATOR, $paths);
    }

    public function build(callable $done = null)
    {
        try {
            if ($this->clean()) {
                File::deleteDirectory($this->destination());
            }

            $files = $this->process();
            $files = $this->write($files);

            if ($done) {
                $done(null, $files);
            }

            return $files;
        } catch (Exception $exception) {
            if ($done) {
                $done($exception);
            } else {
                throw $exception;
            }
        }
    }

    public function process(callable $done = null)
    {
        try {
            $files = $this->read();
            $files = $this->run($files);

            if ($done) {
                $done(null, $files);
            }

            return $files;
        } catch (Exception $exception) {
            if ($done) {
                $done($exception);
            } else {
                throw $exception;
            }
        }
    }

    public function plugins(): array
    {
        return $this->plugins;
    }

    protected function run(Collection $files, array $plugins = null)
    {
        $plugins = $plugins ?? $this->plugins;

        return (new Pipeline())
            ->send($files)
            ->through($plugins)
            ->thenReturn();
    }

    public function read(string $dir = null)
    {
        if (is_null($dir)) {
            $dir = $this->source();
        }

        $finder = new Finder();
        $finder
            ->files()
            ->followLinks()
            ->in($dir);

        return collect($finder)->mapWithKeys(
            function ($file) use ($dir) {
                $path = $file->getRelativePathname();

                $data = [
                    'mode' => substr(
                        base_convert((string) $file->getPerms(), 10, 8),
                        -4
                    ),
                ];

                $contents = $file->getContents();

                $path = preg_replace("#^{$dir}/#", '', $path);

                if ($this->frontmatter()) {
                    $frontMatter = new FrontMatter();
                    $document = $frontMatter->parse($contents);

                    $data = array_merge(
                        $data,
                        $document->getData(),
                    );

                    $contents = trim($document->getContent());
                }

                $data['contents'] = $contents;

                return [
                    $path => $data,
                ];
            }
        );

    }

    protected function write(Collection $files, string $dir = null): Collection
    {
        if (is_null($dir)) {
            $dir = $this->destination();
        }

        return $files
            ->map(
                function ($file) use ($dir) {
                    $fullPath = $dir . DIRECTORY_SEPARATOR . $file['path'];

                    File::makeDirectory(
                        File::dirname($fullPath),
                        0755,
                        true,
                        true
                    );

                    File::put($fullPath, $file['contents']);

                    return $file;
                }
            );
    }
}
