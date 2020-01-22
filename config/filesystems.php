<?php

return [
    'default' => 'source',
    'disks' => [
        'source' => [
            'driver' => 'local',
            'root' => base_path('src'),
        ],
        'build' => [
            'driver' => 'local',
            'root' => base_path('build'),
        ],
    ],
];
