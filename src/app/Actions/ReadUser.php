<?php

namespace App\Actions;

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
            $sendBack = [];
            foreach ($orders as $order) {
                $sendBack[] = $order->toArray();
            }
            $response->getBody()->write(json_encode(['orders' => $sendBack], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            return $response;
        } catch (Throwable $e) {
            $response = $this->responseFactory->createResponse(400);
            $response->getBody()->write($e->getMessage());
            return $response;
        }
    }

    function ordersArray($orders)
    {
        $ordersArray = [];

        foreach ($orders as $order) {
            $itemsArray = [];
            $items = $order->getPersistentItems()->getValues();
            foreach ($items as $item) {
                $itemsArray[] = [
                    'id' => $item->getId(),
                    'name' => $item->getName(),
                    'cost' => $item->getCost()
                ];
            }

            $ordersArray[] = [
                'orderId' => $order->getOrderID(),
                'location'  => $order->getLocation(),
                'locationId'  => $order->getLocationId(),
                'server'  => $order->getServer(),
                'customer'  => $order->getCustomer(),
                'items'  => $itemsArray,
                'discount'  => $order->getDiscount(),
                'total'  => $order->getTotal(),
                'orderDate'  => $order->getOrderDate()
            ];
        }
        return $ordersArray;
    }
}
