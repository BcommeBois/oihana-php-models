<?php

namespace oihana\models\enums;

use oihana\models\enums\traits\ModelParamTrait;
use oihana\reflect\traits\ConstantsTrait;

/**
 * Enumeration of all the common model parameters.
 *
 * Each constant is the canonical **key** recognized inside the `$init` options
 * array that model operations ({@see \oihana\models\interfaces\DocumentsModel})
 * and model factories accept — for instance `key`, `document`, `binds`,
 * `conditions`, `sort`, `cache` or `ttl`. Referencing these constants instead
 * of raw strings keeps configuration arrays and DI definitions in sync and free
 * of *magic strings*.
 *
 * The constants themselves live in {@see ModelParamTrait}; this class simply
 * exposes them together with the {@see ConstantsTrait} reflection helpers
 * (`ModelParam::getAll()`, `ModelParam::includes()`, …).
 *
 * Example:
 * ```php
 * use oihana\models\enums\ModelParam;
 *
 * $init =
 * [
 *     ModelParam::KEY        => 'users/42' ,
 *     ModelParam::CONDITIONS => 'doc.active == true' ,
 *     ModelParam::SORT       => 'name' ,
 *     ModelParam::CACHE      => true ,
 *     ModelParam::TTL        => 3600 ,
 * ];
 * ```
 *
 * @package oihana\models\enums
 * @author  Marc Alcaraz
 * @since   1.0.0
 *
 * @see ModelParamTrait For the full list of parameter constants.
 */
class ModelParam
{
    use ConstantsTrait ,
        ModelParamTrait ;
}