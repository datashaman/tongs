<?php

declare(strict_types=1);

namespace Datashaman\Tongs\Plugins;

use Illuminate\Support\Facades\File;
use Parsedown;

final class MarkdownPlugin extends Plugin
{
    public function handle(array $files, callable $next): array
    {
        $ret = [];

        foreach ($files as $path => $file) {
            if (File::extension($path) === 'md') {
                $path = preg_replace('/\.md$/', '.html', $path);

                $parser = $this->getParser();
                $file['contents'] = $parser->text($file['contents']);
            }

            $ret[$path] = $file;
        }

        return $next($ret);
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
