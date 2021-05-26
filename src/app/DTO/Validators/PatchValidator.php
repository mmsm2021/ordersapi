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
        $order = array_change_key_case($order, CASE_LOWER);
        v::arrayType()->notEmpty()->anyOf(
            v::key('location', v::stringType()->notEmpty()),
            v::key('locationid', v::stringType()->uuid(4)),
            v::key('server', v::stringType()->notEmpty()),
            v::key('customer', v::stringType()->notEmpty()),
            v::key('items', v::arrayType()->notEmpty()->each(v::arrayType()->notEmpty())),
            v::key('discount', v::intType()->min(0)->max(100)),
            //v::key('total', v::stringType()->numericVal())
        )->check($order);
    }
}
