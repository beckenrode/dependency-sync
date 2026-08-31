<?php

namespace DependencySync\Integrations;

use DependencySync\DependencySync;
use DependencySync\Support\ProjectRoot;
use Throwable;

final class WordPress
{
    public static function boot(): void
    {
        add_action('dependency_sync_report', [self::class, 'report']);
        add_action('init', [self::class, 'schedule']);

        if (defined('WP_CLI') && WP_CLI) {
            \WP_CLI::add_command('dependency-sync report', function (): void {
                try {
                    $result = self::report();
                    \WP_CLI::success(self::summary($result));
                } catch (Throwable $exception) {
                    \WP_CLI::error($exception->getMessage());
                }
            });
        }
    }

    public static function report(): array
    {
        return DependencySync::reporter(ProjectRoot::find(ABSPATH), [
            'token' => defined('DEPENDENCY_SYNC_TOKEN') ? DEPENDENCY_SYNC_TOKEN : getenv('DEPENDENCY_SYNC_TOKEN'),
            'endpoint' => defined('DEPENDENCY_SYNC_ENDPOINT') ? DEPENDENCY_SYNC_ENDPOINT : getenv('DEPENDENCY_SYNC_ENDPOINT'),
            'timeout' => defined('DEPENDENCY_SYNC_TIMEOUT') ? DEPENDENCY_SYNC_TIMEOUT : getenv('DEPENDENCY_SYNC_TIMEOUT'),
        ])->report();
    }

    public static function schedule(): void
    {
        if (! wp_next_scheduled('dependency_sync_report')) {
            wp_schedule_event(time(), 'hourly', 'dependency_sync_report');
        }
    }

    private static function summary(array $result): string
    {
        return sprintf('Reported %d Composer packages and %d npm packages.', $result['counts']['composer'] ?? 0, $result['counts']['npm'] ?? 0);
    }
}
