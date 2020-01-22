<?php

return [
    'name' => 'Tongs',
    'production' => true,
    'providers' => [
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,
        App\Providers\AppServiceProvider::class,
    ],
    'version' => app('git.version'),
];
