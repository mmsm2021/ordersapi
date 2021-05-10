<?php

namespace Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\EmbeddedDocument */
class OrderItem
{
    /** @ODM\Field(type="int") */
    private $id;

    /** @ODM\Field(type="string") */
    private $name;

    /** @ODM\Field(type="int") */
    private $cost;

    public function getId(): int { return $this->id; }
    public function setId(int $id): void { $this->id = $id; }

    public function getName(): ?string { return $this->name; }
    public function setName(string $name): void { $this->name = $name; }

    public function getCost(): int { return $this->cost; }
    public function setCost(int $cost): void { $this->cost = $cost; }
}
