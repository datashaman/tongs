<?php

declare(strict_types=1);

namespace Datashaman\Tongs\Plugins;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Parsedown;
use Webuni\FrontMatter\FrontMatter;

final class MarkdownPlugin extends Plugin
{
    public function handle(Collection $files, callable $next): Collection
    {
        $files = $files
            ->mapWithKeys(
                function ($file) {
                    if (File::extension("{$this->config['source']}/{$file['path']}") === 'md') {
                        $file = $this->transform($file);
                    }

                    return [
                        $file['path'] => $file,
                    ];
                }
            );

        return $next($files);
    }

    protected function transform(array $file): array
    {
        $frontMatter = new FrontMatter();
        $document = $frontMatter->parse($file['contents']);

        $file['data'] = $document->getData();
        $contents = $document->getContent();

        $parser = $this->getParser();

        $file['contents'] = $parser->text($contents);
        $file['path'] = preg_replace('/\.md$/', '.html', $file['path']);

        return $file;
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
