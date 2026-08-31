<?php

namespace DependencySync\Contracts;

interface HttpClient
{
    public function postJson(string $url, array $payload, string $token, int $timeout): array;
}
