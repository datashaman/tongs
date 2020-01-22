<?php

namespace App\Pipes;

use Illuminate\Filesystem\Filesystem;

class SassPipe extends Pipe
{
    public function handle($files, $next)
    {
        $files = $files
            ->mapWithKeys(
                function ($file, $path) {
                    $extension = $this->filesystem->extension($path);

                    if (in_array($extension, ['sass', 'scss'])) {
                        $fullPath = $this->source->path($path);

                        $file['contents']= `node-sass $fullPath`;
                        $file['path'] = preg_replace("/\.{$extension}$/", '.css', $file['path']);
                    }

                    return [
                        $file['path'] => $file,
                    ];
                }
            );

        return $next($files);
    }
}
