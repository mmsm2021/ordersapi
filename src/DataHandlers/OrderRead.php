<?php

use Doctrine\Common\Collections\ArrayCollection;
use Documents\Order;

$query_pos = strrpos($_SERVER['REQUEST_URI'],"/");
$id = substr($_SERVER['REQUEST_URI'], $query_pos+1);

$order = $dm->find(Order::class, $id);
$orderJson = new OrderJson($order);
echo json_encode($orderJson, JSON_UNESCAPED_UNICODE);

/**
 * Class for serialization of order collected from DB
 *
 * @author  NINLeviathan
 */
class OrderJson
{
    /** Order ID */
    public $orderId;
    /** Location name */
    public $location;
    /** Location ID */
    public $locationId;
    /** Name or ID of waitress or waiter */
    public $server;
    /** Name or ID of customer */
    public $customer;
    /** NON-Serializable array of order items */
    private $persistentItems;
    /** Array of items purchased */
    public $items;
    /** Discount percentage given on order */
    public $discount;
    /** Amount total payed for order */
    public $total;
    /** Date of order */
    public $orderDate;

    /** Constructor, assigns relevant info to all properties */
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

        /** Each order item is put in an anonymous class and pushed to items array */
        foreach($order->getPersistentItems()->getValues() as $item) {
            
            $this->items[] = new class($item) {
                /** Order item ID */
                public $id;
                /** Order item name */
                public $name;
                /** Order item prize */
                public $cost;

                /** Constructor, assigns relevant info to all properties */
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