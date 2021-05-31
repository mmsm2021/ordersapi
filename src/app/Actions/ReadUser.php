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
    )
    {
        $this->authorizer = $authorizer;
        $this->documentManager = $documentManager;
        $this->responseFactory = $responseFactory;
    }

    /**
     * @OA\Get(
     *      path="/api/v1/orders/user/{userId}",
     *      summary="Reads all orders for the specified user",
     *      description="Returns a JSON representation of all orders for the specified user",
     *      tags={"Orders"},
     *      security={{ "bearerAuth":{} }},
     *      @OA\Parameter(
     *          name="userId",
     *          in="path",
     *          description="The user for who to get orders",
     *          required=true
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  schema="UserOrdersObject",
     *                  type="object",
     *                  description="Object containing an array of orders",
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
        if ($isRequestingUser || $isEmployee) {
            $orders = $this->documentManager->getRepository(Order::class)->findBy([
                'customer' => $userId
            ]);
            $sendBack = [];
            foreach ($orders as $order) {
                $sendBack[] = $order->toArray();
            }
            return $this->responseFactory->create(200, ['orders' => $sendBack]);
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
        if ($token instanceof JWT && ($token->getClaims()['sub'] === $user)) {
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
