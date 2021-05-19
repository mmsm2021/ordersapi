<?php

namespace App\Actions;

use Slim\Psr7\Factory\ResponseFactory;

class SwaggerJson
{
    private ResponseFactory $responseFactory;

    public function __construct(ResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function __invoke()
    {
        $response = $this->responseFactory->createResponse(200);
        $response->getBody()->write(file_get_contents(ROOT_DIR . '/swagger.json'));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
