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
    )
    {
        $this->authorizer = $authorizer;
        $this->documentManager = $documentManager;
        $this->patchValidator = $patchValidator;
        $this->responseFactory = $responseFactory;
        $this->orderItemFactory = $orderItemFactory;
    }

    /**
     * @OA\Patch(
     *      path="/api/v1/orders/{orderId}",
     *      summary="Updates the order items",
     *      description="For the specified order, items specified in carried JSON are updated as specified, order total is updated accordingly",
     *      tags={"Orders"},
     *      security={{ "bearerAuth":{} }},
     *      @OA\Parameter(
     *          name="orderID",
     *          in="path",
     *          description="The id of the order to be updated",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="The OrderItems to set as delivered",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  schema="OrderItemsUpdateObject",
     *                  type="object",
     *                  description="Object containing the order items to be updated",
     *                  @OA\Property(
     *                      property="items",
     *                      description="Array of OrderItems, to be changed, specifying changes for each item",
     *                      type="array",
     *                      @OA\Items(
     *                          @OA\Property(
     *                              property="itemUUID",
     *                              description="The unique identifier of the item",
     *                              type="string"
     *                          ),
     *                          @OA\Property(
     *                              property="nr",
     *                              description="The menu number for the item",
     *                              type="number"
     *                          ),
     *                          @OA\Property(
     *                              property="name",
     *                              description="The name of the item",
     *                              type="string"
     *                          ),
     *                          @OA\Property(
     *                              property="cost",
     *                              description="The prize of the item",
     *                              type="string"
     *                          )
     *                      )
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  schema="UserOrdersObject",
     *                  type="object",
     *                  description="JSON representation of updated order",
     *                  @OA\Property(
     *                      property="orders",
     *                      description="Array orders",
     *                      type="array",
     *                      @OA\Items(
     *                          ref="#/components/schemas/Order"
     *                      )
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="will contain a JSON object with a message.",
     *              @OA\MediaType(
     *                  mediaType="application/json",
     *                  @OA\Schema(
     *                      @OA\Property(
     *                          property="error",
     *                          type="boolean"
     *                   ),
     *                  @OA\Property(
     *                      property="message",
     *                      type="string"
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="will contain a JSON object with a message.",
     *              @OA\MediaType(
     *                  mediaType="application/json",
     *                  @OA\Schema(
     *                      @OA\Property(
     *                          property="error",
     *                          type="boolean"
     *                   ),
     *                  @OA\Property(
     *                      property="message",
     *                      type="array",
     *                      @OA\Items(
     *                              type="string"
     *                      )
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="will contain a JSON object with a message.",
     *              @OA\MediaType(
     *                  mediaType="application/json",
     *                  @OA\Schema(
     *                      @OA\Property(
     *                          property="error",
     *                          type="boolean"
     *                   ),
     *                  @OA\Property(
     *                      property="message",
     *                      type="array",
     *                      @OA\Items(
     *                              type="string"
     *                      )
     *                  )
     *              )
     *          )
     *      )
     *  )
     */

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

        $body = $request->getParsedBody();
        $this->patchValidator->validate($body);
        /** @var Order $order */
        $order = $this->documentManager->find(Order::class, $orderId);
        $itemsOnOrder = $this->isItemOnOrder($order, $body);
        if ($itemsOnOrder) {
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
        foreach ($items['items'] as $item) {
            $orderItem = $order->getItem($item['itemUUID']);
            if ($orderItem == null) {
                $itemsOnOrder = false;
            }
        }
        return $itemsOnOrder;
    }
}
