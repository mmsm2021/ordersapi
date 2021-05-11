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

/** order ID(s) from document(s) are put in anonymous class*/
$ordersJson = new class($order) {
    public $orders =[];

    public function addOrder($order): void { $this->orders[] = $order; }
};

foreach($orders as $order)
    {
        $ordersJson->addOrder($order->getOrderID());
    }

/** order(s) are returned */
echo json_encode($ordersJson, JSON_UNESCAPED_UNICODE);