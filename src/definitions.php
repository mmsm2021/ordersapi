<?php

use App\Exceptions\DefinitionException;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;
use MMSM\Lib\AuthorizationMiddleware;
use MMSM\Lib\Parsers\JsonBodyParser;
use MMSM\Lib\Parsers\XmlBodyParser;
use MMSM\Lib\Validators\JWKValidator;
use MMSM\Lib\Validators\JWTValidator;
use MongoDB\Client;
use Psr\Container\ContainerInterface;
use Slim\Middleware\BodyParsingMiddleware;
use function DI\env;

define('ROOT_DIR', __DIR__);

return [
    'environment' => env('ENV', 'development'),
    'auth.jwk_uri' => env('JWK_URI', false),
    'auth.allowedBearers' => [
        'Bearer'
    ],
    'mongo.uri' => env('MONGO_URI'),
    Client::class => function (ContainerInterface $container) {
        $connectionUri = $container->get('mongo.uri');
        return new Client($connectionUri, [], ['typeMap' => DocumentManager::CLIENT_TYPEMAP]);
    },
    Configuration::class => function () {
        $config = new Configuration();
        $config->setProxyDir(__DIR__ . '/app/Proxies');
        $config->setProxyNamespace('App\\Proxies');
        $config->setHydratorDir(__DIR__ . '/app/Hydrators');
        $config->setHydratorNamespace('App\\Hydrators');
        $config->setDefaultDB('FranDine');
        $config->setDocumentNamespaces(['App\\Documents']);
        $config->setMetadataDriverImpl(AnnotationDriver::create(__DIR__ . '/app/Documents'));
        return $config;
    },
    DocumentManager::class => function (Client $client, Configuration $config) {
        return DocumentManager::create($client, $config);
    },
    AuthorizationMiddleware::class => function(
        JWKValidator $JWKValidator,
        JWTValidator $JWTValidator,
        ContainerInterface $container
    ): AuthorizationMiddleware {
        $authMiddleware = new AuthorizationMiddleware($JWKValidator, $JWTValidator);
        if (stristr($container->get('environment'), 'prod') !== false) {
            $authMiddleware->loadJWKs('/keys/auth0_jwks.json');
        } else {
            if (!is_string($container->get('auth.jwk_uri'))) {
                throw new DefinitionException('invalid type gotten from "auth.jwk_uri".');
            }
            $authMiddleware->loadJWKs($container->get('auth.jwk_uri'), false);
        }
        foreach ($container->get('auth.allowedBearers') as $bearer) {
            $authMiddleware->addAllowedBearer($bearer);
        }
        return $authMiddleware;
    },
    BodyParsingMiddleware::class => function(JsonBodyParser $jsonBodyParser, XmlBodyParser $xmlBodyParser) {
        return new BodyParsingMiddleware([
            'application/json' => $jsonBodyParser,
            'application/xml' => $xmlBodyParser,
        ]);
    },
];
