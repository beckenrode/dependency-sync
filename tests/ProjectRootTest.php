<?php

namespace DependencySync\Tests;

use DependencySync\Support\ProjectRoot;
use PHPUnit\Framework\TestCase;

class ProjectRootTest extends TestCase
{
    public function test_it_finds_a_composer_project_above_a_nested_wordpress_directory(): void
    {
        $root = sys_get_temp_dir().'/dependency-sync-root-'.bin2hex(random_bytes(6));
        mkdir($root.'/web/wp', 0777, true);
        file_put_contents($root.'/composer.json', '{}');

        try {
            self::assertSame($root, ProjectRoot::find($root.'/web/wp'));
        } finally {
            unlink($root.'/composer.json');
            rmdir($root.'/web/wp');
            rmdir($root.'/web');
            rmdir($root);
        }
    }
}
