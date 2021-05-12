<?php

use Slim\Routing\RouteCollectorProxy;
use App\Actions\Create;

/** @var \Slim\App $app */

$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

$app->group('/api', function (RouteCollectorProxy $group) {
    $group->post('/orders', Create::class);
});
