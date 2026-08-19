<?php

namespace DependencySync\Services;

use Illuminate\Http\Client\Factory;
use RuntimeException;

class DependencyReporter
{
    public function __construct(
        private readonly DependencyCollector $collector,
        private readonly Factory $http,
    ) {
    }

    public function report(): array
    {
        $token = config('dependency-sync.token');
        $endpoint = config('dependency-sync.endpoint');

        if (! is_string($token) || $token === '') {
            throw new RuntimeException('DEPENDENCY_SYNC_TOKEN is not configured.');
        }

        if (! is_string($endpoint) || ! $this->isValidHttpEndpoint($endpoint)) {
            throw new RuntimeException('DEPENDENCY_SYNC_ENDPOINT must be a valid HTTP or HTTPS URL.');
        }

        $response = $this->http
            ->asJson()
            ->acceptJson()
            ->withToken($token)
            ->timeout((int) config('dependency-sync.timeout', 30))
            ->post($endpoint, $this->collector->collect());

        $response->throw();

        return $response->json();
    }

    private function isValidHttpEndpoint(string $endpoint): bool
    {
        $parts = parse_url($endpoint);

        return is_array($parts)
            && in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
            && ! empty($parts['host']);
    }
}
