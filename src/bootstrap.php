<?php

use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;
use MongoDB\Client;

if (!file_exists($file = __DIR__ . '/vendor/autoload.php')) {
    throw new RuntimeException('Install dependencies to run this script.');
}

require_once $file;

$client = new Client('mongodb://FranDineReadWrite:ReadWritePassword@mongo', [], ['typeMap' => DocumentManager::CLIENT_TYPEMAP]);
$config = new Configuration();
$config->setProxyDir(__DIR__ . '/Proxies');
$config->setProxyNamespace('Proxies');
$config->setHydratorDir(__DIR__ . '/Hydrators');
$config->setHydratorNamespace('Hydrators');
$config->setDefaultDB('FranDine');
$config->setDocumentNamespaces(['App\\Documents']);
$config->setMetadataDriverImpl(AnnotationDriver::create(__DIR__ . '/app/Documents'));

$dm = DocumentManager::create($client, $config);
