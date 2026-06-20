<?php

namespace oihana\models\enums;

use oihana\models\enums\traits\ModelParamTrait;
use oihana\reflect\traits\ConstantsTrait;

/**
 * The enumeration of all the common model's parameters.
 *
 * @package oihana\models\enums
 * @author  Marc Alcaraz
 * @since   1.0.0
 */
class ModelParam
{
    use ConstantsTrait ,
        ModelParamTrait ;
}