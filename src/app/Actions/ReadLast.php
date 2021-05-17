<?php

namespace App\Actions;

use App\Documents\Order;
use App\DTO\ArrayBuilder;
use Doctrine\ODM\MongoDB\DocumentManager;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Factory\ResponseFactory;
use Throwable;

class ReadLast
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

    public function __invoke(Response $response, $locationId, int $n)
    {
        try {
            $count = $this->documentManager->createQueryBuilder(Order::class)->field('locationId')->equals($locationId)->count()->getQuery()->execute();
            $orders = $this->documentManager->createQueryBuilder(Order::class)->field('locationId')->equals($locationId)->skip($count - $n)->getQuery()->execute();
            $arrayBuilder = new ArrayBuilder();
            $orders = $arrayBuilder->ordersArray($orders);
            $response->getBody()->write(json_encode(['orders' => $orders], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            return $response;
        } catch (Throwable $e) {
            $response = $this->responseFactory->createResponse(400);
            $response->getBody()->write($e->getMessage());
            return $response;
        }
    }
}
