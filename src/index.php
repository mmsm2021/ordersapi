<?php

#phpinfo();

require_once __DIR__ . '/vendor/autoload.php';

$containerBuilder = new \DI\ContainerBuilder(
    \MMSM\Lib\Container::class
);

$containerBuilder->addDefinitions(__DIR__ . '/definitions.php');
$containerBuilder->addDefinitions(__DIR__ . '/vendor/mmsm/service-lib/definitions.php');

$container = $containerBuilder->build();
$app = \DI\Bridge\Slim\Bridge::create($container);

require_once __DIR__ . '/routes.php';

$app->run();
