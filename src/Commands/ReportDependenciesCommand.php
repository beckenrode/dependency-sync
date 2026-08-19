<?php

namespace DependencySync\Commands;

use DependencySync\Services\DependencyReporter;
use Illuminate\Console\Command;
use Throwable;

class ReportDependenciesCommand extends Command
{
    protected $signature = 'dependency-sync:report';

    protected $description = 'Report installed Composer and npm package versions to the configured endpoint';

    public function handle(DependencyReporter $reporter): int
    {
        try {
            $result = $reporter->report();
        } catch (Throwable $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->components->info(sprintf(
            'Dependency sync completed: %d Composer packages and %d npm packages.',
            $result['counts']['composer'] ?? 0,
            $result['counts']['npm'] ?? 0,
        ));

        return self::SUCCESS;
    }
}
