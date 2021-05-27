<?php

namespace App\Actions;

use App\Documents\Order;
use Doctrine\ODM\MongoDB\DocumentManager;
use MMSM\Lib\Authorizer;
use MMSM\Lib\Factories\JsonResponseFactory;
use Psr\Http\Message\ResponseInterface;
use SimpleJWT\JWT;
use Slim\Exception\HttpException;
use Slim\Psr7\Request;
use Throwable;

class ReadUser
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
     * ReadUser constructor.
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
     * @param string $userId
     * @return ResponseInterface
     * @throws Throwable
     */
    public function __invoke(Request $request, string $userId): ResponseInterface
    {
        $this->authorizer->authorizeToRoles(
            $request,
            [
                'user.roles.customer',
                'user.roles.employee',
                'user.roles.admin',
                'user.roles.super',
            ]
        );
        $isRequestingUser = $this->isRequestingUser($request->getAttribute('token'), $userId);
        $isEmployee = $this->isEmployee($request);
        if($isRequestingUser || $isEmployee) {
            try {
                $orders = $this->documentManager->getRepository(Order::class)->findBy([
                    'customer' => $userId
                ]);
                $sendBack = [];
                foreach ($orders as $order) {
                    $sendBack[] = $order->toArray();
                }
                return $this->responseFactory->create(200, ['orders' => $sendBack]);
            } catch (Throwable $e) {
                if ($e instanceof HttpException) {
                    throw $e;
                }
                return $this->responseFactory->create(400, [
                    'error' => true,
                    'message' => $e->getMessage(),
                ]);
            }
        }
        return $this->responseFactory->create(401, [
            'error' => true,
            'message' => 'Unauthorized',
        ]);
    }

    /**
     * Verifies equality of requesting user and user for whom orders are being requested
     * @param JWT $token
     * @param string $user
     * @return bool
     */
    private function isRequestingUser(JWT $token, string $user): bool
    {
        if($token instanceof JWT && ($token->getClaims()['sub'] === $user)) {
            return true;
        }
        return false;
    }

    /**
     * Verifies that user is Employee
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
                    'user.roles.admin',
                    'user.roles.super',
                ]
            );
        } catch (Throwable $e) {
            return false;
        }
    }
}
