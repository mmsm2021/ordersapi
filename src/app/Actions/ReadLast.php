<?php

namespace App\Actions;

use App\Documents\Order;
use Doctrine\ODM\MongoDB\DocumentManager;
use MMSM\Lib\Factories\JsonResponseFactory;
use Throwable;

class ReadLast
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
     * @param $locationId
     * @param int $n
     * @return ResponseInterface
     */
    public function __invoke($locationId, int $n)
    {
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
