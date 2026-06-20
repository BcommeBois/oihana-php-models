<?php

namespace oihana\models\traits\alters;

use DI\Container;

use InvalidArgumentException;
use function oihana\core\callables\resolveCallable;

trait AlterMapPropertyTrait
{
    /**
     * Provides a mechanism to alter a property of a document using a custom "map" callback.
     *
     * This allows applying arbitrary transformations to a specific property of an array or object,
     * with access to the full document and optionally a DI container.
     *
     * The first element of `$params` must be a callable (or string resolvable to a callable via `resolveCallable`),
     * which will be invoked with the following signature:
     *
     * ```php
     * function map(array|object $document, ?Container $container, string $key, mixed $value, array $params = []): mixed
     * ```
     *
     * The callable should return the new value for the property. Any modification will set `$modified` to true.
     *
     * @param array|object $document The document (array or object) containing the property.
     * @param Container|null $container Optional DI container, for resolving services if needed.
     * @param string $key The key or property name being altered.
     * @param mixed $value The current value of the property.
     * @param array $params Additional parameters, where the first element must be the callable.
     * @param bool &$modified Output flag indicating whether the value was modified by the callable.
     *
     * @return mixed The altered value for the property.
     *
     * @throws InvalidArgumentException If the callable cannot be resolved.
     *
     * @example
     * ```php
     * $document = ['price' => 10 , 'vat' => '0.2' ];
     * $callback = fn( array &$document , $container , $key, $value, $params) => $value + ( $value * ( $document['vat'] ?? 0 ) ) ;
     *
     * $newValue = $this->alterMapProperty
     * (
     *      $document ,
     *      null ,
     *      'price',
     *      $document['price'],
     *      $callback ,
     *      $modified
     * );
     * // $newValue = 12
     * // $modified = true
     * ```
     */
    public function alterMapProperty
    (
        array|object &$document ,
        ?Container   $container ,
        string       $key       ,
        mixed        $value     ,
        array        $params    = [] ,
        bool         &$modified = false
    )
    : mixed
    {
        if ( count( $params ) === 0 )
        {
            return $value ;
        }

        $callback = array_shift( $params ) ;

        if ( is_string( $callback ) )
        {
            $callback = resolveCallable($callback);
        }

        if ( $callback !== null && is_callable( $callback ) )
        {
            $value    = $callback( $document , $container , $key , $value , $params ) ;
            $modified = true ;
        }

        return $value ;
    }
}