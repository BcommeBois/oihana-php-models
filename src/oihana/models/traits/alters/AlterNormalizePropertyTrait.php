<?php

namespace oihana\models\traits\alters;

use oihana\core\arrays\CleanFlag ;

use function oihana\core\normalize ;

/**
 * Provides a method to normalize a property value according to configurable cleaning flags.
 *
 * This trait is typically used in alteration pipelines to ensure that values
 * are cleaned, trimmed, and standardized before being stored or further processed.
 *
 * The normalization behavior is controlled via {@see CleanFlag} constants:
 * - By default, `CleanFlag::DEFAULT | CleanFlag::RETURN_NULL` is applied.
 * - Custom flags can be provided as the first element of the `$definition` array.
 *
 * Normalization handles:
 * - Arrays recursively, removing empty values, nulls, or falsy values according to flags.
 * - Strings by trimming and optionally converting empty strings to null.
 * - Scalars are generally returned as-is unless `CleanFlag::FALSY` is used.
 * - Objects are returned as-is unless empty `stdClass` and `CleanFlag::RETURN_NULL` is set.
 *
 * The `$modified` flag is set to `true` if the resulting value differs from the original.
 *
 * @package oihana\models\traits\alters
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 *
 * @see normalize  For the underlying normalization logic.
 * @see CleanFlag  For the enumeration of cleaning modes.
 *
 * @example
 * ```php
 * use oihana\models\traits\alters\AlterNormalizePropertyTrait;
 * use oihana\core\arrays\CleanFlag;
 *
 * class Product {
 *     use AlterNormalizePropertyTrait;
 * }
 *
 * $product = new Product();
 * $modified = false;
 *
 * // Default normalization
 * $value = $product->alterNormalizeProperty(['', ' foo ', null], [], $modified);
 * // Result: ['foo']
 * // $modified === true
 *
 * // Custom flags
 * $value = $product->alterNormalizeProperty('   ', [CleanFlag::TRIM | CleanFlag::EMPTY], $modified);
 * // Result: null
 * // $modified === true
 *
 * // Scalars remain unchanged if not affected by flags
 * $value = $product->alterNormalizeProperty(42, [], $modified);
 * // Result: 42
 * // $modified === false
 * ```
 *
 * @package oihana\models\traits\alters
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
trait AlterNormalizePropertyTrait
{
    /**
     * Normalize a document property using configurable flags.
     *
     * The normalization can be customized via the `$definition` array:
     * - If empty or no flags provided, uses CleanFlag::DEFAULT | CleanFlag::RETURN_NULL
     * - If a flags value is provided at index 0, uses that instead
     *
     * @param mixed $value The value to normalize
     * @param array $definition Optional flags array: [CleanFlag value, ...other params]
     * @param bool $modified Reference flag indicating if the value was modified
     *
     * @return mixed The normalized value, or null if cleaned away
     *
     * @example
     * ```php
     * // Use default flags
     * $this->alterNormalizeProperty( $value );
     * // Uses: CleanFlag::DEFAULT | CleanFlag::RETURN_NULL
     *
     * // Use custom flags
     * $this->alterNormalizeProperty($value, [ CleanFlag::NULLS | CleanFlag::EMPTY ] );
     *
     * // Only remove nulls
     * $this->alterNormalizeProperty($value, [CleanFlag::NULLS]);
     * ```
     */
    public function alterNormalizeProperty
    (
        mixed $value ,
        array $definition = [] ,
        bool  &$modified  = false
    )
    : mixed
    {
        $flags = $definition[0] ?? ( CleanFlag::DEFAULT | CleanFlag::RETURN_NULL ) ;

        $newValue = normalize( $value , $flags ) ;

        if ( $newValue !== $value )
        {
            $modified = true ;
        }

        return $newValue ;
    }
}