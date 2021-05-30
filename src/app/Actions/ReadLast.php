<?php

namespace App\Actions;

use App\Documents\Order;
use Doctrine\ODM\MongoDB\DocumentManager;
use Psr\Http\Message\ServerRequestInterface as Request;
use MMSM\Lib\Authorizer;
use MMSM\Lib\Factories\JsonResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class ReadLast
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
     * ReadLocation constructor.
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
     *  @OA\Get(
     *      path="/api/v1/orders/{locationId}/last/{n}",
     *      summary="Reads the last {n} orders from the specified location",
     *      description="Returns a JSON representation of the requested amount of orders, if less is available, this lesser amount is returned",
     *      tags={"Orders"},
     *      @OA\Parameter(
     *          name="Authorization",
     *          in="header",
     *          description="Bearer {id-token}",
     *          required=true
     *      ),
     *      @OA\Parameter(
     *          name="locationId",
     *          in="path",
     *          description="The location for which to get orders",
     *          required=true
     *      ),
     *      @OA\Parameter(
     *          name="n",
     *          in="path",
     *          description="The amount of orders to get",
     *          required=true
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  schema="LastOrdersObject",
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
     *          response=400,
     *          description="will contain a JSON object with a message.",
     *              @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="error",
     *                      type="boolean"
     *                  ),
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
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="error",
     *                      type="boolean"
     *                  ),
     *                  @OA\Property(
     *                      property="message",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="code",
     *                      type="number"
     *                  )
     *              )
     *          )
     *      )
     *  )
     */

    /**
     * @param Request $request
     * @param string $locationId
     * @param int $n
     * @return ResponseInterface
     * @throws Throwable
     */
    public function __invoke(Request  $request, string $locationId, int $n): ResponseInterface
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
            $count = $this->documentManager->createQueryBuilder(Order::class)->field('locationId')->equals($locationId)->count()->getQuery()->execute();
            $n = $count < $n ? $count : $n;
            $orders = $this->documentManager->createQueryBuilder(Order::class)->field('locationId')->equals($locationId)->skip($count - $n)->getQuery()->execute();
            $sendBack = [];
            foreach ($orders as $order) {
                $sendBack[] = $order->toArray();
            }
            return $this->responseFactory->create(200, ['orders' => $sendBack]);
        } catch (Throwable $e) {
            return $this->responseFactory->create(400, [
                'error' => true,
                'message' => $e->getMessage()()
            ]);
        }
    }
}
