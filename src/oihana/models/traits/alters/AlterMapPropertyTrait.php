<?php

namespace oihana\models\traits\alters;

use DI\Container;

use InvalidArgumentException;
use function oihana\core\callables\resolveCallable;

/**
 * Alters a property of a document through a context-aware "map" callback.
 *
 * This alteration is declared with the `Alter::MAP` type. Unlike the simpler
 * {@see AlterCallablePropertyTrait} (which only sees the property value), the map callback
 * receives the **whole document**, the optional DI container and the property key, so that
 * the new value can be computed from sibling properties or from injected services. It is the
 * tool of choice for derived/computed properties in a transformation pipeline:
 *
 * ```php
 * Property::PRICE_INCL_VAT => [ Alter::MAP , $computeTotalCallback ] ,
 * ```
 *
 * The first element of the parameters must be a callable (or a string resolvable to a
 * callable via {@see resolveCallable()}), invoked with the signature:
 *
 * ```php
 * function map( array|object $document , ?Container $container , string $key , mixed $value , array $params = [] , array $context = [] ): mixed
 * ```
 *
 * The optional `$context` is an opaque payload threaded from {@see AlterDocumentTrait::alter()}
 * (a skin, a locale, the originating request…); a callback that does not declare it keeps working.
 *
 * The callable returns the new property value; when it is applied, the `$modified` flag is
 * set to `true`. When no callable is provided the original value is returned unchanged.
 *
 * @package oihana\models\traits\alters
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
trait AlterMapPropertyTrait
{
    /**
     * Alters a property of a document through a context-aware "map" callback.
     *
     * The first element of `$params` is treated as the callback (a callable or a string
     * resolvable via {@see resolveCallable()}); the remaining elements are forwarded to it as
     * its `$params` argument. The callback receives the full document, the container, the key,
     * the current value, its `$params` and finally the opaque `$context`, and returns the new
     * value. If `$params` is empty or the callback cannot be resolved to a callable, the original
     * value is returned untouched.
     *
     * The callback signature is:
     * ```php
     * function ( array|object $document , ?Container $container , string $key , mixed $value , array $params , array $context ) : mixed
     * ```
     * A callback that does not declare the trailing `$context` parameter keeps working unchanged
     * (PHP discards the surplus argument), so the parameter is fully backward compatible.
     *
     * @param array|object   $document  The document (array or object) holding the property; passed
     *                                  by reference so the callback may read or adjust siblings.
     * @param Container|null $container Optional DI container, forwarded to the callback for
     *                                  resolving services when the computed value needs them.
     * @param string         $key       The key or property name being altered.
     * @param mixed          $value     The current value of the property.
     * @param array          $params    Parameters whose first element is the callback (callable or
     *                                  resolvable string); any remaining elements are forwarded to it.
     * @param bool          &$modified  Output flag set to `true` when the callback is applied.
     * @param array          $context   Optional opaque context forwarded as the callback's last argument
     *                                  (e.g. the originating request payload, a skin, a locale…).
     *
     * @return mixed The value returned by the callback, or the original value when no callback applies.
     *
     * @throws InvalidArgumentException If the callback is given as a string that cannot be resolved.
     *
     * @example
     * ```php
     * $document = [ 'price' => 10 , 'vat' => '0.2' ];
     * $modified = false;
     *
     * // The callback reads a sibling property ('vat') to compute the new value
     * $callback = fn( array|object $document , $container , string $key , mixed $value , array $params )
     *     => $value + ( $value * ( $document['vat'] ?? 0 ) ) ;
     *
     * $newValue = $this->alterMapProperty
     * (
     *      $document ,
     *      null ,
     *      'price' ,
     *      $document['price'] ,
     *      [ $callback ] ,   // the callback is the first element of $params
     *      $modified
     * );
     * // $newValue === 12
     * // $modified === true
     * ```
     */
    public function alterMapProperty
    (
        array|object &$document ,
        ?Container   $container ,
        string       $key       ,
        mixed        $value     ,
        array        $params    = [] ,
        bool         &$modified = false ,
        array        $context   = []
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
            $callback = resolveCallable( $callback );
        }

        if ( $callback !== null && is_callable( $callback ) )
        {
            $value    = $callback( $document , $container , $key , $value , $params , $context ) ;
            $modified = true ;
        }

        return $value ;
    }
}