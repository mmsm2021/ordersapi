<?php

namespace Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\EmbeddedDocument */
class OrderItem
{
    /** Order item ID */
    /** @ODM\Field(type="int") */
    private $id;

    /** Order item name */
    /** @ODM\Field(type="string") */
    private $name;

    /** Order item prize */
    /** @ODM\Field(type="int") */
    private $cost;

    /** Order item getter and setter */
    public function getId(): int { return $this->id; }
    public function setId(int $id): void { $this->id = $id; }

    /** Order item getter and setter */
    public function getName(): ?string { return $this->name; }
    public function setName(string $name): void { $this->name = $name; }

    /** Order item prize getter and setter */
    public function getCost(): int { return $this->cost; }
    public function setCost(int $cost): void { $this->cost = $cost; }
}
