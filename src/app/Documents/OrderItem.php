<?php

namespace App\Documents;

use DateTime;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Types\Type;
use Ramsey\Uuid\Uuid;

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
     * @var string
     * @OA\Property()
     */
    private $itemUUID;

    /**
     * Order item number on menu
     * @var int
     * @OA\Property()
     */
    private $nr;

    /**
     * Order item name
     * @var string
     * @OA\Property()
     */
    private $name;

    /**
     * Order item prize
     * @var string
     * @OA\Property()
     */
    private $cost;

    /**
     * Order item delivery status
     * @var object
     * @OA\Property()
     */
    private ?DateTime $delivered = null;

    /** Order item Unique identifier getter and setter */
    public function getUUID(): ?string
    {
        return $this->itemUUID;
    }

    public function setUUID($itemUUID): void
    {
        if (null == $itemUUID) {
            $this->itemUUID = Uuid::uuid4();
        } else {
            $this->itemUUID = $itemUUID;
        }
    }

    /** Order item getter and setter */
    public function getNr(): int
    {
        return $this->nr;
    }
    public function setNr(int $nr): void
    {
        $this->nr = $nr;
    }

    /** Order item getter and setter */
    public function getName(): ?string
    {
        return $this->name;
    }
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /** Order item prize getter and setter */
    public function getCost(): int
    {
        return $this->cost;
    }
    public function setCost(int $cost): void
    {
        $this->cost = $cost;
    }

    /** Order item delivery status getter and setter */
    public function getDeliveredStatus(): ?DateTime
    {
        return $this->delivered;
    }
    public function setDeliveredStatus(DateTime $dateTime): void
    {
        $this->delivered = $dateTime;
    }
    public function setDeliveredTrue($deliveryTime): void
    {
        $this->delivered = $deliveryTime;
    }

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
    }
}
