<?php

use App\Exceptions\DefinitionException;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\Mapping\Driver\StaticPHPDriver;
use MMSM\Lib\AuthorizationMiddleware;
use MMSM\Lib\Parsers\JsonBodyParser;
use MMSM\Lib\Parsers\XmlBodyParser;
use MMSM\Lib\Validators\JWKValidator;
use MMSM\Lib\Validators\JWTValidator;
use MongoDB\Client;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Middleware\BodyParsingMiddleware;
use Slim\Psr7\Factory\ResponseFactory;

use function DI\env;
use function DI\create;

define('ROOT_DIR', __DIR__);

return [
    'root.dir' => ROOT_DIR,
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
        $config->setMetadataDriverImpl(new StaticPHPDriver(__DIR__ . '/app/Documents'));
        return $config;
    },
    DocumentManager::class => function (Client $client, Configuration $config) {
        return DocumentManager::create($client, $config);
    },
];
