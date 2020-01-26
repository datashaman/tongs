<?php

declare(strict_types=1);

namespace Datashaman\Tongs;

use Exception;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use SplFileInfo;
use Symfony\Component\Filesystem\Filesystem;
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
     * @var array<callable>
     */
    protected $built = [];

    /**
     * @var
     *
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
        $plugin->tongs($this);
        $this->plugins[] = $plugin;

        return $this;
    }

    /** * @param string $directory
     *
     * @return string|self
     */
    public function directory(string $directory = null)
    {
        if (is_null($directory)) {
            return $this->directory;
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
            $files = $this->write($files->all());

            if ($done) {
                $done(null, $files);
            }

            collect($this->built)
                ->each(
                    function ($callable) use ($files) {
                        $callable($files);
                    }
                );

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
            $files = $this->run($files->all());

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

    public function run(array $files, array $plugins = null)
    {
        $plugins = $plugins ?: $this->plugins();

        return (new Pipeline())
            ->send(collect($files))
            ->through($plugins)
            ->thenReturn();
    }

    public function read(string $dir = null)
    {
        $dir = $dir ?: $this->source();

        $finder = new Finder();
        $finder
            ->files()
            ->notPath($this->ignore())
            ->followLinks()
            ->in($dir);

        return collect($finder)->mapWithKeys(
            function ($file) {
                return [
                    $file->getRelativePathname() => $this->readFile($file->getPathname()),
                ];
            }
        );
    }

    public function readFile(string $file): array
    {
        $src = $this->source();
        $ret = [];

        $filesystem = resolve(Filesystem::class);
        if (!($filesystem->isAbsolutePath($file))) {
            $file = "{$src}/{$file}";
        }

        $contents = File::get($file);

        if ($this->frontmatter() && File::extension($file) === 'md') {
            $processor = new YamlProcessor();
            $frontMatter = new FrontMatter($processor);
            $document = $frontMatter->parse($contents);

            $ret = $document->getData() ?? [];

            if (!is_array($ret)) {
                throw new Exception('Invalid frontmatter');
            }

            $contents = trim($document->getContent());
        }

        $ret['contents'] = $contents;
        $fileInfo = new SplFileInfo($file);
        $ret['mode'] = substr(base_convert((string) $fileInfo->getPerms(), 10, 8), -4);

        return $ret;
    }

    public function write(array $files, string $dir = null): Collection
    {
        $dir = $dir ?: $this->destination();

        return collect($files)
            ->map(
                function ($file, $key) use ($dir) {
                    $pathname = (new SplFileInfo("{$dir}/{$key}"))->getPathname();

                    return $this->writeFile($pathname, $file);
                }
            );
    }

    public function writeFile(string $path, array $file)
    {
        $dest = $this->destination();

        $filesystem = resolve(Filesystem::class);
        if (!($filesystem->isAbsolutePath($path))) {
            $path = "{$dest}/{$path}";
        }

        File::makeDirectory(
            File::dirname($path),
            0755,
            true,
            true
        );

        File::put($path, $file['contents']);

        if (Arr::has($file, 'mode')) {
            File::chmod($path, octdec($file['mode']));
        }

        return $file;
    }

    public function built(callable $callable)
    {
        $this->built[] = $callable;

        return $this;
    }
}
