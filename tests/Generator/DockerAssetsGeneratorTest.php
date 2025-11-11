<?php

namespace Thedevopser\DockerizeMe\Tests\Generator;

use PHPUnit\Framework\TestCase;
use Thedevopser\DockerizeMe\Generator\DockerAssetsGenerator;

class DockerAssetsGeneratorTest extends TestCase
{
    public function testGenerateWithPostgresAndExtensions(): void
    {
        $gen = new DockerAssetsGenerator();
        $files = $gen->generate([
            'db' => 'postgres',
            'extensions' => ['intl'],
            'php_version' => '8.3',
            'http_port' => 8080,
            'add_db_service' => true,
        ]);
        $this->assertArrayHasKey('docker/Dockerfile', $files);
        $this->assertArrayHasKey('docker/compose.yml', $files);
        $this->assertArrayHasKey('docker/php/dev.ini', $files);
        $this->assertArrayHasKey('docker/php/prod.ini', $files);
        $this->assertArrayHasKey('docker/Caddyfile', $files);
        $dockerfile = $files['docker/Dockerfile'] ?? null;
        $compose = $files['docker/compose.yml'] ?? null;
        $this->assertIsString($dockerfile);
        $this->assertIsString($compose);
        $this->assertStringContainsString('install-php-extensions', $dockerfile);
        $this->assertStringContainsString('pdo_pgsql', $dockerfile);
        $this->assertStringContainsString('intl', $dockerfile);
        $this->assertStringContainsString('postgres:16', $compose);
        $this->assertStringContainsString('dunglas/frankenphp', $dockerfile);
        $this->assertStringNotContainsString('APP_ENV', $dockerfile);
        $this->assertStringNotContainsString('APP_ENV', $compose);
    }

    public function testNoDbServiceWhenOptionDisabled(): void
    {
        $gen = new DockerAssetsGenerator();
        $files = $gen->generate([
            'db' => 'mysql',
            'extensions' => [],
            'http_port' => 8080,
            'add_db_service' => false,
        ]);
        $dockerfile = $files['docker/Dockerfile'] ?? null;
        $compose = $files['docker/compose.yml'] ?? null;
        $this->assertIsString($dockerfile);
        $this->assertIsString($compose);
        $this->assertStringNotContainsString("\n  db:\n", $compose);
        $this->assertStringNotContainsString('depends_on:', $compose);
        $this->assertStringContainsString('pdo_mysql', $dockerfile);
    }

    public function testDefaultPhpVersionIs84(): void
    {
        $gen = new DockerAssetsGenerator();
        $files = $gen->generate([
            'db' => 'none',
            'extensions' => [],
            'http_port' => 8080,
        ]);
        $dockerfile = $files['docker/Dockerfile'] ?? null;
        $this->assertIsString($dockerfile);
        $this->assertStringContainsString('dunglas/frankenphp:1-php8.4', $dockerfile);
    }

    public function testWriteCreatesDirectories(): void
    {
        $gen = new DockerAssetsGenerator();
        $files = $gen->generate(['db' => 'none']);
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'dockgen_' . bin2hex(random_bytes(4));
        $gen->write($dir, $files);
        $this->assertFileExists($dir . DIRECTORY_SEPARATOR . 'docker' . DIRECTORY_SEPARATOR . 'Dockerfile');
        $this->assertFileExists($dir . DIRECTORY_SEPARATOR . 'docker' . DIRECTORY_SEPARATOR . 'compose.yml');
    }
}
