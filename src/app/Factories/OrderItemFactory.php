<?php

namespace App\Factories;

use App\Documents\OrderItem;
use Respect\Validation\Validator as v;
use SimpleJWT\JWT;

class OrderItemFactory
{
    /**
     * @var \ReflectionProperty
     */
    private \ReflectionProperty $itemUuidProperty;

    public function __construct()
    {
        $this->itemUuidProperty = new \ReflectionProperty(OrderItem::class, 'itemUUID');
    }

    /**
     * @param string $productId
     * @param int $number
     * @param string $name
     * @param string $cost
     * @param \DateTime|null $delivered
     * @param string|null $itemUuid
     * @param int $qty
     * @param string $totalPrice
     * @return OrderItem
     */
    public function create(
        string $productId,
        int $number,
        string $name,
        string $cost,
        int $qty,
        string $totalPrice,
        ?\DateTime $delivered = null,
        ?string $itemUuid = null
    ): OrderItem
    {
        $orderItem = new OrderItem();
        $orderItem->setProductId($productId);
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
        $orderItem->setQty($qty);
        $orderItem->setTotalPrice($totalPrice);
        return $orderItem;
    }

    /**
     * @param JWT $productToken
     * @return OrderItem
     */
    public function createFromArray(JWT $productToken): OrderItem
    {
        $data = $productToken->getClaim('product');
        $qty = $productToken->getClaim('qty');
        $totalPrice = $productToken->getClaim('totalPrice');

        $data = array_change_key_case($data, CASE_LOWER);
        v::arrayType()
            ->notEmpty()
            ->key('id', v::stringType()->notEmpty(), false)
            ->key('nr', v::numericVal(), false)
            ->key('name', v::stringType()->notEmpty()->length(4, 200), true)
            ->key('price', v::stringType()->numericVal(), true)
            ->key('delivered', v::stringType()->notEmpty()->dateTime(\DateTimeInterface::ISO8601), false)
            ->check($data);

        v::intType()->notEmpty()->check($qty);
        v::stringType()->notEmpty()->numericVal()->check($totalPrice);
        return $this->create(
            $data['id'],
//            $data['nr'],
            0,
            $data['name'],
            $data['price'],
            $qty,
            $totalPrice,
            (isset($data['delivered']) ?
                \DateTime::createFromFormat(\DateTimeInterface::ISO8601, $data['delivered']) :
                null
            ),
            (isset($data['itemuuid']) ? $data['itemuuid'] : null)
        );
    }
}