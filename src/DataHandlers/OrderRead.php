<?php

use App\DataModels\OrderJson;
use App\Documents\Order;

$query_pos = strrpos($_SERVER['REQUEST_URI'], "/");
$id = substr($_SERVER['REQUEST_URI'], $query_pos + 1);

try {
    $order = $dm->find(Order::class, $id);
} catch (Exception $e) {
    header('HTTP/1.0 500 INTERNAL SERVER ERROR');
    echo "Order could not be collected from Data Base\n",  $e->getMessage(), "\n";
}
if (null != $order) {
    $orderJson = new OrderJson($order);
    echo json_encode($orderJson, JSON_UNESCAPED_UNICODE);
} else {
    header('HTTP/1.0 500 INTERNAL SERVER ERROR');
    echo "Order does not exist";
}
