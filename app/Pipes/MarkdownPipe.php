<?php

namespace App\Pipes;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\File;
use Parsedown;
use Webuni\FrontMatter\FrontMatter;

class MarkdownPipe extends Pipe
{
    public function handle($files, $next)
    {
        $files = $files
            ->mapWithKeys(
                function ($file, $path) {
                    if (File::extension($this->source->path($path)) === 'md') {
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

        $parser = new Parsedown();

        $this->options
            ->each(
                function ($value, $key) use ($parser) {
                    $methodName = 'set' . ucfirst($key);
                    $parser->$methodName($value);
                }
            );

        $file['contents'] = $parser->text($contents);
        $file['path'] = preg_replace('/\.md$/', '.html', $file['path']);

        return $file;
    }
}
