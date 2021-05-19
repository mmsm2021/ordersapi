<?php
require_once 'vendor/autoload.php';
$openapi = \OpenApi\scan(__DIR__, [
    'exclude' => [
        __DIR__ . '/vendor'
    ]
]);
echo $openapi->toJson();
