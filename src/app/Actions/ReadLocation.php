<?php

namespace App\Actions;

use App\Documents\Order;
use Doctrine\ODM\MongoDB\DocumentManager;
use MMSM\Lib\Factories\JsonResponseFactory;
use Slim\Psr7\Request;
use Throwable;

class ReadLocation
{
    /**
     * Document manager used for persisting and reading Documents
     * @var DocumentManager
     */
    private $documentManager;

    /**
     * Factory for JSON HTTP response
     * @var JsonResponseFactory
     */
    private JsonResponseFactory $responseFactory;

    /**
     * ReadLocation constructor.
     * @param DocumentManager $documentManager
     * @param JsonResponseFactory $responseFactory
     */
    public function __construct(
        DocumentManager $documentManager,
        JsonResponseFactory $responseFactory
    ) {
        $this->documentManager = $documentManager;
        $this->responseFactory = $responseFactory;
    }

    /**
     * @param Request $request
     * @param int $locationId
     * @return ResponseInterface
     */
    #public function __invoke($locationId, $sortBy, $page, $size)
    public function __invoke(Request $request, $locationId)
    {
        try {
            $sortBy = $request->getQueryParams()['sortBy'];
            $page = $request->getQueryParams()['page'] - 1;
            $size = $request->getQueryParams()['size'];
            $orders = $this->documentManager->createQueryBuilder(Order::class)->field('locationId')->equals($locationId)->sort($sortBy, 'desc')->limit($size)->skip($page * $size)->getQuery()->execute();
            $sendBack = [];
            foreach ($orders as $order) {
                $sendBack[] = $order->toArray();
            }
            return $this->responseFactory->create(200, ['orders' => $sendBack]);
        } catch (Throwable $e) {
            return $this->responseFactory->create(400, [
                'error' => true,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
