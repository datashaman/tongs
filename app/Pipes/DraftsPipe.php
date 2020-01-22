<?php

declare(strict_types=1);

namespace App\Pipes;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

final class DraftsPipe extends Pipe
{
    public function handle(Collection $files, callable $next): Collection
    {
        $files = $files->reject(
            static function ($file) {
                return Arr::get($file, 'data.draft');
            }
        );

        return $next($files);
    }
}
