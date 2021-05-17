<?php

namespace App\Documents;

use DateTime;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Ramsey\Uuid\Generator\RandomBytesGenerator;
use Ramsey\Uuid\Uuid;

/** @ODM\EmbeddedDocument */
class OrderItem
{
    /** Item UUID */
    /** @ODM\Id(type="string") */
    private $itemUUID;

    /** Product Nr */
    /** @ODM\Field(type="int") */
    private $nr;

    /** Order item name */
    /** @ODM\Field(type="string") */
    private $name;

    /** Order item prize */
    /** @ODM\Field(type="int") */
    private $cost;

    /** Delivery status for the order, null if not delivered, DateTime object if delivered */
    /** @ODM\Field(type="date") */
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
