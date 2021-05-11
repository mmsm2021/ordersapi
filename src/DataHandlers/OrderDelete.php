<?php

use Documents\Order;

$query_pos = strrpos($_SERVER['REQUEST_URI'],"/");
$id = substr($_SERVER['REQUEST_URI'], $query_pos+1);

try{
    $order = $dm->find(Order::class, $id);
    $dm->remove($order);
    $dm->flush();
} catch (Exception $e) {
    header('HTTP/1.0 500 INTERNAL SERVER ERROR');
    echo "Order delete couldn not be completed\n",  $e->getMessage(), "\n";
}