<?php

namespace App\Documents;

use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Types\Type;
use DateTime;
use OpenApi\Annotations as OA;

/**
 * Representation of an Order Document in MongoDB
 *
 * @author  NINLeviathan
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
     * @var string
     * @OA\Property()
     */
    private ?string $orderId;

    /**
     * Location Name
     * @var string
     * @OA\Property()
     */
    private ?string $location = '0';

    /**
     * Location ID
     * @var int
     * @OA\Property()
     */
    private ?int $locationId;

    /**
     * Name or ID of waitress or waiter
     * @var string
     * @OA\Property()
     */
    private ?string $server;

    /**
     * Name or ID of customer
     * @var string
     * @OA\Property()
     */
    private ?string $customer;

    /**
     * Array of items purchased
     * @var array
     * @OA\Property(
     *  @OA\Items(
     *   ref="#/components/schemas/OrderItem"
     *  )
     * )
     */
    private ?array $items = [];

    /**
     * Order status
     * @var int
     * OA\Property
     */
    private ?int $orderStatus;

    /**
     * Discount percenntage given on order
     * @var string
     * @OA\Property()
     */
    private ?string $discount;

    /**
     * Amount total payed for order
     * @var string
     * @OA\Property()
     */
    private ?string $total;

    /**
     * Date the order was placed
     * @var object
     * @OA\Property()
     */
    private ?DateTime $orderDate = null;

    /** Constructor */
    public function __construct()
    {
        $this->items = [];
        $this->orderDate = new DateTime();
    }

    public function toArray(): array
    {
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

        $orderArray = [
            'orderId' => $this->getOrderID(),
            'location'  => $this->getLocation(),
            'locationId'  => $this->getLocationId(),
            'server'  => $this->getServer(),
            'customer'  => $this->getCustomer(),
            'items'  => $itemsArray,
            'orderStatus' => $this->getOrderStatus(),
            'discount'  => $this->getDiscount(),
            'total'  => $this->getTotal(),
            // 'orderDate'  => $this->getOrderDate()
        ];
        if ($this->getOrderDate() instanceof DateTime) {
            $orderArray['orderDate'] = $this->getOrderDate()->format(\DateTimeInterface::ISO8601);
        } else {
            $orderArray['orderDate'] = null;
        }
        return $orderArray;
    }

    /** Order ID getter */
    public function getOrderId(): ?string
    {
        return $this->orderId;
    }

    /** Location getter and setter */
    public function getLocation(): ?string
    {
        return $this->location;
    }
    public function setLocation(string $location): void
    {
        $this->location = $location;
    }

    /** Location ID getter and setter */
    public function getLocationId(): ?int
    {
        return $this->locationId;
    }
    public function setLocationId(int $locationId)
    {
        $this->locationId = $locationId;
    }

    /** Waitress/Waiter ID/name getter and setter */
    public function getServer(): ?string
    {
        return $this->server;
    }
    public function setServer(string $server): void
    {
        $this->server = $server;
    }

    /** Customer name/ID getter and setter */
    public function getCustomer(): ?string
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

    /** Returns order item objects in an array */
    public function getItems(): ?array
    {
        $sendBack = [];
        foreach ($this->items as $item) {
            $sendBack[] = $item;
        }
        return $sendBack;
    }

    /** Returns order items as arrays in array */
    public function getItemsArray(): ?array
    {
        $sendBack = [];
        foreach ($this->items as $item) {
            $sendBack[] = $item->toArray();
        }
        return $sendBack;
    }

    /** Adds an order item to the order */
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

    /** Clears the list of items */
    public function clearItems(): void
    {
        $this->items = [];
    }

    /** Order status getter and setter */
    public function getOrderStatus(): ?int
    {
        return $this->orderStatus;
    }
    public function setOrderStatus($status): void
    {
        $this->orderStatus = $status != null ? $status : 1;
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
    public function getTotal(): ?int
    {
        return $this->total;
    }
    public function setTotal(int $total): void
    {
        $this->total = $total;
    }

    /** Order Date getter */
    public function getOrderDate(): ?DateTime
    {
        return $this->orderDate;
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

    /** Mapping of document */
    public static function loadMetaData(ClassMetadata $metadata)
    {
        $metadata->setDatabase('FranDine');
        $metadata->setCollection('Orders');
        $metadata->mapField([
            'name' => 'orderId',
            'id' => true,
            'type' => Type::STRING,
            'nullable' => false
        ]);
        $metadata->mapField([
            'name' => 'location',
            'id' => false,
            'type' => Type::STRING,
            'nullable' => false
        ]);
        $metadata->mapField([
            'name' => 'locationId',
            'id' => false,
            'type' => Type::STRING,
            'nullable' => false
        ]);
        $metadata->mapField([
            'name' => 'server',
            'id' => false,
            'type' => Type::STRING,
            'nullable' => false
        ]);
        $metadata->mapField([
            'name' => 'customer',
            'id' => false,
            'type' => Type::STRING,
            'nullable' => false
        ]);
        $metadata->mapManyEmbedded([
            'name' => 'items',
            'targetDocument' => OrderItem::class
        ]);
        $metadata->mapField([
            'name' => 'orderStatus',
            'id' => false,
            'type' => Type::INT,
            'nullable' => false
        ]);
        $metadata->mapField([
            'name' => 'discount',
            'id' => false,
            'type' => Type::FLOAT,
            'nullable' => false
        ]);
        $metadata->mapField([
            'name' => 'total',
            'id' => false,
            'type' => Type::FLOAT,
            'nullable' => false
        ]);
        $metadata->mapField([
            'name' => 'orderDate',
            'id' => false,
            'type' => Type::DATE,
            'nullable' => false
        ]);
    }
}
