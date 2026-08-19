<?php

namespace DependencySync;

use DependencySync\Commands\ReportDependenciesCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class DependencySyncServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/dependency-sync.php', 'dependency-sync');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/dependency-sync.php' => config_path('dependency-sync.php'),
        ], 'dependency-sync-config');

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([ReportDependenciesCommand::class]);

        if (config('dependency-sync.schedule.enabled')) {
            $this->callAfterResolving(Schedule::class, function (Schedule $schedule): void {
                $event = $schedule
                    ->command('dependency-sync:report')
                    ->cron((string) config('dependency-sync.schedule.cron', '0 * * * *'));

                if (config('dependency-sync.schedule.without_overlapping', true)) {
                    $event->withoutOverlapping();
                }
            });
        }
    }
}
