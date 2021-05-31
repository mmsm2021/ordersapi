<?php

namespace App\Actions;

use App\Documents\Order;
use Doctrine\ODM\MongoDB\DocumentManager;
use MMSM\Lib\Authorizer;
use MMSM\Lib\Factories\JsonResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Request;
use Throwable;

class ReadLocation
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
    )
    {
        $this->authorizer = $authorizer;
        $this->documentManager = $documentManager;
        $this->responseFactory = $responseFactory;
    }

    /**
     * @OA\Get(
     *      path="/api/v1/orders/location/{locationId}",
     *      summary="Reads orders from the specified location",
     *      description="Returns a JSON representation of orders from the specified location, paginated, these orders are returned as specified in query params",
     *      tags={"Orders"},
     *      security={{ "bearerAuth":{} }},
     *      @OA\Parameter(
     *          name="sortBy",
     *          in="path",
     *          description="data to sort orders by",
     *          required=true
     *      ),
     *      @OA\Parameter(
     *          name="page",
     *          in="path",
     *          description="The desired page",
     *          required=true
     *      ),
     *      @OA\Parameter(
     *          name="size",
     *          in="path",
     *          description="The desired page size",
     *          required=true
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  schema="LocationOrdersObject",
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
     * @param string $locationId
     * @return ResponseInterface
     * @throws Throwable
     */
    public function __invoke(Request $request, string $locationId): ResponseInterface
    {
        $this->authorizer->authorizeToRoles(
            $request,
            [
                'user.roles.employee',
                'user.roles.admin',
                'user.roles.super',
            ]
        );
        $sortBy = $request->getQueryParams()['sortBy'];
        $page = $request->getQueryParams()['page'] - 1;
        $size = $request->getQueryParams()['size'];
        $orders = $this->documentManager->createQueryBuilder(Order::class)->field('locationId')->equals($locationId)->sort($sortBy, 'desc')->limit($size)->skip($page * $size)->getQuery()->execute();
        $sendBack = [];
        foreach ($orders as $order) {
            $sendBack[] = $order->toArray();
        }
        return $this->responseFactory->create(200, ['orders' => $sendBack]);
    }
}
