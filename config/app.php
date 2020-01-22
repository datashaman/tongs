<?php

return [
    'name' => 'Tongs',
    'production' => false,
    'providers' => [
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,
        App\Providers\AppServiceProvider::class,
    ],
    'version' => app('git.version'),
];
