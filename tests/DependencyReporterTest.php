<?php

namespace DependencySync\Tests;

use DependencySync\Services\DependencyReporter;
use Illuminate\Support\Facades\Http;

class DependencyReporterTest extends TestCase
{
    public function test_it_reports_the_expected_payload_with_a_bearer_token(): void
    {
        config([
            'dependency-sync.token' => 'test-secret-token',
            'dependency-sync.endpoint' => 'https://dependencies.example/api/sync',
        ]);

        Http::fake([
            '*' => Http::response([
                'status' => true,
                'counts' => ['composer' => 1, 'npm' => 1],
            ]),
        ]);

        app(DependencyReporter::class)->report();

        Http::assertSent(fn ($request): bool =>
            $request->url() === config('dependency-sync.endpoint')
            && $request->hasHeader('Authorization', 'Bearer '.config('dependency-sync.token'))
            && isset($request['php_version'], $request['composer'], $request['npm'])
        );
    }
}
