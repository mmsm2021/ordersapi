<?php

namespace App\Documents;

use DateTime;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Types\Type;

/** 
 * @OA\Schema(
 *   schema="OrderItem",
 *   type="object",
 *   description="Order item object",
 * )
 */
class OrderItem
{
    /**
     * Item unique ID on order
     * @var string|null
     * @OA\Property()
     */
    private ?string $itemUUID = null;

    /**
     * Order item number on menu
     * @var int
     * @OA\Property()
     */
    private int $nr;

    /**
     * Order item product id
     * @var string
     * @OA\Property()
     */
    private string $productId;

    /**
     * Order item name
     * @var string
     * @OA\Property()
     */
    private string $name;

    /**
     * Order item prize
     * @var string
     * @OA\Property()
     */
    private string $cost;

    /**
     * Order item delivery status
     * @var DateTime|null
     * @OA\Property()
     */
    private ?DateTime $delivered = null;

    /**
     * Order item quantity
     * @var int
     * @OA\Property
     */
    private int $qty;

    /**
     * Order item(s) total cost
     * @var string
     * @OA\Property
     */
    private string $totalPrice;

    /**
     * Order item Unique identifier getter
     * @return string|null
     */
    public function getUUID(): ?string
    {
        return $this->itemUUID;
    }

    /**
     * Order item getter
     * @return int
     */
    public function getNr(): int
    {
        return $this->nr;
    }

    /**
     * @param int $nr
     */
    public function setNr(int $nr): void
    {
        $this->nr = $nr;
    }

    /**
     * Order item product number getter and setter
     * @return string
     */
    public function getProductId(): string
    {
        return $this->productId;
    }

    /**
     * @param $productId
     */
    public function setProductId($productId): void
    {
        $this->productId = $productId;
    }

    /**
     * Order item getter and setter
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Order item prize getter and setter
     * @return string
     */
    public function getCost(): string
    {
        return $this->cost;
    }

    /**
     * @param string $cost
     */
    public function setCost(string $cost): void
    {
        $this->cost = $cost;
    }

    /**
     * Order item delivery status getter and setter
     * @return DateTime|null
     */
    public function getDelivered(): ?DateTime
    {
        return $this->delivered;
    }

    /**
     * @param DateTime $dateTime
     */
    public function setDelivered(DateTime $dateTime): void
    {
        $this->delivered = $dateTime;
    }

    /**
     * Order item quantity getter and setter
     * @return int
     */
    public function getQty(): int
    {
        return $this->qty;
    }

    /**
     * @param $qty
     */
    public function setQty($qty): void
    {
        $this->qty = $qty;
    }

    /**
     * Order item(s) total price getter and setter
     * @return string
     */
    public function getTotalPrice(): string
    {
        return $this->totalPrice;
    }

    /**
     * @param $totalPrice
     */
    public function setTotalPrice($totalPrice):void
    {
        $this->totalPrice = $totalPrice;
    }

    /** Returns items as array */
    public function toArray(): array
    {
        $itemArray = [
            'itemUUID' => $this->getUUID(),
            'product' => $this->getProductId(),
            'nr'  => $this->getNr(),
            'name'  => $this->getName(),
            'cost'  => $this->getCost(),
            'qty' => $this->getQty(),
            'totalPrice' => $this->getTotalPrice(),
        ];
        if ($this->getDelivered() instanceof DateTime) {
            $itemArray['delivered'] = $this->getDelivered()->format(\DateTimeInterface::ISO8601);
        } else {
            $itemArray['delivered'] = null;
        }
        return $itemArray;
    }

    /** Mapping of OrderItem */
    public static function loadMetaData(ClassMetadata $metadata)
    {
        $metadata->isEmbeddedDocument = true;
        $metadata->mapField([
            'name' => 'itemUUID',
            'id' => true,
            'type' => Type::STRING,
            'nullable' => false
        ]);
        $metadata->mapField([
            'name' => 'nr',
            'id' => false,
            'type' => Type::STRING,
            'nullable' => false
        ]);
        $metadata->mapField([
            'name' => 'productId',
            'id' => false,
            'type' => Type::STRING,
            'nullable' => false
        ]);
        $metadata->mapField([
            'name' => 'name',
            'id' => false,
            'type' => Type::STRING,
            'nullable' => false
        ]);
        $metadata->mapField([
            'name' => 'cost',
            'id' => false,
            'type' => Type::FLOAT,
            'nullable' => false
        ]);
        $metadata->mapField([
            'name' => 'delivered',
            'id' => false,
            'type' => Type::DATE,
            'nullable' => true
        ]);
        $metadata->mapField([
            'name' => 'qty',
            'id' => false,
            'type' => Type::INT,
            'nullable' => true
        ]);
        $metadata->mapField([
            'name' => 'totalPrice',
            'id' => false,
            'type' => Type::STRING,
            'nullable' => true
        ]);
    }

}
