<?php

namespace App\DTO;

class ArrayBuilder
{
    public function ordersArray($orders)
    {
        $ordersArray = [];

        foreach ($orders as $order) {
            $itemsArray = [];
            $items = $order->getPersistentItems()->getValues();
            foreach ($items as $item) {
                $itemsArray[] = [
                    'id' => $item->getId(),
                    'name' => $item->getName(),
                    'cost' => $item->getCost()
                ];
            }

            $ordersArray[] = [
                'orderId' => $order->getOrderID(),
                'location'  => $order->getLocation(),
                'locationId'  => $order->getLocationId(),
                'server'  => $order->getServer(),
                'customer'  => $order->getCustomer(),
                'items'  => $itemsArray,
                'discount'  => $order->getDiscount(),
                'total'  => $order->getTotal(),
                'orderDate'  => $order->getOrderDate()
            ];
        }
        return $ordersArray;
    }
}
