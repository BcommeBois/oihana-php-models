<?php

namespace oihana\models\traits\alters;

use InvalidArgumentException;
use function oihana\core\callables\resolveCallable;

/**
 * Provides support for altering a property value using a user-defined callable.
 *
 * This trait is used by the AlterDocument processing system to allow flexible,
 * callback-driven transformations on document fields. An alteration rule using
 * this trait typically looks like:
 *
 * ```php
 * 'fieldName' => [ Alter::CALLABLE, $callable, ...$extraParams ]
 * ```
 *
 * The callable may be:
 * - an anonymous function,
 * - a standard PHP function name,
 * - a static method `ClassName::method`,
 * - a service callable resolved through a DI container,
 * - or any other valid `callable`.
 *
 * If the callable is provided as a string (e.g. `"myFunction"` or `"App\\Service@method"`),
 * it will be resolved using `resolveCallable()`.
 *
 * The callable receives:
 *
 * ```php
 * function (mixed $value, mixed ...$additionalParams): mixed
 * ```
 *
 * The returned value replaces the original property value, and `$modified` is set to `true`
 * if a callable was successfully applied.
 *
 * @package oihana\models\traits\alters
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
trait AlterCallablePropertyTrait
{
    /**
     * Alters a value by invoking a user-defined callable.
     *
     * This method allows transforming a property value using any callable
     * (closure, function name, static method, service callback, etc.).
     * It is typically used when an alteration rule is defined as:
     *
     * ```php
     * [
     *     Alter::CALLABLE,
     *     $callable,
     *     ...$additionalParams
     * ]
     * ```
     *
     * The first element of `$definition` **must** be a callable or a string
     * resolvable to a callable via `resolveCallable()`.
     *
     * The callable will be invoked with the following signature:
     *
     * ```php
     * function (mixed $value, mixed ...$additionalParams): mixed
     * ```
     *
     * Any additional elements from `$definition` will be forwarded as extra arguments.
     * If the callable returns a different value, `$modified` will be set to `true`.
     *
     * @param mixed $value
     *     The current value of the property to be altered.
     *
     * @param array $definition
     *     The alteration definition:
     *     - index `0`: the callable (closure, function name, or resolvable string)
     *     - index `1+`: optional parameters passed to the callable
     *
     * @param bool &$modified
     *     Output flag set to `true` if the callable altered the value.
     *
     * @return mixed
     *     The altered value returned by the callable, or the original value if
     *     no callable was provided or callable resolution failed.
     *
     * @throws InvalidArgumentException
     *     If the callable is a string but cannot be resolved.
     *
     * @example
     * ```php
     * $definition = [
     *     fn($value, $factor) => $value * $factor,
     *     2
     * ];
     *
     * $value = 10;
     * $value = $this->alterCallableProperty($value, $definition, $modified);
     *
     * // $value    = 20
     * // $modified = true
     * ```
     */
    public function alterCallableProperty
    (
        mixed $value ,
        array $definition = [] ,
        bool &$modified   = false
    )
    : mixed
    {
        $callable = array_shift($definition);

        if ( is_string( $callable ) )
        {
            $callable = resolveCallable( $callable ) ;
        }

        if( $callable !== null && is_callable( $callable ) )
        {
            $value = $callable( $value , ...$definition );
            $modified = true;
        }

        return $value;
    }
}