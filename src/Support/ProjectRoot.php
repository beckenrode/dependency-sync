<?php

namespace DependencySync\Support;

final class ProjectRoot
{
    public static function find(string $start): string
    {
        $directory = rtrim($start, DIRECTORY_SEPARATOR);

        while ($directory !== '' && dirname($directory) !== $directory) {
            if (is_file($directory.'/composer.json')) {
                return $directory;
            }
            $directory = dirname($directory);
        }

        return rtrim($start, DIRECTORY_SEPARATOR);
    }
}
