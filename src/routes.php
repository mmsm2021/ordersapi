<?php

/**
 * @OA\Info(title="OrdersAPI", version="1.0.0")
 */

use Slim\Routing\RouteCollectorProxy;
use App\Actions\Create;
use App\Actions\Read;
use App\Actions\ReadLast;
use App\Actions\ReadLocation;
use App\Actions\ReadUser;
use App\Actions\Update;
use App\Actions\Delivered;
use App\Actions\Delete;
use App\Actions\SwaggerJson;
use App\Middlewares\JsonBodyParserMiddleware;
use OpenApi\Annotations as OA;

/** @var \Slim\App $app */

$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

$app->group('/api', function (RouteCollectorProxy $group) {
    $group->post('/orders', Create::class)->add(JsonBodyParserMiddleware::class);
    $group->get('/orders/{orderId}', Read::class);
    $group->get('/orders/{locationId}/last/{n}', ReadLast::class);
    $group->get('/orders/location/{locationId}/{sortBy}/{page}/{size}', ReadLocation::class);
    $group->get('/orders/user/{userId}/all', ReadUser::class);
    $group->patch('/orders/{orderId}', Update::class)->add(JsonBodyParserMiddleware::class);
    $group->patch('/orders/delivered/{orderId}', Delivered::class)->add(JsonBodyParserMiddleware::class);
    $group->delete('/orders/{orderId}', Delete::class);
});

$app->get('/swagger.json', SwaggerJson::class);
