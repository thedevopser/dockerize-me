<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->defaults()
            ->autowire()
            ->autoconfigure()
            ->private()
    ;

    $services->load('Thedevopser\\DockerizeMe\\Command\\', dirname(__DIR__, 2) . '/Command/*');
};
