<?php

namespace App\Actions;

use App\Documents\Order;
use App\DTO\Validators\PatchValidator;
use Doctrine\ODM\MongoDB\DocumentManager;
use MMSM\Lib\Authorizer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Exceptions\ValidationException;
use DateTime;
use MMSM\Lib\Factories\JsonResponseFactory;
use Throwable;

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
    ) {
        $this->authorizer = $authorizer;
        $this->documentManager = $documentManager;
        $this->responseFactory = $responseFactory;
        $this->patchValidator = $patchValidator;
    }

    /**
     * @param $orderId
     * @param Request $request
     * @return ResponseInterface
     */
    public function __invoke(Request $request, $orderId): ResponseInterface
    {
        try {
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
     * @param array $items
     * @param Order $order
     * @param string $server
     * @return Order $order
     */
    function updater(array $items, Order $order, string $server): Order
    {
        foreach ($items as $item) {
            $item = array_change_key_case($item, CASE_LOWER);
            $orderItem = $order->getItem($item['itemuuid']);
            if($orderItem !== null) {
                $orderItem->setDelivered(new DateTime());
            }
        }
        $order->setServer($server);
        return $order;
    }
}
