<?php

/**
 * @OA\Info(title="OrdersAPI", version="1.0.0")
 */

use MMSM\Lib\AuthorizationMiddleware;
use Slim\Middleware\BodyParsingMiddleware;
use Slim\Middleware\ErrorMiddleware;
use Slim\Psr7\Factory\ResponseFactory;
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
$app->add($container->get(BodyParsingMiddleware::class));
$app->add($container->get(AuthorizationMiddleware::class));
$app->add($container->get(ErrorMiddleware::class));

$app->options('{routes:.+}', function (ResponseFactory $responseFactory) {
    return $responseFactory->createResponse(204);
});

$app->group('/api/v1', function (RouteCollectorProxy $group) {
    $group->post('/orders', Create::class);
    $group->get('/orders/{orderId}', Read::class);
    $group->get('/orders/{locationId}/last/{n}', ReadLast::class);
    $group->get('/orders/location/{locationId}', ReadLocation::class);
    $group->get('/orders/user/{userId}', ReadUser::class);
    $group->patch('/orders/{orderId}', Update::class);
    $group->patch('/orders/delivered/{orderId}', Delivered::class);
    $group->delete('/orders/{orderId}', Delete::class);
});
