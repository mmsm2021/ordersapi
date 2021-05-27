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

    /** Returns items as array */
    public function toArray(): array
    {
        $itemArray = [
            'itemUUID' => $this->getUUID(),
            'nr'  => $this->getNr(),
            'name'  => $this->getName(),
            'cost'  => $this->getCost(),
        ];
        if ($this->getDelivered() instanceof DateTime) {
            $itemArray['delivered'] = $this->getDelivered()->format(\DateTimeInterface::ISO8601);
        } else {
            $itemArray['delivered'] = null;
        }
        return $itemArray;
    }

    /** Mapping of document */
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
