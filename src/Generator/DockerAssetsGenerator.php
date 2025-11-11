<?php

declare(strict_types=1);

namespace Thedevopser\DockerizeMe\Generator;

/**
 * @phpstan-type Options array{
 *   db?: string,
 *   extensions?: array<array-key, mixed>,
 *   php_version?: string,
 *   http_port?: string|int,
 *   add_db_service?: bool
 * }
 * @phpstan-type Files array<string, string>
 */
class DockerAssetsGenerator
{
    /**
     * @param Options $options
     * @return Files
     */
    public function generate(array $options): array
    {
        $dbOpt = $options['db'] ?? 'none';
        $db = $this->normalizeDb($dbOpt);
        $extOpt = $options['extensions'] ?? [];
        $extensions = $this->normalizeList($extOpt);
        $phpOpt = $options['php_version'] ?? '8.4';
        $phpVersion = $this->normalizePhpVersion($phpOpt);
        $portOpt = $options['http_port'] ?? '8080';
        $portStr = (string)$portOpt;
        $httpPort = $this->normalizePort($portStr);
        $addDbService = (bool)($options['add_db_service'] ?? true);
        $files = [];
        $files['docker/Dockerfile'] = $this->dockerfile($phpVersion, $extensions, $db);
        $files['docker/compose.yml'] = $this->compose($httpPort, $db, $addDbService);
        $files['docker/php/dev.ini'] = $this->phpIniDev();
        $files['docker/php/prod.ini'] = $this->phpIniProd();
        $files['docker/frankenphp/Caddyfile'] = $this->caddyfile();
        return $files;
    }

    /**
     * @param string $targetDir
     * @param Files $files
     */
    public function write(string $targetDir, array $files): void
    {
        foreach ($files as $relative => $content) {
            $path = rtrim($targetDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relative);
            $dir = dirname($path);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            $bytes = @file_put_contents($path, $content);
            if ($bytes === false) {
                throw new \RuntimeException('Failed to write file: ' . $path);
            }
        }
    }

    /**
     * @param string $phpVersion
     * @param list<string> $extensions
     * @param string $db
     * @return string
     */
    private function dockerfile(string $phpVersion, array $extensions, string $db): string
    {
        $base = 'dunglas/frankenphp:1-php' . $phpVersion . '-alpine';
        $builder = [];
        $builder[] = 'FROM ' . $base . ' AS builder';
        $builder[] = 'WORKDIR /app';
        $builder[] = 'COPY . /app';
        $exts = $this->normalizeExtensions($extensions, $db);
        if ($exts) {
            $builder[] = 'RUN install-php-extensions ' . implode(' ', array_unique($exts));
        }
        $builder[] = 'RUN mkdir -p /app/var';
        $dev = [];
        $dev[] = 'FROM ' . $base . ' AS dev';
        $dev[] = 'WORKDIR /app';
        $dev[] = 'COPY --from=builder /app /app';
        $dev[] = 'RUN install-php-extensions xdebug';
        $dev[] = 'ENV XDEBUG_MODE=develop,debug';
        $dev[] = 'COPY docker/php/dev.ini /usr/local/etc/php/conf.d/dev.ini';
        $dev[] = 'COPY docker/frankenphp/Caddyfile /etc/caddy/Caddyfile';
        $stable = [];
        $stable[] = 'FROM ' . $base . ' AS stable';
        $stable[] = 'WORKDIR /app';
        $stable[] = 'COPY --from=builder /app /app';
        if ($exts) {
            $stable[] = 'RUN install-php-extensions ' . implode(' ', array_unique($exts));
        }
        $stable[] = 'COPY docker/php/prod.ini /usr/local/etc/php/conf.d/prod.ini';
        $stable[] = 'COPY docker/frankenphp/Caddyfile /etc/caddy/Caddyfile';
        return implode("\n", array_merge($builder, [''], $dev, [''], $stable)) . "\n";
    }

    /**
     * @param string $httpPort
     * @param string $db
     * @param bool $addDbService
     * @return string
     */
    private function compose(string $httpPort, string $db, bool $addDbService): string
    {
        $services = [];
        $services[] = 'services:';
        $services[] = '  app:';
        $services[] = '    build:';
        $services[] = '      context: ..';
        $services[] = '      dockerfile: docker/Dockerfile';
        $services[] = '      target: dev';
        // Set environment variables required by FrankenPHP/Caddy
        $services[] = '    environment:';
        $services[] = '      SERVER_NAME: :80';
        $services[] = '    volumes:';
        $services[] = '      - ../:/app';
        $services[] = '    ports:';
        $services[] = '      - "' . $httpPort . ':80"';
        $services[] = '    tty: true';
        $dbBlock = $this->dbServiceBlock($db);
        if ($addDbService && $dbBlock) {
            $services[] = '    depends_on:';
            $services[] = '      - db';
            $services[] = '  db:';
            foreach ($dbBlock as $line) {
                $services[] = '    ' . $line;
            }
        }
        return implode("\n", $services) . "\n";
    }

