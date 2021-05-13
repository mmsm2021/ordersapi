<?php

namespace App\Actions;

use App\Documents\Order;
use App\DTO\Validators\PatchValidator;
use Doctrine\ODM\MongoDB\DocumentManager;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Exceptions\ValidationException;
use Slim\Psr7\Factory\ResponseFactory;
use Throwable;

class Update
{
    /** Document manager used for persisting Document */
    private $documentManager;
    /** Validator for validation of PATCH Document */
    private $patchValidator;
    /** Factory for responses */
    private $responseFactory;

    public function __construct(
        DocumentManager $documentManager,
        PatchValidator $patchValidator,
        ResponseFactory $responseFactory
    ) {
        $this->documentManager = $documentManager;
        $this->responseFactory = $responseFactory;
        $this->patchValidator = $patchValidator;
    }

    public function __invoke(Request $request, Response $response, $orderId)
    {
        try {
            #$this->patchValidator->validate($request->getParsedBody());
            $order = $this->documentManager->find(Order::class, $orderId);
            $order = $this->updater($request->getParsedBody(), $order);
            $this->documentManager->persist($order);
            $this->documentManager->flush();
            return $response;
        } catch (ValidationException $e) {
            $response = $this->responseFactory->createResponse(400);
            $response->getBody()->write($e->getMessage());
            return $response;
        } catch (Throwable $e) {
            $response = $this->responseFactory->createResponse(400);
            $response->getBody()->write($e->getMessage());
            return $response;
        }
    }

    function updater($data, Order $order)
    {
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
                    $order->clearItems();
                    $order->addItems($value);
                    break;
                case 'discount':
                    $order->setDiscount($value);
                    break;
                case 'total':
                    $order->setTotal($value);
                    break;
            }
        }
        return $order;
    }
}
