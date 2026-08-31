<?php

namespace DependencySync;

use DependencySync\Commands\ReportDependenciesCommand;
use DependencySync\Contracts\HttpClient;
use DependencySync\Services\DependencyCollector;
use DependencySync\Services\DependencyReporter;
use DependencySync\Integrations\LaravelHttpClient;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class DependencySyncServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/dependency-sync.php', 'dependency-sync');
        $this->app->singleton(HttpClient::class, LaravelHttpClient::class);
        $this->app->singleton(DependencyCollector::class, fn () => new DependencyCollector(base_path()));
        $this->app->singleton(DependencyReporter::class, fn ($app) => new DependencyReporter(
            $app->make(DependencyCollector::class),
            $app->make(HttpClient::class),
            (string) config('dependency-sync.token', ''),
            (string) config('dependency-sync.endpoint', ''),
            (int) config('dependency-sync.timeout', 30),
        ));
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
