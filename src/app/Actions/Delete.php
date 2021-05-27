<?php

namespace App\Actions;

use App\Constants\OrderStatus;
use App\Documents\Order;
use Doctrine\ODM\MongoDB\DocumentManager;
use MMSM\Lib\Authorizer;
use MMSM\Lib\Factories\JsonResponseFactory;
use Psr\Http\Message\ResponseInterface;
use SimpleJWT\JWT;
use Slim\Psr7\Request;
use Throwable;

class Delete
{
    /**
     * Authorizer for verification of user permissions
     * @var Authorizer
     */
    private Authorizer $authorizer;

    /**
     * Document manager used for persisting and reading Documents
     * @var DocumentManager
     */
    private DocumentManager $documentManager;

    /**
     * Factory for JSON HTTP response
     * @var JsonResponseFactory
     */
    private JsonResponseFactory $responseFactory;

    /**
     * Delete constructor.
     * @param Authorizer $authorizer
     * @param DocumentManager $documentManager
     * @param JsonResponseFactory $responseFactory
     */
    public function __construct(
        Authorizer $authorizer,
        DocumentManager $documentManager,
        JsonResponseFactory $responseFactory
    ) {
        $this->authorizer = $authorizer;
        $this->documentManager = $documentManager;
        $this->responseFactory = $responseFactory;
    }

    /**
     * @param Request $request
     * @param string $orderId
     * @return ResponseInterface
     */
    public function __invoke(Request $request, string $orderId): ResponseInterface
    {
        if ($this->isAdmin($request)) {
            try {
                $this->documentManager->createQueryBuilder(Order::class)
                    ->findAndRemove()->field('orderId')->equals($orderId)->getQuery()->execute();
                return $this->responseFactory->create(200, [
                    'Delete' => 'success',
                ]);
            } catch (Throwable $e) {
                return $this->responseFactory->create(400, [
                    'error' => true,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        try {
            /** @var Order $order */
            $order = $this->documentManager->find(Order::class, $orderId);
            $isOrderOwner = $this->isOrderOwner($request->getAttribute('token'), $order);
        } catch (Throwable $e) {
            return $this->responseFactory->create(400, [
                'error' => true,
                'message' => $e->getMessage(),
            ]);
        }

        if($isOrderOwner) {
            $order->setOrderStatus(OrderStatus::CANCELED);
        }
        if($this->isEmployee($request)) {
            $order->setOrderStatus(OrderStatus::CANCELED);
        }
        try {
            $this->documentManager->persist($order);
            $this->documentManager->flush();
            return $this->responseFactory->create(200, [
                'Cancel' => 'success',
            ]);
        } catch (Throwable $e) {
            return $this->responseFactory->create(400, [
                'error' => true,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Verifies that requesting user is the same as user on order
     * @param JWT $token
     * @param Order $order
     * @return bool
     */
    private function isOrderOwner(JWT $token, Order $order): bool
    {
        if ($token->getClaims()['sub'] === $order->getCustomer()) {
            return true;
        }
        return false;
    }

    /**
     * Verifies if requester is an employee
     * @param Request $request
     * @return bool
     */
    private function isEmployee(Request $request): bool
    {
        try {
            return $this->authorizer->authorizeToRoles(
                $request,
                [
                    'user.roles.employee',
                ]
            );
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Verifies if requester is an admin
     * @param Request $request
     * @return bool
     */
    private function isAdmin(Request  $request): bool
    {
        try {
            return $this->authorizer->authorizeToRoles(
                $request,
                [
                    'user.roles.admin',
                    'user.roles.super',
                ]
            );
        } catch (Throwable $e) {
            return false;
        }
    }
}
