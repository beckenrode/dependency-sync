<?php

namespace DependencySync\Integrations;

use DependencySync\Contracts\HttpClient;
use Illuminate\Http\Client\Factory;

final class LaravelHttpClient implements HttpClient
{
    public function __construct(private readonly Factory $http)
    {
    }

    public function postJson(string $url, array $payload, string $token, int $timeout): array
    {
        return $this->http->asJson()->acceptJson()->withToken($token)->timeout($timeout)
            ->post($url, $payload)->throw()->json();
    }
}
