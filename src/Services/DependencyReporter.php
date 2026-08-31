<?php

namespace DependencySync\Services;

use DependencySync\Contracts\HttpClient;
use RuntimeException;

class DependencyReporter
{
    public function __construct(
        private readonly DependencyCollector $collector,
        private readonly HttpClient $http,
        private readonly string $token,
        private readonly string $endpoint,
        private readonly int $timeout = 30,
    ) {
    }

    public function report(): array
    {
        if ($this->token === '') {
            throw new RuntimeException('DEPENDENCY_SYNC_TOKEN is not configured.');
        }

        if (! $this->isValidHttpEndpoint($this->endpoint)) {
            throw new RuntimeException('DEPENDENCY_SYNC_ENDPOINT must be a valid HTTP or HTTPS URL.');
        }

        return $this->http->postJson(
            $this->endpoint,
            $this->collector->collect(),
            $this->token,
            $this->timeout,
        );
    }

    private function isValidHttpEndpoint(string $endpoint): bool
    {
        $parts = parse_url($endpoint);

        return is_array($parts)
            && in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
            && ! empty($parts['host']);
    }
}
