<?php

namespace App\Pipes;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SassPipe extends Pipe
{
    public function handle($files, $next)
    {
        $files = $files
            ->mapWithKeys(
                function ($file, $path) {
                    $extension = File::extension($path);

                    if (in_array($extension, ['sass', 'scss'])) {
                        $fullPath = $this->source->path($path);

                        $options = $this->options
                            ->map(
                                function ($value, $key) {
                                    $option = '--' . Str::slug(Str::snake($key));

                                    return "'$option=$value'";
                                }
                            )
                            ->join(' ');

                        $file['contents']= `node-sass $options $fullPath`;
                        $file['path'] = preg_replace(
                            "/\.{$extension}$/",
                            '.css',
                            $file['path']
                        );
                    }

                    return [
                        $file['path'] => $file,
                    ];
                }
            );

        return $next($files);
    }
}
