<?php

use Documents\Order;
use Documents\OrderItem;

/** Gets JSON object from received request */
$data = json_decode(file_get_contents('php://input'), true);

/** Order object is populated with relevant data */
$order = new Order();
foreach($data as $key => $value) {
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
$dm->persist($order);
$dm->flush();

/** If creation in DataBase was successfull ID is replied to request */
if(null != $order->getOrderId()) {
    echo $order->getOrderId();
} else {
    echo 'Order creation failed';
}
