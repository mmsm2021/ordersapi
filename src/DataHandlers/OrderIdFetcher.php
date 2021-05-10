<?php

use Documents\Order;

/** Get the amount of orders being queried */
$query_pos = strrpos($_SERVER['REQUEST_URI'],"/");
$n = substr($_SERVER['REQUEST_URI'], $query_pos+1);

/** Get the last 'n' documents from DB, or just one if n is null */
$count = $dm->createQueryBuilder(Order::class)->count()->getQuery()->execute();
$orders;
if (null != $n)
{
    $orders = $dm->createQueryBuilder(Order::class)->select('orderId')->skip($count - $n)->getQuery()->execute();
} else {
    $orders = $dm->createQueryBuilder(Order::class)->select('orderId')->skip($count -1)->getQuery()->execute();
}

/** Return order ID(s) from document(s) */
$ordersArray = [];

foreach($orders as $order)
    {
        $ordersArray[] = $order->getOrderId();
    }

echo json_encode($ordersArray, JSON_UNESCAPED_UNICODE);