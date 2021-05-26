<?php

namespace App\Factories;

use App\Documents\OrderItem;
use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Validator as v;

class OrderItemFactory
{
    private \ReflectionProperty $itemUuidProperty;

    public function __construct()
    {
        $this->itemUuidProperty = new \ReflectionProperty(OrderItem::class, 'itemUUID');
    }

    /**
     * @param int $number
     * @param string $name
     * @param string $cost
     * @param \DateTime|null $delivered
     * @param string|null $itemUuid
     * @return OrderItem
     */
    public function create(
        int $number,
        string $name,
        string $cost,
        ?\DateTime $delivered = null,
        ?string $itemUuid = null
    ) : OrderItem {
        $orderItem = new OrderItem();
        $orderItem->setNr($number);
        $orderItem->setName($name);
        $orderItem->setCost($cost);
        if ($delivered instanceof \DateTime) {
            $orderItem->setDelivered($delivered);
        }
        if ($itemUuid !== null) {
            $this->itemUuidProperty->setAccessible(true);
            $this->itemUuidProperty->setValue($orderItem, $itemUuid);
            $this->itemUuidProperty->setAccessible(false);
        }
        return $orderItem;
    }

    /**
     * @param array $data
     * @return OrderItem
     * @throws ValidationException
     */
    public function createFromArray(array $data): OrderItem
    {
        $data = array_change_key_case($data, CASE_LOWER);
        v::arrayType()
            ->notEmpty()
            ->key('itemuuid', v::stringType()->notEmpty(), false)
            ->key('nr', v::numericVal(), true)
            ->key('name', v::stringType()->notEmpty()->length(4,200), true)
            ->key('cost', v::stringType()->numericVal(), true)
            ->key('delivered', v::stringType()->notEmpty()->dateTime(\DateTimeInterface::ISO8601), false)
            ->check($data);
        return $this->create(
            (int)$data['nr'],
            $data['name'],
            $data['cost'],
            (isset($data['delivered']) ?
                \DateTime::createFromFormat(\DateTimeInterface::ISO8601, $data['delivered']) :
                null
            ),
            (isset($data['itemuuid']) ? $data['itemuuid'] : null)
        );
    }
}