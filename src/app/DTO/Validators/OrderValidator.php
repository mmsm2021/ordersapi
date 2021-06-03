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
     *     schema="OrderCreateObject",
     *     type="object",
     *     description="Order creation JSON object",
     *     @OA\Property(
     *         property="location",
     *         description="Order- location/restaurant name",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="items",
     *         description="Array OrderItems",
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/quoteToken")
     *     ),
     *     @OA\Property(
     *         property="discount",
     *         description="The discount percentage applied on the order",
     *         type="number"
     *     )
     * )
     * @param array $order The order to validate as array
     */
    public function validate(array $order)
    {
        v::arrayType()->notEmpty()
            ->key('location', v::stringType()->notEmpty(), true)
            ->key('items', v::ArrayType()->notEmpty()->each(v::stringType()->notEmpty()), true)
            ->key('discount', v::intType()->notEmpty(), false)
            ->check($order);
    }
}
