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
     * The default bind values definition of the model.
     * @var array|null
     */
    public ?array $binds = [] ;

    /**
     * The 'binds' parameter constant.
     */
    public const string BINDS = 'binds' ;

    /**
     * Initialize the 'binds' property.
     * @param array $init
     * @return static
     */
    public function initializeBinds( array $init = [] ):static
    {
        $this->binds = $init[ self::BINDS ] ?? $this->binds ;
        return $this ;
    }

    /**
     * Prepares the binding parameters to inject in a PDO statement.
     * @param array $init The binding parameters to push in the default binds associative array definition.
     * @return array
     */
    public function prepareBindVars( array $init = [] ) :array
    {
        return [ ...( $this->binds ?? [] ) , ...( $init[ static::BINDS ] ?? [] ) ] ;
    }
}