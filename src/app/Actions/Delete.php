<?php

namespace App\Actions;

use App\Documents\Order;
use Doctrine\ODM\MongoDB\DocumentManager;
use MMSM\Lib\Factories\JsonResponseFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Factory\ResponseFactory;
use Throwable;

class Delete
{
    /**
     * Document manager used for persisting and reading Documents
     * @var DocumentManager
     */
    private $documentManager;

    /**
     * Factory for JSON HTTP response
     * @var JsonResponseFactory
     */
    private JsonResponseFactory $responseFactory;

    public function __construct(
        DocumentManager $documentManager,
        JsonResponseFactory $responseFactory
    ) {
        $this->documentManager = $documentManager;
        $this->responseFactory = $responseFactory;
    }

    public function __invoke(Response $response, $orderId)
    {
        try {
            $this->documentManager->createQueryBuilder(Order::class)->findAndRemove()->field('orderId')->equals($orderId)->getQuery()->execute();
            return $this->responseFactory->create(200, [
                'Delete' => 'success',
            ]);
        } catch (Throwable $e) {
            return $this->responseFactory->create(400, [
                'error' => true,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
