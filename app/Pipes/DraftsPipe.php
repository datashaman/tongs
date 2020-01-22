<?php

namespace App\Pipes;

use Illuminate\Support\Arr;

class DraftsPipe extends Pipe
{
    public function handle($files, $next)
    {
        $files = $files->reject(
            function ($file) {
                return Arr::get($file, 'data.draft');
            }
        );

        return $next($files);
    }
}
