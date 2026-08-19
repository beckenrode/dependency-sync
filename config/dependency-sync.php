<?php

return [
    'token' => env('DEPENDENCY_SYNC_TOKEN'),
    'endpoint' => env('DEPENDENCY_SYNC_ENDPOINT'),

    'timeout' => (int) env('DEPENDENCY_SYNC_TIMEOUT', 30),

    'schedule' => [
        'enabled' => (bool) env('DEPENDENCY_SYNC_SCHEDULE_ENABLED', false),
        'cron' => env('DEPENDENCY_SYNC_SCHEDULE', '0 * * * *'),
        'without_overlapping' => true,
    ],
];
