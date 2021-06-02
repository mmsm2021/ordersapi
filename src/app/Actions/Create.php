<?php

namespace App\Actions;

use App\Documents\Order;
use App\DTO\Validators\OrderValidator;
use App\DTO\Validators\QuoteValidator;
use App\Factories\OrderItemFactory;
use Doctrine\ODM\MongoDB\DocumentManager;
use MMSM\Lib\Authorizer;
use MMSM\Lib\Factories\JsonResponseFactory;
use MMSM\Lib\JwtHandler;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use SimpleJWT\InvalidTokenException;
use SimpleJWT\JWT;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpInternalServerErrorException;
use Throwable;

class Create
{
    /**
     * Document manager used for persisting and reading Documents
     * @var DocumentManager
     */
    private DocumentManager $documentManager;

    /**
     * Validator for validation of order contents
     */
    private OrderValidator $orderValidator;

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
     * Container for various definitions
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    /**
     * Factory for creation of orderitems
     * @var OrderItemFactory
     */
    private OrderItemFactory $orderItemFactory;

    /**
     * Handler for accessing product tokens
     * @var JwtHandler
     */
    private JwtHandler $jwtHandler;

    /**
     * @var QuoteValidator
     */
    private QuoteValidator $quoteValidator;

    /**
     * Create constructor.
     * @param DocumentManager $documentManager
     * @param OrderValidator $orderValidator
     * @param JsonResponseFactory $responseFactory
     * @param Authorizer $authorizer
     * @param ContainerInterface $container
     * @param OrderItemFactory $orderItemFactory
     * @param JwtHandler $jwtHandler
     * @param QuoteValidator $quoteValidator
     */
    public function __construct(
        DocumentManager $documentManager,
        OrderValidator $orderValidator,
        JsonResponseFactory $responseFactory,
        Authorizer $authorizer,
        ContainerInterface $container,
        OrderItemFactory $orderItemFactory,
        JwtHandler $jwtHandler,
        QuoteValidator $quoteValidator
    )
    {
        $this->documentManager = $documentManager;
        $this->orderValidator = $orderValidator;
        $this->responseFactory = $responseFactory;
        $this->authorizer = $authorizer;
        $this->container = $container;
        $this->orderItemFactory = $orderItemFactory;
        $this->jwtHandler = $jwtHandler;
        $this->quoteValidator = $quoteValidator;
    }

    /**
     * @OA\Post(
     *      path="/api/v1/orders",
     *      summary="Creates new order from carried JSON",
     *      tags={"Orders"},
     *      security={{ "bearerAuth":{} }},
     *      @OA\RequestBody(
     *         required=true,
     *         description="The Location that you want to create.",
     *         @OA\JsonContent(ref="#/components/schemas/OrderCreateObject"),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Will reply with the orderId of the newly created order",
     *          @OA\JsonContent(ref="#/components/schemas/OrderCreatedObject")
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="will contain a JSON object with a message.",
     *          @OA\JsonContent(ref="#/components/schemas/error")
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="will contain a JSON object with a message.",
     *          @OA\JsonContent(ref="#/components/schemas/error")
     *      )
     *  )
     */

    /**
     * @param Request $request
     * @return ResponseInterface
     * @throws HttpBadRequestException
     * @throws HttpInternalServerErrorException
     * @throws Throwable
     */
    public function __invoke(Request $request): ResponseInterface
    {
        $body = $request->getParsedBody();
        if (!is_array($body) && empty($body)) {
            throw new HttpBadRequestException(
                $request,
                'Invalid body.'
            );
        }
        $this->orderValidator->validate($body);
        $this->authorizer->authorizeToRoles(
            $request,
            [
                'user.roles.customer',
                'user.roles.employee',
                'user.roles.admin',
                'user.roles.super',
            ]
        );
        $order = $this->createOrder($request);
        $this->documentManager->persist($order);
        $this->documentManager->flush();
        return $this->responseFactory->create(200, ['orderId' => $order->getOrderId()]);
    }

    /**
     * Creates the order based on the received JSON and info from JWT
     * @param Request $request
     * @return Order $order
     * @throws HttpBadRequestException
     */
    public function createOrder(Request $request): Order
    {
        $isSuperAdmin = $this->authorizer->hasRole($request, 'user.roles.super');
        $data = $request->getParsedBody();
        $order = new Order();
        $order->setLocation($data['location']);
        if ($this->authorizer->hasRole($request, 'user.roles.customer')) {
            $order->setCustomer($request->getAttribute('token')->getClaim('sub'));
        } else {
            $order->setServer($request->getAttribute('token')->getClaim('sub'));
        }

        $tokens = [];
        $failedTokens = [];

        $orderTotal = '0';
        $locationId = null;
        foreach ($data['items'] as $item) {
            try{
                $decodedToken = $this->jwtHandler->decode($item);
                $claims = $decodedToken->getClaims();
                $this->quoteValidator->check($claims);
                $tokens[] = $decodedToken;
                $orderTotal = bcadd($orderTotal, $claims['totalPrice']);
                if ($locationId === null) {
                    $locationId = $claims['product']['locationId'];
                } else {
                    if($claims['product']['locationId'] != $locationId) {
                        throw new HttpBadRequestException(
                            $request,
                            'You cannot order products for different locations at the same time.'
                        );
                    }
                }
            } catch (InvalidTokenException $invalidTokenException) {
                $failedTokens[] = $item;
            }
        }
        if (!empty($failedTokens)) {
            throw new HttpBadRequestException($request, 'One or more invalid tokens received!');
        }
        $order->setLocationId($locationId);
        foreach ($tokens as $item) {
            $oi = $this->orderItemFactory->createFromArray($item);
            $order->addItem($oi);
        }
        if ($isSuperAdmin || (
                $this->authorizer->hasRoles($request, ['user.roles.employee', 'user.roles.admin']) &&
                $this->authorizer->isUserInLocation($request, $locationId)
            )
        ) {
            $order->setDiscount($data['discount']);
        }
        $order->setTotal($orderTotal);
        return $order;
    }
}
