<?php

namespace App\DTO\Validators;

use Respect\Validation\Validator as v;

/**
 * Validator for Order Json
 */
class OrderValidator
{
    /**
     * @OA\Schema(
     *     schema="OrderCreatedObject",
     *     type="object",
     *     description="Object containing the id of the order created.",
     *     @OA\Property(
     *         property="orderId",
     *         type="number"
     *     )
     * )
     * @OA\Schema(
     *     schema="OrderItemCreateObject",
     *     type="object",
     *     description="Order Item creation JSON object",
     *     @OA\Property(
     *         property="nr",
     *         description="The item menu number",
     *         type="number"
     *     ),
     *     @OA\Property(
     *         property="name",
     *         description="Name of the item",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="cost",
     *         description="Prize of the item",
     *         type="string"
     *     )
     * )
     * @OA\Schema(
     *     schema="OrderCreateObject",
     *     type="object",
     *     description="Order creation JSON object",
     *     @OA\Property(
     *         property="location",
     *         description="Order- location/restaurant name",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="locationId",
     *         description="UUID of Order- location/restaurant",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="items",
     *         description="Array OrderItems",
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/OrderItemCreateObject")
     *     ),
     *     @OA\Property(
     *         property="discount",
     *         description="The discount percentage applied on the order",
     *         type="number"
     *     ),
     *     @OA\Property(
     *         property="total",
     *         description="The total amount payed for the order",
     *         type="number"
     *     )
     * )
     * @param array $order The order to validate as array
     */
    public function validate(array $order)
    {
        v::arrayType()->notEmpty()
            ->key('location', v::stringType()->notEmpty(), true)
            ->key('locationId', v::stringType()->notEmpty()->uuid(4), true)
            //->key('server', v::stringType()->notEmpty(), true)
            //->key('customer', v::stringType()->notEmpty(), true)
            ->key('items', v::ArrayType()->notEmpty(), true)
            ->key('discount', v::intType()->notEmpty(), true)
            ->key('total', v::intType()->notEmpty(), true)
            ->check($order);
    }
}
