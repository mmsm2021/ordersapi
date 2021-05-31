<?php

namespace App\Actions;

use App\Documents\Order;
use App\DTO\Validators\OrderValidator;
use App\Factories\OrderItemFactory;
use Doctrine\ODM\MongoDB\DocumentManager;
use MMSM\Lib\Authorizer;
use MMSM\Lib\Factories\JsonResponseFactory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
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
     * Create constructor.
     * @param DocumentManager $documentManager
     * @param OrderValidator $orderValidator
     * @param JsonResponseFactory $responseFactory
     * @param Authorizer $authorizer
     * @param ContainerInterface $container
     * @param OrderItemFactory $orderItemFactory
     */
    public function __construct(
        DocumentManager $documentManager,
        OrderValidator $orderValidator,
        JsonResponseFactory $responseFactory,
        Authorizer $authorizer,
        ContainerInterface $container,
        OrderItemFactory $orderItemFactory
    )
    {
        $this->documentManager = $documentManager;
        $this->orderValidator = $orderValidator;
        $this->responseFactory = $responseFactory;
        $this->authorizer = $authorizer;
        $this->container = $container;
        $this->orderItemFactory = $orderItemFactory;
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
        if (empty($body)) {
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
        $order = $this->createOrder($request->getAttribute('token'), $body);
        $this->documentManager->persist($order);
        $this->documentManager->flush();
        return $this->responseFactory->create(200, ['orderId' => $order->getOrderId()]);
    }

    /**
     * Creates the order based on the received JSON and info from JWT
     * @param JWT $token
     * @param array $data
     * @return Order $order
     */
    public function createOrder(JWT $token, array $data): Order
    {
        $order = new Order();
        $order->setLocation($data['location']);
        $order->setLocationId($data['locationId']);
        if ($this->isCustomer($token)) {
            $order->setCustomer($token->getClaim('sub'));
        } else {
            $order->setServer($token->getClaim('sub'));
        }

        foreach ($data['items'] as $item) {
            $oi = $this->orderItemFactory->createFromArray($item);
            $order->addItem($oi);
        }
        $order->setDiscount($data['discount']);
        $order->setTotal($data['total']);
        return $order;
    }

    /**
     * Verifies that JWT identifies a user
     * @param JWT $token
     * @return bool
     */
    protected function isCustomer(JWT $token): bool
    {
        $customerRoles = $this->container->get('user.roles.customer');
        $namespace = $this->container->get('custom.tokenClaim.namespace');
        foreach ($token->getClaim($namespace . '/roles') as $role) {
            if (in_array(strtolower($role), $customerRoles)) {
                return true;
            }
        }
        return false;
    }
}
