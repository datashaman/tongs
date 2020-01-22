<?php

namespace App\Pipes;

use Illuminate\Support\Arr;

class ViewPipe extends Pipe
{
    public function handle($files, $next)
    {
        $files = $files
            ->map(
                function ($file, $path) {
                    if ($view = Arr::get($file, 'data.view')) {
                        $file['contents'] = $this->view($view, $file);
                    }

                    return $file;
                }
            );

        return $next($files);
    }
}
