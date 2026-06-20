<?php

namespace oihana\models\traits\alters;

use oihana\enums\Char;

/**
 * Removes empty or null entries from an array value.
 *
 * This alteration is typically declared with the `Alter::CLEAN` type and is used in
 * property transformation pipelines to discard "blank" elements (empty strings and
 * unset/null values) before the array is stored or further processed. It is often
 * chained after {@see AlterArrayPropertyTrait} to clean up an array that was just
 * built from a raw string:
 *
 * ```php
 * Property::CATEGORY => [ Alter::ARRAY , Alter::CLEAN ] ,
 * ```
 *
 * Behavior details:
 * - If the value is an array, elements equal to {@see Char::EMPTY} (the empty string)
 *   or not set are filtered out and the `$modified` flag is set to `true`.
 * - If the value is not an array, it is returned untouched and `$modified` is left as-is.
 *
 * Note that the original keys are preserved by {@see array_filter()} (the array is not
 * re-indexed).
 *
 * @package oihana\models\traits\alters
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
trait AlterArrayCleanPropertyTrait
{
    /**
     * Removes empty-string and null elements from an array value.
     *
     * Only arrays are processed; any other value is returned unchanged. When an array
     * is given, the `$modified` flag is set to `true` even if no element was actually
     * removed (the filtering pass is always considered an alteration).
     *
     * @param mixed $value    The value to clean. When it is an array, empty-string and
     *                        unset elements are removed; otherwise it is returned as-is.
     * @param bool  $modified Reference flag set to `true` when an array was processed.
     *
     * @return array|float The cleaned array (keys preserved), or the original value when
     *                     it was not an array.
     *
     * Example:
     * ```php
     * use oihana\models\traits\alters\AlterArrayCleanPropertyTrait;
     *
     * class Product
     * {
     *     use AlterArrayCleanPropertyTrait;
     * }
     *
     * $product  = new Product();
     * $modified = false;
     *
     * $tags = $product->alterArrayCleanProperty( [ 'php' , '' , 'models' , null ] , $modified );
     * // $tags     === [ 0 => 'php' , 2 => 'models' ]  (keys preserved)
     * // $modified === true
     *
     * $scalar = $product->alterArrayCleanProperty( 'unchanged' , $modified );
     * // $scalar === 'unchanged'
     * ```
     */
    public function alterArrayCleanProperty( mixed $value , bool &$modified = false ): array|float
    {
        if( is_array( $value ) )
        {
            $value = array_filter( $value , fn( $item ) => $item != Char::EMPTY && isset( $item )  ) ;
            $modified = true ;
        }
        return $value ;
    }
}