<?php

namespace Documents;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use DateTime;

/** @ODM\Document(collection="Orders") */
class Order
{
    /** @ODM\Id(type="string") */
    private $orderId;

    /** @ODM\Field(type="string") */
    private $location = 0;

    /** @ODM\Field(type="int") */
    private $locationId;

    /** @ODM\Field(type="string") */
    private $server;

    /** @ODM\Field(type="string") */
    private $customer;

    /** @ODM\EmbedMany) */
    private $items = [];

    /** @ODM\Field(type="int") */
    private $discount;

    /** @ODM\Field(type="int") */
    private $total;

    /** @ODM\Field(type="date") */
    private $orderDate;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public function getOrderId(): string { return $this->orderId; }

    public function getLocation(): string { return $this->location; }
    public function setLocation(string $location): void { $this->location = $location; }

    public function getLocationId(): int { return $this->locationId; }
    public function setLocationId(int $locationId) { $this->locationId = $locationId; }

    public function getServer(): string { return $this->server; }
    public function setServer(string $server): void { $this->server = $server; }

    public function getCustomer(): string { return $this->customer; }
    public function setCustomer(string $customer): void { $this->customer = $customer; }

    public function getItems(): collection { return $this->items; }
    public function addItems($items) { 
        foreach($items as $item) {
            $orderItem = new OrderItem();
            foreach($item as $key => $value) {
                switch($key) {
                    case 'nr':
                        $orderItem->setId($value);
                    break;
                    case 'name':
                        $orderItem->setName($value);
                    break;
                    case 'cost':
                        $orderItem->setCost($value);
                    break;
                }
            }
            $this->items[] = $orderItem;
        } 
    }

    public function getDiscount(): ?int { return $this->discount; }
    public function setDiscount(int $discount): void { $this->discount = $discount; }

    public function getTotal(): int { return $this->total; }
    public function setTotal(int $total): void { $this->total = $total; }

    public function getOrderDate(): ?DateTime { return $this->orderDate; }
    public function setOrderDate(): void { 
        date_default_timezone_set('CET');
        $this->orderDate = new DateTime(); 
    }
}
