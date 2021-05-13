<?php

namespace App\DataModels;

class OrderItem
{
    /** Order item ID */
    public $id;
    /** Order item name */
    public $name;
    /** Order item prize */
    public $cost;

    /** Constructor, assigns relevant info to all properties */
    public function __construct($item)
    {
        $this->id = $item->getId();
        $this->name = $item->getName();
        $this->cost = $item->getCost();
    }
}
