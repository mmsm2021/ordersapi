<?php

namespace App\Actions;

use App\Documents\Order;
use Doctrine\ODM\MongoDB\DocumentManager;
use MMSM\Lib\Factories\JsonResponseFactory;
use Psr\Http\Message\ResponseInterface as Response;
use SimpleJWT\JWT;
use Slim\Exception\HttpException;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Request;
use Throwable;

class Read
{
    /**
     * Document manager used for persisting Document
     * @var DocumentManager
     */
    private $documentManager;

    /**
     * Factory for JSON HTTP response
     * @var JsonResponseFactory
     */
    private JsonResponseFactory $responseFactory;

    /**
     * Read constructor.
     * @param DocumentManager $documentManager
     * @param JsonResponseFactory $responseFactory
     */
    public function __construct(
        DocumentManager $documentManager,
        JsonResponseFactory $responseFactory
    ) {
        $this->documentManager = $documentManager;
        $this->responseFactory = $responseFactory;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/orders",
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
    public function __invoke(Request $request, Response $response, $orderId)
    {
        try {
            $token = $request->getAttribute('token');
            if (!($token instanceof JWT)) {
                throw new HttpUnauthorizedException($request, 'Unauthorized');
            }
            $order = $this->documentManager->find(Order::class, $orderId);
            if ($order instanceof Order) {
                return $this->responseFactory->create(200, ['orders' => $order->toArray()]);
            }
            return $this->responseFactory->create(404, [
                'error' => true,
                'message' => 'Order not found / Does not exist',
            ]);
        } catch (Throwable $e) {
            if ($e instanceof HttpException) {
                throw $e;
            }
            return $this->responseFactory->create(400, [
                'error' => true,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
