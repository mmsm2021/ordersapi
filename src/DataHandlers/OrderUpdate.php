<?php

use Documents\Order;

/** Gets JSON object from received request, and gets document from DataBase */
$data = json_decode(file_get_contents('php://input'));
try {
    $order = $dm->find(Order::class, $data->orderId);
} catch (Exception $e) {
    header('HTTP/1.0 500 INTERNAL SERVER ERROR');
    echo "Order could not be collected from Data Base\n",  $e->getMessage(), "\n";
}

/** Updates data in document */
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
            $order->clearItems();
            $order->addItems($value);
            break;
        case 'discount':
            $order->setDiscount($value);
            break;
        case 'total':
            $order->setTotal($value);
            break;
    }
}

/** Stores updated document in database */
try {
    $dm->persist($order);
    $dm->flush();
} catch (Exception $e) {
    header('HTTP/1.0 500 INTERNAL SERVER ERROR');
    echo "Order could not be updated in Data Base\n",  $e->getMessage(), "\n";
}
