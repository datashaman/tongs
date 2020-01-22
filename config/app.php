<?php

return [
    'name' => 'Tongs',
    'production' => false,
    'providers' => [
        Datashaman\Tongs\Providers\ViewServiceProvider::class,
        Datashaman\Tongs\Providers\AppServiceProvider::class,
    ],
    'version' => app('git.version'),
];
