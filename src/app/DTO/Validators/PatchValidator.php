<?php

namespace App\DTO\Validators;

use Respect\Validation\Validator as v;

/**
 * Validator for Patch document
 */
class PatchValidator
{
    /**
     * @param array $order The order to validate as array
     */
    public function validate(array $order)
    {
        v::arrayType()->anyOf()
            ->key('location', v::stringType()->notEmpty(), false)
            ->key('locationId', v::intType()->notEmpty(), false)
            ->key('server', v::stringType()->notEmpty(), false)
            ->key('customer', v::stringType()->notEmpty(), false)
            ->key('items', v::ArrayType()->notEmpty(), false)
            ->key('discount', v::intType()->notEmpty(), false)
            ->key('total', v::intType()->notEmpty(), false)
            ->check($order);
    }
}
