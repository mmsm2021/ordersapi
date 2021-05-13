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
    /** Factory for responses */
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
