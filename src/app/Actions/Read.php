<?php

namespace App\Actions;

use App\Documents\Order;
use Doctrine\ODM\MongoDB\DocumentManager;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Factory\ResponseFactory;
use Throwable;

class Read
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

    /**
     * @OA\Get(
     *     path="/api/orders",
     *     summary="Reads requested order from database",
     *     description="Returns a JSON representation of the requested order",
     *     @OA\Response(
     *           response=200,
     *           description="successful operation",
     *           @OA\JsonContent(ref="#/components/schemas/Order"),
     *          )
     *     ), 
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     )   
     * )
     */
    public function __invoke(Response $response, $orderId)
    {
        try {
            $order = $this->documentManager->find(Order::class, $orderId);
            $order = $order->toArray();
            $response->getBody()->write(json_encode($order, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            return $response;
        } catch (Throwable $e) {
            $response = $this->responseFactory->createResponse(400);
            $response->getBody()->write($e->getMessage());
            return $response;
        }
    }
}
