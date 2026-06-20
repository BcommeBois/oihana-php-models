<?php

namespace oihana\models\traits\alters;

/**
 * Provides a standardized way to cast a property value to `float` within the AlterDocument system.
 *
 * This alteration is typically declared as:
 *
 * ```php
 * 'price' => Alter::FLOAT
 * ```
 *
 * or inside a chained definition:
 *
 * ```php
 * 'prices' => [ Alter::ARRAY, Alter::FLOAT ]
 * ```
 *
 * Behavior details:
 *
 * - If the input is already a `float`, it is returned unchanged and `$modified`
 *   remains `false`.
 * - If the input is an array, **each element** is converted using `floatval()`.
 * - If the input is any scalar (string, int, bool), it is cast using `floatval()`.
 * - Non-scalar, non-array values should generally not occur, but if they do,
 *   PHP's `floatval()` semantics apply.
 *
 * This alteration is useful for ensuring numeric consistency when working
 * with user input, JSON data, APIs, DTO hydration, or internal normalization.
 *
 * @package oihana\models\traits\alters
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
trait AlterFloatPropertyTrait
{
    /**
     * Casts a value (or every element of an array) to float.
     *
     * This method ensures that the resulting value is always of type `float`
     * (or an array of floats). It also sets `$modified` to `true` when any
     * transformation occurs.
     *
     * Rules:
     * - If `$value` is already a float → returned as is, not marked modified.
     * - If `$value` is an array → each element converted via `floatval()`.
     * - Otherwise → `$value` is cast to float.
     *
     * @param mixed $value
     *     The value to convert. Can be scalar or array.
     *
     * @param bool &$modified
     *     Output flag set to `true` if a cast was performed.
     *
     * @return float|array
     *     The float-cast value or an array of float-cast values.
     *
     * @example
     * ```php
     * $value = '12.5';
     * $new   = $this->alterFloatProperty($value, $modified);
     *
     * // $new      = 12.5
     * // $modified = true
     * ```
     *
     * @example Array usage
     * ```php
     * $value = ['1.2', '3.4', 5];
     * $new   = $this->alterFloatProperty($value, $modified);
     *
     * // $new      = [1.2, 3.4, 5.0]
     * // $modified = true
     * ```
     */
    public function alterFloatProperty( mixed $value , bool &$modified = false ): array|float
    {
        if( is_float( $value ) )
        {
            return $value ;
        }
        $modified = true ;
        if( is_array( $value ) )
        {
            return array_map( 'floatval' , $value ) ;
        }
        return floatval( $value ) ;
    }
}