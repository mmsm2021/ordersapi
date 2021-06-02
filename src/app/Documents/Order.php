<?php

namespace App\Documents;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Types\Type;
use DateTime;

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
     * @var string|null
     * @OA\Property()
     */
    private ?string $orderId;

    /**
     * Location Name
     * @var string
     * @OA\Property()
     */
    private string $location;

    /**
     * Location ID
     * @var string
     * @OA\Property()
     */
    private string $locationId;

    /**
     * Name or ID of waitress or waiter
     * @var string|null
     * @OA\Property()
     */
    private ?string $server = null;

    /**
     * Name or ID of customer
     * @var string|null
     * @OA\Property()
     */
    private ?string $customer = null;

    /**
     * Array of OrderItem
     * @var Collection
     * @OA\Property(
     *  @OA\Items(
     *   ref="#/components/schemas/OrderItem"
     *  )
     * )
     */
    private Collection $items;

    /**
     * Order status
     * @var int
     * OA\Property
     */
    private int $orderStatus = 1;

    /**
     * Discount percenntage given on order
     * @var int
     * @OA\Property()
     */
    private int $discount = 0;

    /**
     * Amount total payed for order
     * @var string
     * @OA\Property()
     */
    private string $total;

    /**
     * Date the order was placed
     * @var DateTime|null
     * @OA\Property()
     */
    private ?DateTime $orderDate;

    /** Constructor */
    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->orderDate = new DateTime();
    }

    public function toArray(): array
    {
        $orderArray = [
            'orderId' => $this->getOrderID(),
            'location'  => $this->getLocation(),
            'locationId'  => $this->getLocationId(),
            'server'  => $this->getServer(),
            'customer'  => $this->getCustomer(),
            'items'  => $this->getItemsArray(),
            'orderStatus' => $this->getOrderStatus(),
            'discount'  => $this->getDiscount(),
            'total'  => $this->getTotal(),
        ];
        if ($this->getOrderDate() instanceof DateTime) {
            $orderArray['orderDate'] = $this->getOrderDate()->format(\DateTimeInterface::ISO8601);
        } else {
            $orderArray['orderDate'] = null;
        }
        return $orderArray;
    }

    /**
     * @return string|null
     */
    public function getOrderId(): ?string
    {
        return $this->orderId;
    }

    /**
     * @return string|null
     */
    public function getLocation(): ?string
    {
        return $this->location;
    }

    /**
     * @param string $location
     */
    public function setLocation(string $location): void
    {
        $this->location = $location;
    }

    /**
     * @return string
     */
    public function getLocationId(): string
    {
        return $this->locationId;
    }

    /**
     * @param string $locationId
     */
    public function setLocationId(string $locationId)
    {
        $this->locationId = $locationId;
    }

    /**
     * @return string|null
     */
    public function getServer(): ?string
    {
        return $this->server;
    }

    /**
     * @param string $server
     */
    public function setServer(string $server): void
    {
        $this->server = $server;
    }

    /**
     * @return string|null
     */
    public function getCustomer(): ?string
    {
        return $this->customer;
    }

    /**
     * @param string $customer
     */
    public function setCustomer(string $customer): void
    {
        $this->customer = $customer;
    }

    /**
     * @param $itemUUID
     * @return OrderItem|null
     */
    public function getItem($itemUUID): ?OrderItem
    {
        foreach ($this->getItems() as $item) {
            /** @var OrderItem $item */
            if ($itemUUID == $item->getUUID()) {
                return $item;
            }
        }
        return null;
    }

    /**
     * @param OrderItem $item
     */
    public function setItem(OrderItem $item): void
    {
        $currentItem = ($item->getUUID() !== null ? $this->getItem($item->getUUID()) : null);
        if ($currentItem !== null) {
            $this->getItems()->removeElement($currentItem);
        }
        $this->addItem($item);
    }

    /** Returns order items as arrays in array */
    public function getItemsArray(): ?array
    {
        return array_map(function (OrderItem $item){
            return $item->toArray();
        }, $this->getItems()->toArray());
    }

    /** Returns order item objects in an array */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(OrderItem $item): void
    {
        $this->items->add($item);
    }

    /**
     * @return int
     */
    public function getOrderStatus(): int
    {
        return $this->orderStatus;
    }

    /**
     * @param int $status
     */
    public function setOrderStatus(int $status): void
    {
        $this->orderStatus = $status;
    }

    /**
     * @return int
     */
    public function getDiscount(): int
    {
        return $this->discount;
    }

    /**
     * @param int $discount
     */
    public function setDiscount(int $discount): void
    {
        if ($discount < 0) {
            $discount = 0;
        }
        if ($discount > 100) {
            $discount = 100;
        }
        $this->discount = $discount;
    }

    /**
     * @return string
     */
    public function getTotal(): string
    {
        return $this->total;
    }

    /**
     * @param string $total
     */
    public function setTotal(string $total): void
    {
        $this->total = $total;
    }

    /** Order Date getter */
    public function getOrderDate(): ?DateTime
    {
        return $this->orderDate;
    }

    /** Mapping of Order */
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
