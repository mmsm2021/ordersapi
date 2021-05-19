<?php

namespace App\Documents;

use DateTime;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Ramsey\Uuid\Generator\RandomBytesGenerator;
use Ramsey\Uuid\Uuid;

/** 
 * @ODM\EmbeddedDocument
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
     * @ODM\Id(type="string")
     * @var string
     * @OA\Property()
     */
    private $itemUUID;

    /**
     * Order item number on menu
     * @ODM\Id(type="int")
     * @var int
     * @OA\Property()
     */
    private $nr;

    /**
     * Order item name
     * @ODM\Id(type="string")
     * @var string
     * @OA\Property()
     */
    private $name;

    /**
     * Order item prize
     * @ODM\Id(type="string")
     * @var string
     * @OA\Property()
     */
    private $cost;

    /**
     * Order item delivery status
     * @ODM\Field(type="date")
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
}
