<?php

declare(strict_types=1);

namespace Datashaman\Tongs\Plugins;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Parsedown;

final class MarkdownPlugin extends Plugin
{
    public function handle(Collection $files, callable $next): Collection
    {
        $files = $files
            ->mapWithKeys(
                function ($file, $path) {
                    if (File::extension($path) === 'md') {
                        return $this->transform($file, $path);
                    }

                    return [
                        $path => $file,
                    ];
                }
            );

        return $next($files);
    }

    protected function transform(array $file, string $path): array
    {
        $parser = $this->getParser();

        $file['contents'] = $parser->text($file['contents']);
        $path = preg_replace('/\.md$/', '.html', $path);

        return [
            $path => $file,
        ];
    }

    protected function getParser(): Parsedown
    {
        $parser = new Parsedown();

        $this->options
            ->each(
                static function ($value, $key) use ($parser) {
                    $methodName = 'set' . ucfirst($key);
                    $parser->$methodName($value);
                }
            );

        return $parser;
    }
}
