<?php

namespace oihana\models\enums;

use oihana\models\enums\traits\ModelParamTrait;
use oihana\reflect\traits\ConstantsTrait;

/**
 * The enumeration of all the common model's parameters.
 */
class ModelParam
{
    use ConstantsTrait ,
        ModelParamTrait ;
}