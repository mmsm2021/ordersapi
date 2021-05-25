<?php

namespace App\Actions;

use App\Documents\Order;
use App\DTO\Validators\PatchValidator;
use Doctrine\ODM\MongoDB\DocumentManager;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Exceptions\ValidationException;
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

    /**
     * Delivered constructor.
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
     * @param $orderId
     * @param Request $request
     * @return ResponseInterface
     */
    public function __invoke(Request $request, $orderId)
    {
        try {
            #$this->deliveryValidator->validate($request->getParsedBody());
            $order = $this->documentManager->find(Order::class, $orderId);
            $order = $this->updater($request, $order);
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

    /** 
     * Updates delivery status for the items specified in patch JSON
     * @param array $data
     * @param Order $order
     * @return Order $order
     */
    function updater(Request $request, Order $order)
    {
        $data = $request->getParsedBody()['items'];
        $deliveredItems = [];
        foreach ($data as $item) {
            foreach ($order->getItems() as $orderItem) {
                if ($orderItem->getUUID() == $item['itemUUID']) {
                    $orderItem->setDeliveredTrue(new DateTime());
                }
            }
            $deliveredItems[] = $order->getItems();
        }
        $order->setServer($request->getAttribute('token')->getClaims()['sub']);
        return $order;
    }
}
