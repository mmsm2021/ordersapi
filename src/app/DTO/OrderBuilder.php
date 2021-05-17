<?php

namespace App\DTO;

class OrderBuilder
{
    public function ordersArray($order)
    {
        $orderArray = [];
        $itemsArray = [];
        $items = $order->getPersistentItems()->getValues();
        foreach ($items as $item) {
            $itemsArray[] = [
                'itemUUID' => $item->getUUID(),
                'nr' => $item->getNr(),
                'name' => $item->getName(),
                'cost' => $item->getCost()
            ];
        }

        $orderArray[] = [
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

        return $orderArray;
    }
}
