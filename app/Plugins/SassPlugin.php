<?php

declare(strict_types=1);

namespace Datashaman\Tongs\Plugins;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

final class SassPlugin extends Plugin
{
    public function handle(Collection $files, callable $next): Collection
    {
        $files = $files
            ->mapWithKeys(
                function (array $file, string $path) {
                    $extension = File::extension($path);

                    if (in_array($extension, ['sass', 'scss'])) {
                        return $this->transform($file, $path, $extension);
                    }

                    return [$path => $file];
                }
            );

        return $next($files);
    }

    /**
     * @param array $file
     * @param string $path
     * @param string $extension
     *
     * @return array
     */
    protected function transform(array $file, string $path, string $extension): array
    {
        $cmd = $this->command($file, $path);

        $process = new Process($cmd);
        $process->mustRun();

        $path = preg_replace(
            "/\.${extension}$/",
            '.css',
            $path
        );

        $file['contents'] = $process->getOutput();

        return [
            $path => $file,
        ];
    }

    /**
     * @param array $file
     * @param string $path
     *
     * @return array
     */
    protected function command(array $file, string $path): array
    {
        $fullPath = $this->tongs()->source() . DIRECTORY_SEPARATOR . $path;

        $options = $this->options
            ->map(
                static function ($value, $key) {
                    $option = '--' . Str::slug(Str::snake($key));

                    return "${option}='${value}'";
                }
            )
            ->all();

        return array_merge(['node-sass'], $options, [$fullPath]);
    }
}
