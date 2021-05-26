<?php

namespace App\Actions;

use App\Documents\Order;
use App\DTO\Validators\OrderValidator;
use App\Factories\OrderItemFactory;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use MMSM\Lib\Authorizer;
use MMSM\Lib\Factories\JsonResponseFactory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Exceptions\ValidationException;
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
    private $orderValidator;

    /**
     * Factory for JSON HTTP response
     * @var JsonResponseFactory
     */
    private JsonResponseFactory $responseFactory;

    /**
     * @var Authorizer
     */
    private Authorizer $authorizer;

    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    /**
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
    ) {
        $this->documentManager = $documentManager;
        $this->orderValidator = $orderValidator;
        $this->responseFactory = $responseFactory;
        $this->authorizer = $authorizer;
        $this->container = $container;
        $this->orderItemFactory = $orderItemFactory;
    }

    /**
     * @OA\Post(
     *     path="/api/orders",
     *     summary="Creates new order from carried JSON",
     *     description="Order details are to be carried as JSON in requestbody , if/when validated this object is used for creation of new order",
     *     @OA\RequestBody(
     *             @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="location",
     *                     description="Name of the location where the order was placed",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="locationId",
     *                     description="The identifying number of the location at which the order was placed",
     *                     type="number"
     *                 ),
     *                 @OA\Property(
     *                     property="server",
     *                     description="The name of the Waiter/Waitress serving the order",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="customer",
     *                     description="The identifyer for the customer who placed the order",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="items",
     *                     description="Array holding the items purchased on the order",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(
     *                              property="nr",
     *                              description="The item menu number",
     *                              type="number"
     *                          ),
     *                         @OA\Property(
     *                              property="name",
     *                              description="Name of the item",
     *                              type="string"
     *                          ),
     *                         @OA\Property(
     *                              property="cost",
     *                              description="Prize of the item",
     *                              type="number"
     *                          )
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="discount",
     *                     description="The discount percentage applied on the order",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="total",
     *                     description="The total amount payed for the order",
     *                     type="string"
     *                 )
     *             )
     *         ),
     *         required=true,
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Will reply with the orderId of the newly created order",
     *     ), 
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     )   
     * )
     */

    /**
     * @param Request $request
     * @return ResponseInterface
     * @throws HttpInternalServerErrorException
     */
    public function __invoke(Request $request)
    {
        try {
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
        } catch (MongoDBException $mongoDBException) {
            throw new HttpInternalServerErrorException(
                $request,
                'Database error occurred',
                $mongoDBException
            );
        }
    }

    /**
     * Creates the order based on the received JSON and info form JWT
     * @param JWT $token
     * @param array $data
     * @return Order $order
     */
    public function createOrder(JWT $token, array $data)
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
     * @param JWT $token
     * @return bool
     */
    protected function isCustomer(JWT $token): bool
    {
        $customerRoles = $this->container->get('user.roles.customer');
        $namespace = $this->container->get('custom.tokenClaim.namespace');
        foreach ($token->getClaim($namespace . '/rules') as $role) {
            if (in_array(strtolower($role), $customerRoles)) {
                return true;
            }
        }
        return false;
    }
}
