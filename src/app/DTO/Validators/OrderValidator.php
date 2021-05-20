<?php

namespace App\DTO\Validators;

use Respect\Validation\Validator as v;

/**
 * Validator for Order Json
 */
class OrderValidator
{
    /**
     * @param array $order The order to validate as array
     */
    public function validate(array $order)
    {
        v::arrayType()->notEmpty()
            ->key('location', v::stringType()->notEmpty(), true)
            ->key('locationId', v::intType()->notEmpty(), true)
            ->key('server', v::stringType()->notEmpty(), true)
            #->key('customer', v::stringType()->notEmpty(), true)
            ->key('items', v::ArrayType()->notEmpty(), true)
            ->key('discount', v::intType()->notEmpty(), true)
            ->key('total', v::intType()->notEmpty(), true)
            ->check($order);
    }
}
