<?php

namespace App\Actions;

use App\Documents\Order;
use Doctrine\ODM\MongoDB\DocumentManager;
use MMSM\Lib\Authorizer;
use MMSM\Lib\Factories\JsonResponseFactory;
use Psr\Http\Message\ResponseInterface;
use SimpleJWT\JWT;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Psr7\Request;
use Throwable;

class Read
{
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
     * Authorizer for verification of user permissions
     * @var Authorizer
     */
    private Authorizer $authorizer;

    /**
     * Read constructor.
     * @param DocumentManager $documentManager
     * @param JsonResponseFactory $responseFactory
     * @param Authorizer $authorizer
     */
    public function __construct(
        DocumentManager $documentManager,
        JsonResponseFactory $responseFactory,
        Authorizer $authorizer
    )
    {
        $this->documentManager = $documentManager;
        $this->responseFactory = $responseFactory;
        $this->authorizer = $authorizer;
    }

    /**
     * @OA\Get(
     *      path="/api/v1/orders/{orderId}",
     *      summary="Reads requested order from database",
     *      description="Returns a JSON representation of the requested order",
     *      tags={"Orders"},
     *      security={{ "bearerAuth":{} }},
     *      @OA\Parameter(
     *          name="orderId",
     *          in="path",
     *          description="The order to get",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/Order"),
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
     *          response=404,
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
                'user.roles.customer',
                'user.roles.employee',
                'user.roles.admin',
                'user.roles.super',
            ]
        );

        $token = $request->getAttribute('token');
        if (!($token instanceof JWT)) {
            throw new HttpUnauthorizedException($request, 'Unauthorized');
        }
        /** @var Order $order */
        $order = $this->documentManager->find(Order::class, $orderId);

        if (($order instanceof Order) && ($this->isOrderOwner($token, $order) || $this->isEmployee($request))) {
            return $this->responseFactory->create(200, ['orders' => $order->toArray()]);
        }
        return $this->responseFactory->create(404, [
            'error' => true,
            'message' => 'Order not found / Does not exist',
        ]);
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
