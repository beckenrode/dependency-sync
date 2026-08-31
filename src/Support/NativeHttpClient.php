<?php

namespace DependencySync\Support;

use DependencySync\Contracts\HttpClient;
use JsonException;
use RuntimeException;

class NativeHttpClient implements HttpClient
{
    public function postJson(string $url, array $payload, string $token, int $timeout): array
    {
        $body = json_encode($payload, JSON_THROW_ON_ERROR);
        $headers = ['Content-Type: application/json', 'Accept: application/json', 'Authorization: Bearer '.$token];

        if (function_exists('curl_init')) {
            $curl = curl_init($url);
            curl_setopt_array($curl, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $body,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $timeout,
            ]);
            $response = curl_exec($curl);
            $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
            $error = curl_error($curl);
            curl_close($curl);
            if ($response === false) {
                throw new RuntimeException('Dependency sync request failed: '.$error);
            }
        } else {
            $context = stream_context_create(['http' => [
                'method' => 'POST', 'header' => implode("\r\n", $headers), 'content' => $body,
                'timeout' => $timeout, 'ignore_errors' => true,
            ]]);
            $response = @file_get_contents($url, false, $context);
            if ($response === false) {
                throw new RuntimeException('Dependency sync request failed.');
            }
            $statusLine = $http_response_header[0] ?? '';
            preg_match('/\s(\d{3})\s/', $statusLine, $matches);
            $status = (int) ($matches[1] ?? 0);
        }

        if ($status < 200 || $status >= 300) {
            throw new RuntimeException(sprintf('Dependency sync API returned HTTP %d: %s', $status, $response));
        }

        try {
            $decoded = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('Dependency sync API returned invalid JSON.', 0, $exception);
        }

        return is_array($decoded) ? $decoded : [];
    }
}
