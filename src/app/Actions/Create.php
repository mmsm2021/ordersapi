<?php

namespace App\Actions;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class Create
{
    public function __invoke(Request $request, Response $response)
    {
        $response->getBody()->write('mjello dude');
        return $response;
    }
}
