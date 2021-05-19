<?php

#phpinfo();

require_once __DIR__ . '/vendor/autoload.php';

$containerBuilder = new \DI\ContainerBuilder();

$containerBuilder->addDefinitions(__DIR__ . '/definitions.php');

$container = $containerBuilder->build();
$app = \DI\Bridge\Slim\Bridge::create($container);

require_once __DIR__ . '/routes.php';

$app->run();
