<?php

namespace App\Actions;

use App\DataModels\OrderJson;
use App\Documents\Order;
use Doctrine\ODM\MongoDB\DocumentManager;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Factory\ResponseFactory;
use Throwable;

class ReadUser
{
    /** Document manager used for persisting Document */
    private $documentManager;
    /** Factory for HTTP response */
    private $responseFactory;

    public function __construct(
        DocumentManager $documentManager,
        ResponseFactory $responseFactory
    ) {
        $this->documentManager = $documentManager;
        $this->responseFactory = $responseFactory;
    }

    public function __invoke(Response $response, $userId)
    {
        try {
            $orders = $this->documentManager->getRepository(Order::class)->findBy(['customer' => $userId]);
            $orders = $this->ordersArray($orders);
            $response->getBody()->write(json_encode($orders, JSON_UNESCAPED_UNICODE));
            return $response;
        } catch (Throwable $e) {
            $response = $this->responseFactory->createResponse(400);
            $response->getBody()->write($e->getMessage());
            return $response;
        }
    }

    function ordersArray($orders)
    {
        $ordersJson = new class($order)
        {
            public $orders = [];

            public function addOrder($order): void
            {
                $this->orders[] = new OrderJson($order);
            }
        };

        foreach ($orders as $order) {
            $ordersJson->addOrder($order);
        }
        return $ordersJson;
    }
}
