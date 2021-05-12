<?php

use App\Documents\Order;

/** Gets JSON object from received request */
$data = json_decode(file_get_contents('php://input'), true);

/** Order object is populated with relevant data */
$order = new Order();
foreach ($data as $key => $value) {
    switch ($key) {
        case 'location':
            $order->setLocation($value);
            break;
        case 'locationId':
            $order->setLocationId($value);
            break;
        case 'server':
            $order->setServer($value);
            break;
        case 'customer':
            $order->setCustomer($value);
            break;
        case 'items':
            $order->addItems($value);
            break;
        case 'discount':
            $order->setDiscount($value);
            break;
        case 'total':
            $order->setTotal($value);
            break;
    }
    $order->setOrderDate();
}

/** Order document is sent to DataBase */
try {
    $dm->persist($order);
    $dm->flush();
    echo $order->getOrderId();
} catch (Exception $e) {
    header('HTTP/1.0 500 INTERNAL SERVER ERROR');
    echo "Order could not be created in Data Base\n",  $e->getMessage(), "\n";
}
