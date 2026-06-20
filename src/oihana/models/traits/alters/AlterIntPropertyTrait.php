<?php

namespace oihana\models\traits\alters;

/**
 * Casts a value (or all elements in an array) to integer.
 *
 * This method is typically used in property alteration pipelines
 * to ensure that the given value is strictly an integer type.
 * If the value is already an integer, it is returned unchanged and
 * the {@see $modified} flag remains `false`.
 *
 * - If the value is an array, each element is individually cast to integer.
 * - If the value is not an integer, it is cast using {@see intval()}.
 *
 * The `$modified` flag will be set to `true` whenever at least one
 * value is cast to integer (i.e., when its original type was not already `int`).
 *
 * Example:
 * ```php
 * use oihana\traits\alters\AlterIntPropertyTrait;
 *
 * class Product {
 *     use AlterIntPropertyTrait;
 * }
 *
 * $product = new Product();
 * $modified = false;
 *
 * // Casting a single value
 * $id = $product->alterIntProperty("42", $modified);
 * // $id === 42  (int)
 * // $modified === true
 *
 * // Casting an array of values
 * $values = $product->alterIntProperty(["10", "20", "30"], $modified);
 * // $values === [10, 20, 30] (all int)
 * // $modified === true
 *
 * // Already an int
 * $count = $product->alterIntProperty(5, $modified);
 * // $count === 5 (int)
 * // $modified === false
 * ```
 *
 * @package oihana\models\traits\alters
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
trait AlterIntPropertyTrait
{
    /**
     * Casts a value (or all elements of an array) to integer.
     *
     * This alteration is typically used within property transformation
     * pipelines to ensure that the resulting value is strictly of type `int`.
     *
     * Behavior:
     * - If `$value` is an array, each element is individually cast using {@see intval()}.
     * - If `$value` is already an integer, it is returned unchanged and `$modified`
     *   remains `false`.
     * - If `$value` is not an integer, it is cast to integer and `$modified`
     *   becomes `true`.
     *
     * The `$modified` flag will be set to `true` whenever at least one casting
     * occurs (i.e., when the original value or any array element was not already
     * an integer).
     *
     * Example:
     * ```php
     * use oihana\traits\alters\AlterIntPropertyTrait;
     *
     * class Product {
     *     use AlterIntPropertyTrait;
     * }
     *
     * $product  = new Product();
     * $modified = false;
     *
     * // Casting a single value
     * $id = $product->alterIntProperty("42", $modified);
     * // $id === 42
     * // $modified === true
     *
     * // Casting an array of values
     * $values = $product->alterIntProperty(["10", "20", "30"], $modified);
     * // $values === [10, 20, 30]
     * // $modified === true
     *
     * // Already an int
     * $count = $product->alterIntProperty(5, $modified);
     * // $count === 5
     * // $modified === false
     * ```
     *
     * @param mixed $value    The input value, scalar or array.
     * @param bool  $modified Reference flag indicating if any cast occurred.
     * @return int|array      The cast integer or array of integers.
     */
    public function alterIntProperty( mixed $value , bool &$modified = false ): array|int
    {
        if ( is_array( $value ) )
        {
            $modified = true;
            return array_map('intval', $value);
        }

        if ( !is_int($value) )
        {
            $modified = true;
            return intval( $value ) ;
        }

        return $value ;
    }
}