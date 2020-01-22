<?php

declare(strict_types=1);

namespace App\Pipes;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

final class SassPipe extends Pipe
{
    public function handle(Collection $files, callable $next): Collection
    {
        $files = $files
            ->mapWithKeys(
                function ($file) {
                    $extension = File::extension($file['path']);
                    if (in_array($extension, ['sass', 'scss'])) {
                        $file['contents'] = $this->transform($file);
                        $file['path'] = preg_replace(
                            "/\.${extension}$/",
                            '.css',
                            $file['path']
                        );
                    }

                    return [$file['path'] => $file];
                }
            );

        return $next($files);
    }

    /**
     * @param array $file
     *
     * @return array
     */
    protected function transform(array $file): array
    {
        $cmd = $this->command($file);
        $process = new Process($cmd);
        $process->mustRun();

        return $process->getOutput();
    }

    /**
     * @param array $file
     *
     * @return array
     */
    protected function command(array $file): array
    {
        $fullPath = $this->source->path($file['path']);
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
