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
        Authorizer  $authorizer,
        DocumentManager $documentManager,
        JsonResponseFactory $responseFactory
    ) {
        $this->authorizer = $authorizer;
        $this->documentManager = $documentManager;
        $this->responseFactory = $responseFactory;
    }

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
