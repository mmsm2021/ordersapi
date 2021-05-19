<?php

namespace App\Actions;

use App\Documents\Order;
use App\DTO\Validators\OrderValidator;
use Doctrine\ODM\MongoDB\DocumentManager;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Exceptions\ValidationException;
use Slim\Psr7\Factory\ResponseFactory;
use Throwable;

class Create
{
    /** Document manager used for persisting Document */
    private $documentManager;
    /** Validator for validation of order contents */
    private $orderValidator;
    /** Factory for HTTP response */
    private $responseFactory;

    public function __construct(
        DocumentManager $documentManager,
        OrderValidator $orderValidator,
        ResponseFactory $responseFactory
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
    public function __invoke(Request $request, Response $response)
    {
        try {
            $this->orderValidator->validate($request->getParsedBody());
            $order = $this->createOrder($request->getParsedBody());
            $this->documentManager->persist($order);
            $this->documentManager->flush();
            $response->getBody()->write($order->getOrderId());
            return $response;
        } catch (ValidationException $e) {
            $response = $this->responseFactory->createResponse(400);
            $response->getBody()->write($e->getMessage());
            return $response;
        } catch (Throwable $e) {
            $response = $this->responseFactory->createResponse(500);
            $response->getBody()->write($e->getMessage());
            return $response;
        }
    }

    function createOrder($data)
    {
        $order = new Order();
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
                case 'customer':
                    $order->setCustomer($value);
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
