<?php

declare(strict_types=1);

namespace Datashaman\Tongs;

use Exception;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\FilesystemInterface;
use Symfony\Component\Filesystem\Filesystem;
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
        $this->source('src');
        $this->destination('build');
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
            return collect($this->metadata)
                ->map('value')
                ->all();
        }

        $this->metadata = $metadata;

        return $this;
    }

    /**
     * @param array|string $source
     *
     * @return FilesystemInterface|self
     */
    public function source($source = null)
    {
        if (is_null($source)) {
            return Storage::disk('source');
        }

        if (is_string($source)) {
            $filesystem = resolve(Filesystem::class);

            $root = $filesystem->isAbsolutePath($source)
                ? $source
                : "{$this->directory()}/{$source}";

            $source = [
                'driver' => 'local',
                'root' => $root,
            ];
        }

        config(['filesystems.disks.source' => $source]);

        return $this;
    }

    /**
     * @param array|string $destination
     *
     * @return FilesystemInterface|self
     */
    public function destination($destination = null)
    {
        if (is_null($destination)) {
            return Storage::disk('destination');
        }

        if (is_string($destination)) {
            $filesystem = resolve(Filesystem::class);

            $root = $filesystem->isAbsolutePath($destination)
                ? $destination
                : "{$this->directory()}/{$destination}";

            $destination = [
                'driver' => 'local',
                'root' => $root,
            ];
        }

        config(['filesystems.disks.destination' => $destination]);

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

    public function build()
    {
        if ($this->clean()) {
            collect($this->destination()->listContents('/', true))
                ->each(
                    function ($entry) {
                        $this->destination()->delete($entry['path']);
                    }
                );
        }

        $files = $this->process();
        $files = $this->write($files->all());

        collect($this->built)
            ->each(
                function ($callable) use ($files) {
                    $callable($files);
                }
            );

        return $files;
    }

    public function process()
    {
        $files = $this->read();
        $files = $this->run($files->all());

        return $files;
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

    public function read(string $dir = '/')
    {
        $entries = $this->source()->listContents($dir, true);

        return collect($entries)
            ->reject(
                function ($entry) {
                    return $entry['basename'] === $entry['filename'] && !isset($entry['size']);
                }
            )
            ->mapWithKeys(
                function ($entry) {
                    return [
                        $entry['path'] => $this->readFile($entry['path']),
                    ];
                }
            );
    }

    public function readFile(string $path): array
    {
        $ret = [];

        $contents = $this->source()->get($path);

        if ($this->frontmatter() && File::extension($path) === 'md') {
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

        return $ret;
    }

    public function write(array $files): Collection
    {
        return collect($files)
            ->map(
                function ($file, $path) {
                    return $this->writeFile($path, $file);
                }
            );
    }

    public function writeFile(string $path, array $file)
    {
        $this->destination()->put(
            $path,
            $file['contents']
        );

        return $file;
    }

    public function built(callable $callable)
    {
        $this->built[] = $callable;

        return $this;
    }
}
