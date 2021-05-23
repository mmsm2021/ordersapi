<?php

namespace App\Actions;

use App\Documents\Order;
use App\DTO\Validators\OrderValidator;
use Doctrine\ODM\MongoDB\DocumentManager;
use MMSM\Lib\Factories\JsonResponseFactory;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Exceptions\ValidationException;
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
     * Create constructor.
     * @param DocumentManager $documentManager
     * @param OrderValidator $orderValidator
     * @param JsonResponseFactory $responseFactory
     */
    public function __construct(
        DocumentManager $documentManager,
        OrderValidator $orderValidator,
        JsonResponseFactory $responseFactory
    ) {
        $this->documentManager = $documentManager;
        $this->orderValidator = $orderValidator;
        $this->responseFactory = $responseFactory;
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
     * @param Request $userIdrequest
     * @return ResponseInterface
     */
    public function __invoke(Request $request)
    {
        try {
            $this->orderValidator->validate($request->getParsedBody());
            $token = $request->getAttribute('token');
            $order = $this->createOrder($token, $request->getParsedBody());
            $this->documentManager->persist($order);
            $this->documentManager->flush();
            return $this->responseFactory->create(200, ['orderId' => $order->getOrderId()]);
        } catch (ValidationException $e) {
            return $this->responseFactory->create(400, [
                'error' => true,
                'message' => $e->getMessage(),
            ]);
        } catch (Throwable $e) {
            return $this->responseFactory->create(500, [
                'error' => true,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Creates the order based on the received JSON and info form JWT
     * @param JWT $token
     * @param array $data
     * @return Order $order
     */
    function createOrder($token, $data)
    {
        $order = new Order();
        $order->setCustomer($token->getClaims()['sub']);
        $order->setOrderStatus(1);
        $order->setOrderStatus(1);
        foreach ($data as $key => $value) {
            switch ($key) {
                case 'location':
                    $order->setLocation($value);
                    break;
                case 'locationId':
                    $order->setLocationId($value);
                    break;
                case 'server':
                    $order->setServer($value);
                    break;
                case 'items':
                    $order->addItems($value);
                    break;
                case 'discount':
                    $order->setDiscount($value);
                    break;
                case 'total':
                    $order->setTotal($value);
                    break;
            }
            $order->setOrderDate();
        }
        return $order;
    }
}
