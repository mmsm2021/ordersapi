<?php

namespace App\Documents;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use DateTime;

/**
 * Representation of an Order Document in MongoDB
 *
 * @author  NINLeviathan
 */
/** @ODM\Document(collection="Orders") */
class Order
{
    /** Order ID */
    /** @ODM\Id(type="string") */
    private $orderId;

    /** Location Name */
    /** @ODM\Field(type="string") */
    private $location = 0;

    /** Location ID */
    /** @ODM\Field(type="int") */
    private $locationId;

    /** Name or ID of waitress or waiter */
    /** @ODM\Field(type="string") */
    private $server;

    /** Name or ID of customer */
    /** @ODM\Field(type="string") */
    private $customer;

    /** Array of items purchased */
    /** @ODM\EmbedMany) */
    private $items = [];

    /** Discount percentage given on order */
    /** @ODM\Field(type="int") */
    private $discount;

    /** Amount total payed for order */
    /** @ODM\Field(type="int") */
    private $total;

    /** Date of order */
    /** @ODM\Field(type="date") */
    private $orderDate;

    /** Constructor */
    public function __construct()
    {
        $this->items = [];
    }

    /** Order ID getter */
    public function getOrderId(): string
    {
        return $this->orderId;
    }

    /** Location getter and setter */
    public function getLocation(): string
    {
        return $this->location;
    }
    public function setLocation(string $location): void
    {
        $this->location = $location;
    }

    /** Location ID getter and setter */
    public function getLocationId(): int
    {
        return $this->locationId;
    }
    public function setLocationId(int $locationId)
    {
        $this->locationId = $locationId;
    }

    /** Waitress/Waiter ID/name getter and setter */
    public function getServer(): string
    {
        return $this->server;
    }
    public function setServer(string $server): void
    {
        $this->server = $server;
    }

    /** Customer name/ID getter and setter */
    public function getCustomer(): string
    {
        return $this->customer;
    }
    public function setCustomer(string $customer): void
    {
        $this->customer = $customer;
    }

    /** Order Item getter and setter */
    public function getItem($itemUUID): ?OrderItem
    {
        foreach ($this->getItems() as $item) {
            if ($itemUUID == $item->getUUID) {
                return $item;
            }
        }
        return null;
    }
    public function setItem($item): void
    {
        foreach ($this->getItems() as $oldItem) {
            if ($item->getUUID() != null && $oldItem->getUUID() != null && $item->getUUID() == $oldItem->getUUID()) {
                $this->removeItem($oldItem->getUUID());
            }
        }
        $this->items[] = $item;
    }

    /** Removes order item from array of order items */
    private function removeItem($itemUUID): void
    {
        $remainingItems = [];
        foreach ($this->getItems() as $item) {
            if ($itemUUID != $item->getUUID()) {
                $remainingItems[] = $item;
            }
        }
        $this->items = $remainingItems;
    }

    /** Order Items array getter and setter */
    public function getItems(): array
    {
        $sendBack = [];
        foreach ($this->items as $item) {
            $sendBack[] = $item;
        }
        return $sendBack;
    }
    public function addItems($items)
    {
        foreach ($items as $item) {
            $orderItem = new OrderItem();
            foreach ($item as $key => $value) {
                switch ($key) {
                    case 'itemUUID':
                        $orderItem->setUUID($value);
                        break;
                    case 'nr':
                        $orderItem->setNr($value);
                        break;
                    case 'name':
                        $orderItem->setName($value);
                        break;
                    case 'cost':
                        $orderItem->setCost($value);
                        break;
                    case 'delivered':
                        $orderItem->setDeliveredStatus($value);
                        break;
                }
            }
            $this->items[] = $orderItem;
        }
    }
    public function getPersistentItems()
    {
        return $this->items;
    }
    public function clearItems(): void
    {
        $this->items = [];
    }

    /** Discount getter and setter */
    public function getDiscount(): ?int
    {
        return $this->discount;
    }
    public function setDiscount(int $discount): void
    {
        $this->discount = $discount;
    }

    /** Order total getter and setter */
    public function getTotal(): int
    {
        return $this->total;
    }
    public function setTotal(int $total): void
    {
        $this->total = $total;
    }

    /** Order Date getter and setter */
    public function getOrderDate(): ?DateTime
    {
        return $this->orderDate;
    }
    public function setOrderDate(): void
    {
        $this->orderDate = new DateTime();
    }
}
