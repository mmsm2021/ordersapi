<?php
require_once __DIR__ . '/vendor/autoload.php';
echo \Ramsey\Uuid\Uuid::uuid4()->toString() . PHP_EOL;