<?php

namespace oihana\models\traits;

/**
 * Provides logic for managing bind parameters used in PDO statements.
 * Allows defining a default set of bind values and dynamically merging them
 * with runtime-provided parameters via the `prepareBindVars()` method.
 *
 * ### Usage example:
 *
 * ```php
 * class MyModel
 * {
 *     use BindsTrait;
 * }
 *
 * $model = new MyModel();
 * $model->binds = [ 'id' => 42 ];
 *
 * $params = $model->prepareBindVars
 * ([
 *     'binds' => [ 'status' => 'active' ]
 * ]);
 *
 * print_r($params);
 * // Output:
 * // [
 * //     'id'     => 42,
 * //     'status' => 'active'
 * // ]
 * ```
 *
 * @package oihana\models\traits
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
trait BindsTrait
{
    /**
     * The default bind values of the model.
     *
     * These values are always merged first by {@see prepareBindVars()} and may be overridden
     * by runtime parameters. May be `null` to indicate "no default binds".
     *
     * @var array|null
     */
    public ?array $binds = [] ;

    /**
     * The `binds` key used in initialization and runtime parameter arrays.
     */
    public const string BINDS = 'binds' ;

    /**
     * Initializes the `$binds` property from an initialization array.
     *
     * The value is read from the {@see self::BINDS} key when present; otherwise the current
     * value of `$binds` is kept.
     *
     * @param array $init Initialization options, typically the model constructor payload.
     *
     * @return static The current instance, for fluent chaining.
     */
    public function initializeBinds( array $init = [] ):static
    {
        $this->binds = $init[ self::BINDS ] ?? $this->binds ;
        return $this ;
    }

    /**
     * Prepares the binding parameters to inject into a PDO statement.
     *
     * The default `$binds` of the model are merged first, then overridden by the runtime values
     * found under the {@see self::BINDS} key of `$init`. Keys present in both win on the runtime side.
     *
     * @param array $init Runtime parameters; the values under the `binds` key are merged on top of the defaults.
     *
     * @return array The merged associative array of bind variables ready to bind on a prepared statement.
     *
     * @example
     * ```php
     * class MyModel
     * {
     *     use BindsTrait;
     * }
     *
     * $model = new MyModel();
     * $model->binds = [ 'id' => 42 ];
     *
     * $params = $model->prepareBindVars([ 'binds' => [ 'status' => 'active' ] ]);
     * // $params === [ 'id' => 42 , 'status' => 'active' ]
     * ```
     */
    public function prepareBindVars( array $init = [] ) :array
    {
        return [ ...( $this->binds ?? [] ) , ...( $init[ static::BINDS ] ?? [] ) ] ;
    }
}