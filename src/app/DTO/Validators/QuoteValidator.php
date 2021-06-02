<?php

namespace App\DTO\Validators;

use Respect\Validation\Validator as v;

class QuoteValidator
{
    public function check(array $data): bool
    {
        v::arrayType()->notEmpty()
            ->key('product',
                v::arrayType()->notEmpty()
                    ->key('id', v::stringType()->notEmpty()->uuid(4), true)
                    ->key('name', v::stringType()->notEmpty(), true)
                    ->key('locationId', v::stringType()->notEmpty()->uuid(4), true)
                    ->key('price', v::stringType()->notEmpty()->numericVal(), true)
                    ->key('attributes', v::arrayType(), true)
                    ->key('description', v::oneOf(
                        v::stringType()->notEmpty(),
                        v::nullType()
                    ), true)
                    ->key('uniqueIdentifier', v::stringType()->notEmpty(), true)
            , true)
            ->key('qty', v::intType()->positive())
            ->key('totalPrice', v::numericVal())->check($data);
        return true;
    }
}