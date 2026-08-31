<?php

namespace DependencySync\Services;

use Composer\InstalledVersions;
use JsonException;
use RuntimeException;

class DependencyCollector
{
    public function __construct(private readonly ?string $projectRoot = null)
    {
    }

    public function collect(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'composer' => $this->composerPackages(),
            'npm' => $this->npmPackages($this->root().'/package-lock.json'),
        ];
    }

    private function root(): string
    {
        return rtrim($this->projectRoot ?? getcwd() ?: '.', DIRECTORY_SEPARATOR);
    }

    public function composerPackages(): array
    {
        $packages = [];

        foreach (InstalledVersions::getInstalledPackages() as $name) {
            $packages[] = [
                'name' => $name,
                'version' => InstalledVersions::getPrettyVersion($name)
                    ?? InstalledVersions::getVersion($name),
            ];
        }

        usort($packages, fn (array $a, array $b): int => strcmp($a['name'], $b['name']));

        return $packages;
    }

    public function npmPackages(string $lockFile): array
    {
        if (! is_file($lockFile)) {
            return [];
        }

        try {
            $lock = json_decode((string) file_get_contents($lockFile), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('Unable to parse package-lock.json: '.$exception->getMessage(), 0, $exception);
        }

        $versions = [];

        if (isset($lock['packages']) && is_array($lock['packages'])) {
            foreach ($lock['packages'] as $path => $package) {
                if ($path === '' || ! is_array($package) || empty($package['version'])) {
                    continue;
                }

                $name = $package['name'] ?? $this->nameFromPackagePath((string) $path);
                if ($name !== '') {
                    $versions[$name][(string) $package['version']] = true;
                }
            }
        } elseif (isset($lock['dependencies']) && is_array($lock['dependencies'])) {
            $this->collectLegacyNpmDependencies($lock['dependencies'], $versions);
        }

        ksort($versions);

        return array_map(
            fn (string $name, array $packageVersions): array => [
                'name' => $name,
                'version' => implode(', ', array_keys($packageVersions)),
            ],
            array_keys($versions),
            array_values($versions),
        );
    }

    private function nameFromPackagePath(string $path): string
    {
        $position = strrpos($path, 'node_modules/');

        return $position === false ? '' : substr($path, $position + 13);
    }

    private function collectLegacyNpmDependencies(array $dependencies, array &$versions): void
    {
        foreach ($dependencies as $name => $package) {
            if (! is_array($package)) {
                continue;
            }

            if (! empty($package['version'])) {
                $versions[$name][(string) $package['version']] = true;
            }

            if (isset($package['dependencies']) && is_array($package['dependencies'])) {
                $this->collectLegacyNpmDependencies($package['dependencies'], $versions);
            }
        }
    }
}
