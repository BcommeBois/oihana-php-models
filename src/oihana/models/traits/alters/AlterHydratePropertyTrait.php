<?php

namespace oihana\models\traits\alters;

use ReflectionException;

use oihana\core\arrays\CleanFlag;
use oihana\reflect\traits\ReflectionTrait;

use org\schema\Thing;

use function oihana\core\normalize;

/**
 * Hydrates an array value into a typed object instance during property alteration.
 *
 * This alteration is declared with the `Alter::HYDRATE` type. It is used in property
 * transformation pipelines to turn a raw associative array (for instance the decoded
 * payload of a nested document) into a real object of a given class, so that downstream
 * code works with strongly-typed instances instead of loose arrays:
 *
 * ```php
 * Property::GEO => [ Alter::HYDRATE , GeoCoordinates::class ] ,
 * ```
 *
 * Two hydration strategies are used depending on the target class: classes extending
 * {@see Thing} are built directly through their constructor, while any other class is
 * populated through {@see ReflectionTrait::hydrate()} (reflection-based property mapping).
 * The input is normalized beforehand (configurable through {@see CleanFlag}), and an empty
 * normalized value yields `null`.
 *
 * The `$modified` flag is set to `true` whenever the resulting value differs from the
 * original input.
 *
 * @package oihana\models\traits\alters
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
trait AlterHydratePropertyTrait
{
    use ReflectionTrait ;

    /**
     * Hydrate a property value into a specific class instance using reflection.
     *
     * This method transforms a raw value (typically an array) into an object of the
     * specified class. If the input value is an array, it can be normalized and then
     * hydrated using either the {@see Thing} constructor or the {@see ReflectionTrait::hydrate()}
     * method depending on the class type.
     *
     * ### Behavior
     * - If `$value` is **not an array**, it is returned as-is.
     * - If `$value` is an **empty array**, the method returns `null` (by default).
     * - If `$schema` refers to a class extending {@see Thing}, the object is created
     *   directly via its constructor.
     * - Otherwise, hydration is performed via {@see ReflectionTrait::hydrate()}.
     * - The `$modified` flag is set to `true` if the resulting value differs from the input.
     *
     * ### Usage Example
     * ```php
     * Property::GEO => [ Alter::HYDRATE, GeoCoordinates::class ],
     * ```
     *
     * ### Custom Normalization
     * You can specify a custom normalization flag as a third element in the definition:
     * ```php
     * [ Alter::HYDRATE, GeoCoordinates::class, true , CleanFlag::ALL ]
     * ```
     *
     * By default, the value is normalized using:
     * {@see normalize()} with flags `CleanFlag::DEFAULT | CleanFlag::RETURN_NULL`.
     *
     * @param mixed $value
     *     The original value to hydrate. Can be a scalar, array, or object.
     * @param array $definition
     *     The alter definition, expected as:
     *     ```
     *     [
     *         0 => string|null $schema,   // Fully qualified class name to hydrate into
     *         1 => bool        $normalize // Whether to normalize the value before hydration (default true)
     *         2 => int         $flags     // Optional CleanFlag bitmask
     *     ]
     *     ```
     * @param bool &$modified
     *     Reference flag set to `true` if the resulting value differs from the original.
     *
     * @return mixed
     *     The hydrated value, possibly an instance of `$schema`, or `null` if empty.
     *
     * @throws ReflectionException
     *     If a class or property cannot be reflected (e.g. during hydration).
     *
     * @example
     * ```php
     * use oihana\models\traits\alters\AlterHydratePropertyTrait;
     * use org\schema\GeoCoordinates;
     *
     * class Place
     * {
     *     use AlterHydratePropertyTrait;
     * }
     *
     * $place    = new Place();
     * $modified = false;
     *
     * $geo = $place->alterHydrateProperty
     * (
     *     [ 'latitude' => 48.85 , 'longitude' => 2.35 ] ,
     *     [ GeoCoordinates::class ] ,
     *     $modified
     * );
     * // $geo instanceof GeoCoordinates === true
     * // $modified === true
     *
     * // A scalar is returned untouched
     * $raw = $place->alterHydrateProperty( 'not-an-array' , [ GeoCoordinates::class ] , $modified );
     * // $raw === 'not-an-array'
     * ```
     */
    public function alterHydrateProperty
    (
        mixed $value ,
        array $definition = [] ,
        bool  &$modified  = false
    )
    : mixed
    {
        if ( !is_array( $value ) )
        {
            return $value ;
        }

        $schema    = $definition[0] ?? null ;
        $normalize = $definition[1] ?? true ;
        $flags     = $definition[2] ?? ( CleanFlag::DEFAULT | CleanFlag::RETURN_NULL ) ;

        $newValue = $normalize ? normalize( $value , $flags ) : $value ;

        if( $newValue == null )
        {
            $modified = true ;
            return $newValue ;
        }

        if( is_string( $schema ) && class_exists( $schema ) )
        {
            if ( is_a( $schema , Thing::class , true ) )
            {
                $newValue = new $schema( $value ) ; // TODO remove it ? use only the hydrate method ?
            }
            else
            {
                $newValue = $this->hydrate( $value , $schema ) ;
            }
        }

        $modified = $value !== $newValue ;

        return $newValue ;
    }
}