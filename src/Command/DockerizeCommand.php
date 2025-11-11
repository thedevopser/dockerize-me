<?php

namespace Thedevopser\DockerizeMe\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Thedevopser\DockerizeMe\Generator\DockerAssetsGenerator;

/**
 * @psalm-type GenerateOptions = array{
 *   db: string,
 *   extensions: list<string>,
 *   php_version: string,
 *   http_port: string,
 *   add_db_service: bool
 * }
 */
#[AsCommand(name: 'dockerize:me', description: 'Generate a FrankenPHP Docker setup for your Symfony app')]
class DockerizeCommand extends Command
{
    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Dockerize your Symfony app');
        $io->writeln('Select database connector');
        $db = $io->choice('Database', ['mysql', 'mariadb', 'postgres', 'sqlite', 'none'], 'none');
        $ext = $io->ask('Additional PHP extensions (space separated)', '');
        $php = $io->ask('PHP version', '8.4');
        $port = $io->ask('HTTP port', '8080');
        $addDb = $io->confirm('Add database service to docker compose for dev?', true);
        $generator = new DockerAssetsGenerator();
        /** @var list<string> $extList */
        $extList = [];
        $trim = is_string($ext) ? trim($ext) : '';
        if ($trim !== '') {
            $split = preg_split('/\s+/', $trim);
            if (is_array($split)) {
                $extList = $split;
            }
        }
        /** @var string $phpVersion */
        $phpVersion = is_string($php) ? $php : '8.4';
        /** @var string $httpPort */
        $httpPort = is_string($port) ? $port : '8080';
        /** @var GenerateOptions $opts */
        $opts = [
            'db' => $db,
            'extensions' => $extList,
            'php_version' => $phpVersion,
            'http_port' => $httpPort,
            'add_db_service' => $addDb,
        ];
        $files = $generator->generate($opts);
        $cwd = getcwd();
        if (!is_string($cwd)) {
            throw new \RuntimeException('Current working directory is not available');
        }
        $generator->write($cwd, $files);
        $io->success('Docker files generated in docker/');
        $io->writeln('Run: docker compose -f docker/compose.yml up --build');
        return Command::SUCCESS;
    }
}
