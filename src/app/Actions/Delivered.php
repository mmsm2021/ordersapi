<?php

namespace App\Actions;

use App\Documents\Order;
use App\Documents\OrderItem;
use App\DTO\Validators\PatchValidator;
use Doctrine\ODM\MongoDB\DocumentManager;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Exceptions\ValidationException;
use Slim\Psr7\Factory\ResponseFactory;
use DateTime;
use Throwable;

class Delivered
{
    /** Document manager used for persisting Document */
    private $documentManager;
    /** Validator for validation of PATCH Document */
    private $patchValidator;
    /** Factory for HTTP response */
    private $responseFactory;

    public function __construct(
        DocumentManager $documentManager,
        PatchValidator $patchValidator,
        ResponseFactory $responseFactory
    ) {
        $this->documentManager = $documentManager;
        $this->responseFactory = $responseFactory;
        $this->patchValidator = $patchValidator;
    }

    public function __invoke(Request $request, Response $response, $orderId)
    {
        try {
            #$this->deliveryValidator->validate($request->getParsedBody());
            $order = $this->documentManager->find(Order::class, $orderId);
            $order = $this->updater($request->getParsedBody()['items'], $order);
            $this->documentManager->persist($order);
            $this->documentManager->flush();
            return $response;
        } catch (ValidationException $e) {
            $response = $this->responseFactory->createResponse(400);
            $response->getBody()->write($e->getMessage());
            return $response;
        } catch (Throwable $e) {
            $response = $this->responseFactory->createResponse(400);
            $response->getBody()->write($e->getMessage());
            return $response;
        }
    }

    function updater($data, Order $order)
    {
        $deliveredItems = [];
        foreach ($data as $item) {
            foreach ($order->getItems() as $orderItem) {
                if ($orderItem->getUUID() == $item['itemUUID']) {
                    $orderItem->setDeliveredTrue(new DateTime());
                }
            }
            $deliveredItems[] = $order->getItems();
        }
        return $order;
    }
}
