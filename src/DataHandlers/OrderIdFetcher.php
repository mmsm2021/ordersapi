<?php

use App\Documents\Order;

/** Get the amount of orders being queried */
$query_pos = strrpos($_SERVER['REQUEST_URI'], "/");
$n = substr($_SERVER['REQUEST_URI'], $query_pos + 1);

/** Get the last 'n' documents from DB */
try {
    $count = $dm->createQueryBuilder(Order::class)->count()->getQuery()->execute();
} catch (Exception $e) {
    header('HTTP/1.0 500 INTERNAL SERVER ERROR');
    echo "Order count could not be collected from Data Base\n",  $e->getMessage(), "\n";
}

$orders;
try {
    if (null != $n) {
        $orders = $dm->createQueryBuilder(Order::class)->select('orderId')->skip($count - $n)->getQuery()->execute();
    } else {
        $orders = $dm->createQueryBuilder(Order::class)->select('orderId')->skip($count - 1)->getQuery()->execute();
    }
} catch (Exception $e) {
    header('HTTP/1.0 500 INTERNAL SERVER ERROR');
    echo "Orders could not be collected from Data Base\n",  $e->getMessage(), "\n";
}

/** order ID(s) from document(s) are put in anonymous class*/
$ordersJson = new class($order)
{
    public $orders = [];

    public function addOrder($order): void
    {
        $this->orders[] = $order;
    }
};

foreach ($orders as $order) {
    $ordersJson->addOrder($order->getOrderID());
}

/** order(s) are returned */
echo json_encode($ordersJson, JSON_UNESCAPED_UNICODE);
