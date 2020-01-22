<?php

return [
    'add' => [
        // ..
    ],
    'default' => Datashaman\Tongs\Commands\BuildCommand::class,
    'hidden' => [
        NunoMaduro\LaravelConsoleSummary\SummaryCommand::class,
        Symfony\Component\Console\Command\HelpCommand::class,
        Illuminate\Console\Scheduling\ScheduleRunCommand::class,
        Illuminate\Console\Scheduling\ScheduleFinishCommand::class,
        Illuminate\Foundation\Console\VendorPublishCommand::class,
    ],
    'paths' => [
        app_path('Commands'),
    ],
    'remove' => [
        // ..
    ],
];
