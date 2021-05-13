<?php

use Slim\Routing\RouteCollectorProxy;
use App\Actions\Create;
use App\Actions\Read;
use App\Middlewares\JsonBodyParserMiddleware;
use MongoDB\Operation\Delete;
use MongoDB\Operation\Update;

/** @var \Slim\App $app */

$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

$app->group('/api', function (RouteCollectorProxy $group) {
    $group->post('/orders', Create::class)->add(JsonBodyParserMiddleware::class);
    $group->get('/orders/{orderId}', Read::class)->add(JsonBodyParserMiddleware::class);
    #$group->get('/orders/{locationId}/{orderId}', Create::class);
    #$group->patch('/orders', Update::class);
    #$group->delete('/orders', Delete::class);
});
