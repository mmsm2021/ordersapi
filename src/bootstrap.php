<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;
use MongoDB\Client;

if ( ! file_exists($file = __DIR__.'/vendor/autoload.php')) {
    throw new RuntimeException('Install dependencies to run this script.');
}

$loader = require_once $file;
$loader->add('Documents', __DIR__);

AnnotationRegistry::registerLoader([$loader, 'loadClass']);

$client = new Client('mongodb://FranDineReadWrite:ReadWritePassword@mongo', [], ['typeMap' => DocumentManager::CLIENT_TYPEMAP]);
$config = new Configuration();
$config->setProxyDir(__DIR__ . '/Proxies'); # Verify necessity
$config->setProxyNamespace('Proxies'); # Verify necessity
$config->setHydratorDir(__DIR__ . '/Hydrators'); # Verify necessity
$config->setHydratorNamespace('Hydrators'); # Verify necessity
$config->setDefaultDB('FranDine');
$config->setMetadataDriverImpl(AnnotationDriver::create(__DIR__ . '/Documents'));

$dm = DocumentManager::create($client, $config);