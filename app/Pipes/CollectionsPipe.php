<?php

namespace App\Pipes;

class CollectionsPipe extends Pipe
{
    public function handle($files, $next)
    {
        $files = $files
            ->map(
                function ($file, $path) {
                    collect($this->options)
                        ->each(
                            function ($defn) {
                                if (fnmatch($defn['match'], $path)) {
                                    // add metadata for collections
                                }
                            }
                        );
                }
            );

        return $next($files);
    }
}
