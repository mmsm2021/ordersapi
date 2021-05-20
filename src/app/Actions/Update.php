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
use Throwable;

class Update
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
     * Factory for HTTP response
     */
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
            #$this->patchValidator->validate($request->getParsedBody());
            $order = $this->documentManager->find(Order::class, $orderId);
            $order = $this->updater($request->getParsedBody(), $order);
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
        foreach ($data as $item) { //nyt item
            $updating = new OrderItem();
            $updating->setUUID($item[0]['itemUUID']);
            $updating->setNr($item[0]['nr']);
            $updating->setName($item[0]['name']);
            $updating->setCost($item[0]['cost']);
            $order->setItem($updating); //setItem
        }
        $prize = 0;
        foreach ($order->getItems() as $item) {
            $prize += $item->getCost();
        }
        if (null != $order->getDiscount()) {
            $discount = ($prize / 100) * $order->getDiscount();
            $prize -= $discount;
        }
        $order->setTotal($prize);
        return $order;
    }
}
