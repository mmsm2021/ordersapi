<?php

namespace App\Actions;

use App\Documents\Order;
use App\DTO\Validators\PatchValidator;
use Doctrine\ODM\MongoDB\DocumentManager;
use MMSM\Lib\Authorizer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use DateTime;
use MMSM\Lib\Factories\JsonResponseFactory;

class Delivered
{
    /**
     * authorizer for verification of user permissions
     * @var Authorizer
     */
    private Authorizer $authorizer;
    /**
     * Document manager used for persisting and reading Documents
     * @var DocumentManager
     */
    private DocumentManager $documentManager;

    /**
     * Validator for validation of PATCH Document
     * @var PatchValidator
     */
    private PatchValidator $patchValidator;

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
        Authorizer $authorizer,
        DocumentManager $documentManager,
        PatchValidator $patchValidator,
        JsonResponseFactory $responseFactory
    )
    {
        $this->authorizer = $authorizer;
        $this->documentManager = $documentManager;
        $this->responseFactory = $responseFactory;
        $this->patchValidator = $patchValidator;
    }

    /**
     * @OA\Patch(
     *      path="/api/v1/orders/delivered/{orderId}",
     *      summary="Sets specified item(s) as delivered, on specified order",
     *      description="Takes JSON patch documen, and sets each contained order item as delivered, for the order specified in path",
     *      tags={"Orders"},
     *      security={{ "bearerAuth":{} }},
     *      @OA\Parameter(
     *          name="orderId",
     *          in="path",
     *          description="The order on which to set items delivered",
     *          required=true
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="The OrderItems to set as delivered",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  schema="OrderItemDeliveredObject",
     *                  type="object",
     *                  description="Object containing the order items to set as delivered",
     *                  @OA\Property(
     *                      property="items",
     *                      description="Array of OrderItems",
     *                      type="array",
     *                      @OA\Items(
     *                          @OA\Property(
     *                              property="itemUUID",
     *                              description="The unique identifier of the item",
     *                              type="string"
     *                          ),
     *                          @OA\Property(
     *                              property="delivered",
     *                              description="Boolean to indicate that item is to be set as delivered",
     *                              type="boolean"
     *                          )
     *                      )
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Replies with JSON object, containing the new state of the order items of the order",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  schema="OrderItemDeliveredObject",
     *                  type="object",
     *                  description="Object containing the order items to set as delivered",
     *                  @OA\Property(
     *                      property="items",
     *                      description="Array of OrderItems",
     *                      type="array",
     *                      @OA\Items(
     *                          @OA\Property(
     *                              property="itemUUID",
     *                              description="The unique identifier of the item",
     *                              type="string"
     *                          ),
     *                          @OA\Property(
     *                              property="delivered",
     *                              description="Boolean to indicate that item is to be set as delivered",
     *                              type="boolean"
     *                          )
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
     * @param $orderId
     * @param Request $request
     * @return ResponseInterface
     */
    public function __invoke(Request $request, $orderId): ResponseInterface
    {
        $this->patchValidator->validate($request->getParsedBody());
        $this->authorizer->authorizeToRoles(
            $request,
            [
                'user.roles.employee',
                'user.roles.admin',
                'user.roles.super',
            ]
        );
        $order = $this->documentManager->find(Order::class, $orderId);
        $items = $request->getParsedBody()['items'];
        $server = $request->getAttribute('token')->getClaims()['sub'];
        /** @var Order $order */
        $order = $this->updater($items, $order, $server);
        if ($order instanceof ResponseInterface) {
            return $order;
        }
        $this->documentManager->persist($order);
        $this->documentManager->flush();
        return $this->responseFactory->create(200, [
            'items' => $order->getItemsArray()
        ]);
    }

    /**
     * Updates delivery status for the items specified in patch JSON
     * @param array $items
     * @param Order $order
     * @param string $server
     * @return Order | ResponseInterface
     */
    function updater(array $items, Order $order, string $server)
    {
        foreach ($items as $item) {
            $item = array_change_key_case($item, CASE_LOWER);
            $orderItem = $order->getItem($item['itemuuid']);
            if ($orderItem !== null) {
                $orderItem->setDelivered(new DateTime());
            } else {
                return $this->responseFactory->create(400, [
                    'error' => true,
                    'message' => 'Item not on order',
                ]);
            }
        }
        $order->setServer($server);
        return $order;
    }
}
