<?php

/**
 * @OA\Info(title="OrdersAPI", version="1.0.0")
 */

use MMSM\Lib\AuthorizationMiddleware;
use Slim\Middleware\BodyParsingMiddleware;
use Slim\Routing\RouteCollectorProxy;
use App\Actions\Create;
use App\Actions\Read;
use App\Actions\ReadLast;
use App\Actions\ReadLocation;
use App\Actions\ReadUser;
use App\Actions\Update;
use App\Actions\Delivered;
use App\Actions\Delete;

/** @var \Slim\App $app */
/** @var \Psr\Container\ContainerInterface $container */

$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

$authMiddleware = $container->get(AuthorizationMiddleware::class);
$bodyMiddleware = $container->get(BodyParsingMiddleware::class);

$app->group('/api/v1', function (RouteCollectorProxy $group) use ($bodyMiddleware) {
    $group->post('/orders', Create::class)->add($bodyMiddleware);
    $group->get('/orders/{orderId}', Read::class);
    $group->get('/orders/{locationId}/last/{n}', ReadLast::class);
    $group->get('/orders/location/{locationId}/', ReadLocation::class);
    $group->get('/orders/user/{userId}/all', ReadUser::class);
    $group->patch('/orders/{orderId}', Update::class)->add($bodyMiddleware);
    $group->patch('/orders/delivered/{orderId}', Delivered::class)->add($bodyMiddleware);
    $group->delete('/orders/{orderId}', Delete::class);
})->add($authMiddleware);
