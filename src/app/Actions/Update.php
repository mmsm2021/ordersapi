<?php

namespace App\Actions;

use App\Documents\Order;
use App\Documents\OrderItem;
use App\DTO\Validators\PatchValidator;
use Doctrine\ODM\MongoDB\DocumentManager;
use MMSM\Lib\Factories\JsonResponseFactory;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Exceptions\ValidationException;
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

    /**
     * Update constructor.
     * @param DocumentManager $documentManager
     * @param PatchValidator $patchValidator
     * @param JsonResponseFactory $responseFactory
     */
    public function __construct(
        DocumentManager $documentManager,
        PatchValidator $patchValidator,
        JsonResponseFactory $responseFactory
    ) {
        $this->documentManager = $documentManager;
        $this->responseFactory = $responseFactory;
        $this->patchValidator = $patchValidator;
    }

    /**
     * @param Request $request
     * @param $orderId
     * @return ResponseInterface
     */
    public function __invoke(Request $request, $orderId)
    {
        try {
            #$this->patchValidator->validate($request->getParsedBody());
            $order = $this->documentManager->find(Order::class, $orderId);
            $order = $this->updater($request->getParsedBody(), $order);
            $this->documentManager->persist($order);
            $this->documentManager->flush();
            return $this->responseFactory->create(200, ['orders' => $order->toArray()]);
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

    /** 
     * Updates the order items based on patch JSON,
     * also corrects the prize and discount
     * @param $data
     * @param Order $order
     * @return Order $order
     */
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
