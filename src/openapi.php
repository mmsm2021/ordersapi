<?php
require_once 'vendor/autoload.php';
$openapi = \OpenApi\scan(__DIR__ . '/app');
echo $openapi->toJson();
