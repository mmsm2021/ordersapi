<?php

use Documents\Order;
use Documents\OrderItem;

$data = json_decode(file_get_contents('php://input'), true);

$order = new Order();

foreach($data as $key => $value) {
    switch ($key) {
        case 'location':
            $order->setLocation($value);
            break;
        case 'locationID':
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

$dm->persist($order);
$dm->flush();

echo $order->getOrderId();
