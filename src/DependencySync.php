<?php

namespace DependencySync;

use DependencySync\Services\DependencyCollector;
use DependencySync\Services\DependencyReporter;
use DependencySync\Support\NativeHttpClient;

final class DependencySync
{
    public static function reporter(?string $projectRoot = null, ?array $options = null): DependencyReporter
    {
        $options ??= [];
        $environmentTimeout = getenv('DEPENDENCY_SYNC_TIMEOUT');

        return new DependencyReporter(
            new DependencyCollector($projectRoot),
            new NativeHttpClient(),
            (string) self::value($options, 'token', getenv('DEPENDENCY_SYNC_TOKEN'), ''),
            (string) self::value($options, 'endpoint', getenv('DEPENDENCY_SYNC_ENDPOINT'), ''),
            (int) self::value($options, 'timeout', $environmentTimeout, 30),
        );
    }

    private static function value(array $options, string $key, mixed $environment, mixed $default): mixed
    {
        $value = $options[$key] ?? $environment;

        return $value === false || $value === '' || $value === null ? $default : $value;
    }
}
