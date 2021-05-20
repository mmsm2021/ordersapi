<?php

namespace App\Actions;

use App\Documents\Order;
use App\DTO\Validators\PatchValidator;
use Doctrine\ODM\MongoDB\DocumentManager;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Exceptions\ValidationException;
use Slim\Psr7\Factory\ResponseFactory;
use DateTime;
use MMSM\Lib\Factories\JsonResponseFactory;
use Throwable;

class Delivered
{
    /**
     * Document manager used for persisting and reading Documents
     * @var DocumentManager
     */
    private $documentManager;

    /** 
     * Validator for validation of PATCH Document
     */
    private $patchValidator;

    /**
     * Factory for JSON HTTP response
     * @var JsonResponseFactory
     */
    private JsonResponseFactory $responseFactory;

    public function __construct(
        DocumentManager $documentManager,
        PatchValidator $patchValidator,
        JsonResponseFactory $responseFactory
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
            return $this->responseFactory->create(200, [
                'items' => $order->getItemsArray()
            ]);
        } catch (ValidationException $e) {
            return $this->responseFactory->create(400, [
                'error' => true,
                'message' => $e->getMessage(),
            ]);
        } catch (Throwable $e) {
            return $this->responseFactory->create(400, [
                'error' => true,
                'message' => $e->getMessage(),
            ]);
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