    /**
     * @return string
     */
    private function phpIniDev(): string
    {
        return implode("\n", [
            'memory_limit=512M',
            'display_errors=1',
            'display_startup_errors=1',
            'error_reporting=E_ALL',
            'xdebug.mode=develop,debug',
            'xdebug.start_with_request=yes',
            'xdebug.client_host=host.docker.internal',
            'xdebug.client_port=9003',
            'opcache.enable=0'
        ]) . "\n";
    }

    /**
     * @return string
     */
    private function phpIniProd(): string
    {
        return implode("\n", [
            'memory_limit=256M',
            'display_errors=0',
            'error_reporting=E_ALL & ~E_DEPRECATED & ~E_STRICT',
            'opcache.enable=1',
            'opcache.validate_timestamps=0',
            'opcache.preload=/app/config/preload.php'
        ]) . "\n";
    }

    /**
     * @return string
     */
    private function caddyfile(): string
    {
        return implode("\n", [
            '{',
            '    # Global options',
            '    auto_https off',
            '    admin off',
            '}',
            '',
            ':80 {',
            '    # FrankenPHP configuration',
            '    root /app/public',
            '',
            '    # Enable compression',
            '    encode zstd gzip',
            '',
            '    # Handle PHP files with FrankenPHP',
            '    php_server',
            '',
            '    # Try files directive for Symfony routing',
            '    try_files {path} {path}/index.php /index.php',
            '',
            '    # Serve static files directly',
            '    file_server',
            '',
            '    # Log in JSON format',
            '    log {',
            '            format json',
            '    }',
            '}'
        ]) . "\n";
    }

    /**
     * @param list<string> $extensions
     * @return list<string>
     */
    private function normalizeExtensions(array $extensions, string $db): array
    {
        $exts = $this->normalizeList($extensions);
        $map = [
            'mysql' => 'pdo_mysql',
            'mariadb' => 'pdo_mysql',
            'postgres' => 'pdo_pgsql',
            'sqlite' => 'pdo_sqlite',
        ];
        $pdo = $map[$db] ?? null;
        if ($pdo) {
            $exts[] = $pdo;
        }
        return array_values(array_filter(array_unique($exts)));
    }

    /**
     * @return list<string>
     */
    private function dbServiceBlock(string $db): array
    {
        if ($db === 'mysql') {
            return [
                'image: mysql:8',
                'environment:',
                '  - MYSQL_DATABASE=app',
                '  - MYSQL_USER=app',
                '  - MYSQL_PASSWORD=app',
                '  - MYSQL_ROOT_PASSWORD=root',
                'ports:',
                '  - "3306:3306"',
            ];
        }
        if ($db === 'mariadb') {
            return [
                'image: mariadb:11',
                'environment:',
                '  - MYSQL_DATABASE=app',
                '  - MYSQL_USER=app',
                '  - MYSQL_PASSWORD=app',
                '  - MYSQL_ROOT_PASSWORD=root',
                'ports:',
                '  - "3306:3306"',
            ];
        }
        if ($db === 'postgres') {
            return [
                'image: postgres:16',
                'environment:',
                '  - POSTGRES_DB=app',
                '  - POSTGRES_USER=app',
                '  - POSTGRES_PASSWORD=app',
                'ports:',
                '  - "5432:5432"',
            ];
        }
        if ($db === 'sqlite') {
            return [
                'image: alpine:3',
                'command: ["sh", "-c", "sleep infinity"]',
            ];
        }
        return [];
    }

    /**
     * @return string
     */
    private function normalizeDb(string $db): string
    {
        $allowed = ['mysql', 'mariadb', 'postgres', 'sqlite', 'none'];
        if (!in_array($db, $allowed, true)) {
            return 'none';
        }
        return $db;
    }

    /**
     * @return string
     */
    private function normalizePhpVersion(string $version): string
    {
        if ($version === '') {
            return '8.4';
        }
        return $version;
    }

    /**
     * @return string
     */
    private function normalizePort(string $port): string
    {
        if (!preg_match('/^\\d{2,5}$/', $port)) {
            return '8080';
        }
        return $port;
    }

    /**
     * @param array<array-key, mixed> $list
     * @return list<string>
     */
    private function normalizeList(array $list): array
    {
        $out = [];
        foreach ($list as $item) {
            if (is_int($item) || is_float($item) || is_string($item)) {
                $trim = trim((string)$item);
                if ($trim !== '') {
                    $out[] = $trim;
                }
                continue;
            }
        }
        return $out;
    }
}
