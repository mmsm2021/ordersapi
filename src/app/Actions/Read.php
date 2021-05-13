<?php

namespace App\Actions;

use App\DataModels\OrderJson;
use App\Documents\Order;
use Doctrine\ODM\MongoDB\DocumentManager;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Factory\ResponseFactory;
use Throwable;

class Read
{
    /** Document manager used for persisting Document */
    private $documentManager;
    /** Factory for responses */
    private $responseFactory;

    public function __construct(
        DocumentManager $documentManager,
        ResponseFactory $responseFactory
    ) {
        $this->documentManager = $documentManager;
        $this->responseFactory = $responseFactory;
    }

    public function __invoke(Response $response, $orderId)
    {
        try {
            $order = $this->documentManager->find(Order::class, $orderId);
            $order = new OrderJson($order);
            $response->getBody()->write(json_encode($order, JSON_UNESCAPED_UNICODE));
            return $response;
        } catch (Throwable $e) {
            $response = $this->responseFactory->createResponse(400);
            $response->getBody()->write($e->getMessage());
            return $response;
        }
    }
}
