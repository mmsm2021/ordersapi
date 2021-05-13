<?php

use Slim\Routing\RouteCollectorProxy;
use App\Actions\Create;
use App\Actions\Read;
use App\Actions\ReadLast;
use App\Actions\Update;
use App\Actions\Delete;
use App\Middlewares\JsonBodyParserMiddleware;

/** @var \Slim\App $app */

$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

$app->group('/api', function (RouteCollectorProxy $group) {
    $group->post('/orders', Create::class)->add(JsonBodyParserMiddleware::class);
    $group->get('/orders/{orderId}', Read::class);
    $group->get('/orders/{locationId}/last/{n}', ReadLast::class);
    $group->patch('/orders/{orderId}', Update::class)->add(JsonBodyParserMiddleware::class);
    $group->delete('/orders/{orderId}', Delete::class);
});
