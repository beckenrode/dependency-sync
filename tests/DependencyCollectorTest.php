<?php

namespace DependencySync\Tests;

use DependencySync\Services\DependencyCollector;
use PHPUnit\Framework\TestCase;

class DependencyCollectorTest extends TestCase
{
    public function test_it_uses_an_explicit_project_root_without_laravel_helpers(): void
    {
        $root = sys_get_temp_dir().'/dependency-sync-'.bin2hex(random_bytes(6));
        mkdir($root);
        file_put_contents($root.'/package-lock.json', json_encode([
            'packages' => [
                '' => ['name' => 'example'],
                'node_modules/vite' => ['name' => 'vite', 'version' => '7.0.0'],
            ],
        ], JSON_THROW_ON_ERROR));

        try {
            $payload = (new DependencyCollector($root))->collect();
            self::assertSame([['name' => 'vite', 'version' => '7.0.0']], $payload['npm']);
            self::assertSame(PHP_VERSION, $payload['php_version']);
            self::assertNotEmpty($payload['composer']);
        } finally {
            unlink($root.'/package-lock.json');
            rmdir($root);
        }
    }
}
