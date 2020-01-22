<?php

declare(strict_types=1);

namespace App\Pipes;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

final class ViewPipe extends Pipe
{
    public function handle(Collection $files, callable $next): Collection
    {
        $files = $files
            ->map(
                function ($file) {
                    $view = Arr::get($file, 'data.view');

                    if ($view) {
                        $file['contents'] = $this->view($view, $file);
                    }

                    return $file;
                }
            );

        return $next($files);
    }
}
