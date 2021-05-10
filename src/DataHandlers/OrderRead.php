<?php

use Doctrine\Common\Collections\ArrayCollection;
use Documents\Order;

$query_pos = strrpos($_SERVER['REQUEST_URI'],"/");
$id = substr($_SERVER['REQUEST_URI'], $query_pos+1);

$order = $dm->find(Order::class, $id);
$orderJson = new OrderJson($order);
echo json_encode($orderJson, JSON_UNESCAPED_UNICODE);

class OrderJson
{
    public $orderId;
    public $location;
    public $locationId;
    public $server;
    public $customer;
    private $persistentItems;
    public $items;
    public $discount;
    public $total;
    public $orderDate;

    public function __construct($order)
    {
        $this->orderId = $order->getOrderId();
        $this->location = $order->getLocation();
        $this->locationId = $order->getLocationId();
        $this->server = $order->getServer();
        $this->customer = $order->getCustomer();
        $this->persistentItems = $order->getPersistentItems()->getValues();
        $this->discount = $order->getDiscount();
        $this->total = $order->getTotal();
        $this->orderDate = $order->getOrderDate();

        foreach($order->getPersistentItems()->getValues() as $item) {
            
            $this->items[] = new class($item) {
                public $id;
                public $name;
                public $cost;
        
                public function __construct($item)
                {
                    $this->id = $item->getId();
                    $this->name = $item->getName();
                    $this->cost = $item->getCost();
                }
            };
        }
    }
}