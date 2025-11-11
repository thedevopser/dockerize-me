<?php

namespace Thedevopser\DockerizeMe\Tests\Command;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Thedevopser\DockerizeMe\Command\DockerizeCommand;

class DockerizeCommandTest extends TestCase
{
    public function testCommandGeneratesFiles(): void
    {
        $app = new Application();
        $app->add(new DockerizeCommand());
        $command = $app->find('dockerize:me');
        $tester = new CommandTester($command);
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'dockcmd_' . bin2hex(random_bytes(4));
        mkdir($dir);
        $cwd = getcwd();
        chdir($dir);
        try {
            $tester->setInputs(['none', '', '8.3', '8080', 'y']);
            $tester->execute([]);
            $this->assertFileExists($dir . DIRECTORY_SEPARATOR . 'docker' . DIRECTORY_SEPARATOR . 'Dockerfile');
            $this->assertFileExists($dir . DIRECTORY_SEPARATOR . 'docker' . DIRECTORY_SEPARATOR . 'compose.yml');
        } finally {
            if (is_string($cwd)) {
                chdir($cwd);
            }
        }
    }
}
