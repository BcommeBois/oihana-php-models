<?php

namespace oihana\models\traits\alters;

use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use oihana\enums\Char;
use oihana\models\enums\Alter;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;

/**
 * Turns a value into an array and optionally alters each of its elements.
 *
 * This alteration is declared with the `Alter::ARRAY` type. It is the entry point of the
 * "array" branch of a property-transformation pipeline: a semicolon-separated string is
 * first exploded into an array, then a chain of nested alter definitions can be applied to
 * every element in turn. This makes it possible to declare a single rule that both builds
 * the array and normalizes its content:
 *
 * ```php
 * Property::CATEGORY => [ Alter::ARRAY , Alter::CLEAN , Alter::JSON_PARSE ] ,
 * ```
 *
 * The example above splits the `category` string into an array, removes null/empty
 * elements, then JSON-decodes every remaining element.
 *
 * The trait composes the element-level alters it can delegate to ({@see AlterCallablePropertyTrait},
 * {@see AlterFloatPropertyTrait}, {@see AlterIntPropertyTrait}) and dispatches the others
 * ({@see Alter::CLEAN}, {@see Alter::GET}, {@see Alter::HYDRATE}, {@see Alter::NORMALIZE},
 * {@see Alter::NOT}, {@see Alter::JSON_PARSE}) through {@see alterArrayElements()}.
 *
 * The `$modified` flag is always set to `true` by {@see alterArrayProperty()} because the
 * value is unconditionally coerced to an array.
 *
 * @package oihana\models\traits\alters
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
trait AlterArrayPropertyTrait
{
    use AlterCallablePropertyTrait ,
        AlterFloatPropertyTrait ,
        AlterIntPropertyTrait ;

    /**
     * Transforms a value into an array and applies an optional chain of element alterations.
     *
     * A non-empty string is split on the semicolon (`;`) separator. The resulting array (or
     * the value itself when it is already an array) is then passed to {@see alterArrayElements()}
     * so that each chained alter definition in `$options` is applied to every element. Any value
     * that is neither a string nor an array becomes an empty array.
     *
     * @param mixed      $value     The value to convert. A semicolon-separated string is
     *                              exploded; an array is used as-is; anything else yields `[]`.
     * @param array      $options   An ordered list of element-level alter definitions to apply
     *                              to each item (e.g. `[ Alter::CLEAN , Alter::INT ]`). Each entry
     *                              is either an `Alter::*` type or an array `[ type , ...params ]`.
     * @param ?Container $container An optional DI container, forwarded to element alters that
     *                              need service resolution (e.g. `Alter::GET`).
     * @param bool       $modified  Reference flag; always set to `true` because the value is
     *                              coerced to an array.
     *
     * @return array The resulting array after splitting and element-level alterations.
     *
     * @throws ContainerExceptionInterface If an error occurs while retrieving an entry from the dependency-injection container.
     * @throws DependencyException         If the dependency cannot be resolved by the container.
     * @throws NotFoundException           If no entry is found for the given identifier in the container.
     * @throws NotFoundExceptionInterface  If no entry is found for the requested identifier in the container.
     * @throws ReflectionException         If a class or property cannot be reflected (e.g. during hydration).
     *
     * Example:
     * ```php
     * use oihana\models\enums\Alter;
     * use oihana\models\traits\alters\AlterArrayPropertyTrait;
     *
     * class Product
     * {
     *     use AlterArrayPropertyTrait;
     * }
     *
     * $product  = new Product();
     * $modified = false;
     *
     * // Split a string and cast each element to int
     * $ids = $product->alterArrayProperty( '10;20;30' , [ Alter::INT ] , null , $modified );
     * // $ids      === [ 10 , 20 , 30 ]
     * // $modified === true
     *
     * // Already an array, cleaned of empty entries
     * $tags = $product->alterArrayProperty( [ 'php' , '' , 'models' ] , [ Alter::CLEAN ] , null , $modified );
     * // $tags === [ 0 => 'php' , 2 => 'models' ]
     * ```
     */
    public function alterArrayProperty
    (
        mixed      $value ,
        array      $options   = [] ,
        ?Container $container = null ,
        bool       &$modified = false
    )
    :array
    {
        if( is_string( $value ) && $value != Char::EMPTY )
        {
            $value = explode( Char::SEMI_COLON , $value ) ;
        }
        $modified = true ;
        return is_array( $value ) ? $this->alterArrayElements( $value , $options , $container ) : [] ;
    }

    /**
     * Applies a chain of element-level alterations to every item of an array.
     *
     * Each entry of `$options` is either a bare `Alter::*` type or an array shaped as
     * `[ type , ...params ]` where the extra parameters are forwarded to the corresponding
     * element alter. The supported types are `CALL`, `CLEAN`, `FLOAT`, `GET`, `HYDRATE`,
     * `NORMALIZE`, `NOT`, `INT` and `JSON_PARSE`; any unknown type leaves the array
     * untouched. The alterations are applied sequentially, each operating on the result of
     * the previous one. If either the array or the option list is empty, the array is
     * returned unchanged.
     *
     * @param array      $array     The array whose elements must be altered.
     * @param array      $options   An ordered list of alter definitions, each a bare
     *                              `Alter::*` type or an array `[ type , ...params ]`.
     * @param ?Container $container An optional DI container, forwarded to alters that resolve
     *                              services (notably `Alter::GET`).
     *
     * @return array The array after all element-level alterations have been applied.
     *
     * @throws ContainerExceptionInterface If an error occurs while retrieving an entry from the dependency-injection container.
     * @throws DependencyException         If the dependency cannot be resolved by the container.
     * @throws NotFoundException           If no entry is found for the given identifier in the container.
     * @throws NotFoundExceptionInterface  If no entry is found for the requested identifier in the container.
     * @throws ReflectionException         If a class or property cannot be reflected (e.g. during hydration).
     *
     * Example:
     * ```php
     * use oihana\models\enums\Alter;
     *
     * // Trim/empty-clean then JSON-decode each element
     * $result = $this->alterArrayElements
     * (
     *     [ '{"a":1}' , '' , '{"b":2}' ] ,
     *     [ Alter::CLEAN , Alter::JSON_PARSE ]
     * );
     * // $result === [ (object) [ 'a' => 1 ] , (object) [ 'b' => 2 ] ]
     * ```
     */
    public function alterArrayElements
    (
        array      $array     ,
        array      $options   = [] ,
        ?Container $container = null  ,
    ):array
    {
        if( count( $array )  > 0 && count( $options ) > 0 )
        {
            foreach( $options as $option )
            {
                if( is_array( $option ) )
                {
                    $type       = current( $option ) ;
                    $definition = array_slice( $option, 1 ) ;
                }
                else
                {
                    $type       = $option ;
                    $definition = [] ;
                }

                $array = match ( $type )
                {
                    Alter::CALL       => array_map( fn( $item ) => $this->alterCallableProperty( $item , $definition ) , $array ) ,
                    Alter::CLEAN      => array_filter( $array , fn( $item ) => $item != Char::EMPTY && isset($item) ) ,
                    Alter::FLOAT      => $this->alterFloatProperty( $array ) ,
                    Alter::GET        => array_map( fn( $item ) => $this->alterGetDocument( $item , $definition , $container ) , $array ),
                    Alter::HYDRATE    => array_map( fn( $item ) => $this->alterHydrateProperty( $item , $definition ) , $array ),
                    Alter::NORMALIZE  => $this->alterNormalizeProperty( $array , $definition ),
                    Alter::NOT        => $this->alterNotProperty( $array ) ,
                    Alter::INT        => $this->alterIntProperty( $array ) ,
                    Alter::JSON_PARSE => array_map( fn($item) => json_decode( $item ) , $array ) ,
                    default           => $array ,
                };
            }
        }
        return $array ;
    }
}