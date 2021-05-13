<?php

use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;
use MongoDB\Client;
use Psr\Container\ContainerInterface;

return [
    'mongo.uri' => \DI\env('MONGO_URI'),
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
    }
];
