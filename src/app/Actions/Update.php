<?php

namespace App\Actions;

use App\Documents\Order;
use App\Documents\OrderItem;
use App\DTO\Validators\PatchValidator;
use App\Factories\OrderItemFactory;
use Doctrine\ODM\MongoDB\DocumentManager;
use MMSM\Lib\Authorizer;
use MMSM\Lib\Factories\JsonResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Exceptions\ValidationException;
use Throwable;

class Update
{
    /**
     * Authorizer for verification of user permissions
     * @var Authorizer
     */
    private Authorizer $authorizer;

    /**
     * @var DocumentManager
     */
    private DocumentManager $documentManager;

    /**
     * @var PatchValidator
     */
    private PatchValidator $patchValidator;

    /**
     * @var OrderItemFactory
     */
    private OrderItemFactory $orderItemFactory;

    /**
     * @var JsonResponseFactory
     */
    private JsonResponseFactory $responseFactory;

    /**
     * Update constructor.
     * @param Authorizer $authorizer
     * @param DocumentManager $documentManager
     * @param PatchValidator $patchValidator
     * @param JsonResponseFactory $responseFactory
     * @param OrderItemFactory $orderItemFactory
     */
    public function __construct(
        Authorizer $authorizer,
        DocumentManager $documentManager,
        PatchValidator $patchValidator,
        JsonResponseFactory $responseFactory,
        OrderItemFactory $orderItemFactory
    ) {
        $this->authorizer = $authorizer;
        $this->documentManager = $documentManager;
        $this->patchValidator = $patchValidator;
        $this->responseFactory = $responseFactory;
        $this->orderItemFactory = $orderItemFactory;
    }

    /**
     * @param Request $request
     * @param string $orderId
     * @return ResponseInterface
     * @throws Throwable
     */
    public function __invoke(Request $request, string $orderId): ResponseInterface
    {
        $this->authorizer->authorizeToRoles(
            $request,
            [
                'user.roles.employee',
                'user.roles.admin',
                'user.roles.super',
            ]
        );

        try {
            $body = $request->getParsedBody();
            $this->patchValidator->validate($body);
            /** @var Order $order */
            $order = $this->documentManager->find(Order::class, $orderId);
            $itemsOnOrder = $this->isItemOnOrder($order, $body);
            if($itemsOnOrder) {
                $order = $this->updateOrder($order, $body);
                $this->documentManager->persist($order);
                $this->documentManager->flush();
                return $this->responseFactory->create(200, ['orders' => $order->toArray()]);
            } else {
                return $this->responseFactory->create(400, [
                    'error' => true,
                    'message' => 'Item not on order'
                ]);
            }
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
     * @param Order $order
     * @param array $data
     * @return Order $order
     */
    protected function updateOrder(Order $order, array $data): Order
    {
        $data = array_change_key_case($data, CASE_LOWER);
        foreach ($data as $key => $value) {
            switch ($key) {
                case 'location':
                    $order->setLocation($value);
                    break;
                case 'locationid':
                    $order->setLocationId($value);
                    break;
                case 'server':
                    $order->setServer($value);
                    break;
                case 'customer':
                    $order->setCustomer($value);
                    break;
                case 'items':
                    $this->updateItems($order, $value);
                    break;
                case 'discount':
                    $order->setDiscount($value);
                    break;
                /*case 'total':
                    $order->setTotal($value);
                    break;*/
            }
        }
        $order->setTotal($this->calculateTotal($order));
        return $order;
    }

    /**
     * @param Order $order
     * @param array $items
     */
    protected function updateItems(Order $order, array $items)
    {
        foreach ($items as $item) {
            $orderItem = $this->orderItemFactory->createFromArray($item);
            $order->setItem($orderItem);
        }
    }

    /**
     * @param Order $order
     * @return string
     */
    protected function calculateTotal(Order $order): string
    {
        $total = '0';
        foreach ($order->getItems() as $item) {
            /** @var OrderItem $item */
            $total = bcadd($total, $item->getCost(), 4);
        }
        if ($order->getDiscount() !== 0) {
            $percent = bcdiv((string)$order->getDiscount(), '100', 4);
            $total = bcsub($total, bcmul($total, $percent, 4), 4);
        }
        return $total;
    }

    /**
     * Verifies that item to change is present on requested order
     * @param Order $order
     * @param array $items
     * @return bool
     */
    private function isItemOnOrder(Order $order, array $items): bool
    {
        $itemsOnOrder = true;
        foreach ($items['items'] as $item)
        {
            $orderItem = $order->getItem($item['itemUUID']);
            if($orderItem == null) {
                $itemsOnOrder = false;
            }
        }
        return $itemsOnOrder;
    }
}
