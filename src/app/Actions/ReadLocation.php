<?php

namespace App\Actions;

use App\Documents\Order;
use Doctrine\ODM\MongoDB\DocumentManager;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Factory\ResponseFactory;
use Throwable;

class ReadLocation
{
    /**
     * Document manager used for persisting and reading Documents
     * @var DocumentManager
     */
    private $documentManager;

    /**
     * Factory for HTTP response
     */
    private $responseFactory;

    public function __construct(
        DocumentManager $documentManager,
        ResponseFactory $responseFactory
    ) {
        $this->documentManager = $documentManager;
        $this->responseFactory = $responseFactory;
    }

    public function __invoke(Response $response, $locationId, $sortBy, $page, $size)
    {
        try {
            $page = $page - 1;
            $orders = $this->documentManager->createQueryBuilder(Order::class)->field('locationId')->equals($locationId)->sort($sortBy, 'desc')->limit($size)->skip($page * $size)->getQuery()->execute();
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
}
