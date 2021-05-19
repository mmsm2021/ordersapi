<?php

namespace App\Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use DateTime;

/**
 * Representation of an Order Document in MongoDB
 *
 * @author  NINLeviathan
 * @ODM\Document(collection="Orders")
 *  
 * @OA\Schema(
 *   schema="Order",
 *   type="object",
 *   description="Order object",
 *   
 * )
 */

class Order
{
    /**
     * Order ID
     * @ODM\Id(type="string")
     * @var string
     * @OA\Property()
     */
    private $orderId;

    /**
     * Location Name
     * @ODM\Field(type="string")
     * @var string
     * @OA\Property()
     */
    private $location = 0;

    /**
     * Location ID
     * @ODM\Field(type="int")
     * @var int
     * @OA\Property()
     */
    private $locationId;

    /**
     * Name or ID of waitress or waiter
     * @ODM\Field(type="string")
     * @var string
     * @OA\Property()
     */
    private $server;

    /**
     * Name or ID of customer
     * @ODM\Field(type="string")
     * @var string
     * @OA\Property()
     */
    private $customer;

    /**
     * Array of items purchased
     * @ODM\Field(type="EmbedMany")
     * @var array
     * @OA\Property(
     *  @OA\Items(
     *   ref="#/components/schemas/OrderItem"
     *  )
     * )
     */
    private $items = [];

    /**
     * Discount percenntage given on order
     * @ODM\Field(type="string")
     * @var string
     * @OA\Property()
     */
    private $discount;

    /**
     * Amount total payed for order
     * @ODM\Field(type="string")
     * @var string
     * @OA\Property()
     */
    private $total;

    /**
     * Date the order was placed
     * @ODM\Field(type="date")
     * @var object
     * @OA\Property()
     */
    private DateTime $orderDate;

    /** Constructor */
    public function __construct()
    {
        $this->items = [];
    }

    public function toArray()
    {
        $orderArray = [];
        $itemsArray = [];

        foreach ($this->items as $item) {
            $itemsArray[] = [
                'itemUUID' => $item->getUUID(),
                'nr' => $item->getNr(),
                'name' => $item->getName(),
                'cost' => $item->getCost(),
                'delivered' => $item->getDeliveredStatus()
            ];
        }

        $orderArray[] = [
            'orderId' => $this->getOrderID(),
            'location'  => $this->getLocation(),
            'locationId'  => $this->getLocationId(),
            'server'  => $this->getServer(),
            'customer'  => $this->getCustomer(),
            'items'  => $itemsArray,
            'discount'  => $this->getDiscount(),
            'total'  => $this->getTotal(),
            'orderDate'  => $this->getOrderDate()
        ];
        return $orderArray;
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
}
