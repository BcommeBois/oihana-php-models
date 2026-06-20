<?php

namespace oihana\models\traits;

use UnexpectedValueException;

/**
 * Provides a `property` reference.
 *
 * This trait allows a class to define a property reference that can be initialized
 * from an associative array (commonly used for hydration or model initialization).
 *
 * Usage:
 * ```php
 * class MyModel
 * {
 *     use PropertyTrait;
 * }
 *
 * $model = new MyModel();
 * $model->initializeProperty(['property' => 'name']);
 * echo $model->property; // outputs "name"
 * ```
 *
 * @package oihana\models\traits
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
trait PropertyTrait
{
    /**
     * The `property` reference.
     *
     * This property is typically a string identifying a key or field name
     * within the document or model context.
     *
     * @var string|null
     */
    public ?string $property = null ;

    /**
     * The 'property' key parameter used in initialization arrays.
     *
     * When passing an array to {@see initializeProperty()}, this key
     * will be checked to set the `$property` value.
     */
    public const string PROPERTY = 'property' ;

    /**
     * Asserts the existence of the 'property' key.
     *
     * @return static
     *
     * @throws UnexpectedValueException If the "property" key is not set.
     */
    public function assertProperty():static
    {
        if( !isset( $this->property ) )
        {
            throw new UnexpectedValueException( 'The "property" key is not set.' ) ;
        }

        return $this ;
    }

    /**
     * Initialize the `property` reference from an associative array.
     *
     * If the array contains the key {@see self::PROPERTY}, the value will be
     * assigned to `$property`. Otherwise, the property is set to `null`.
     *
     * @param array<string, mixed> $init Optional initialization array.
     * @return static Returns `$this` to allow method chaining.
     *
     * @example
     * ```php
     * $model = new MyModel();
     * $model->initializeProperty(['property' => 'foo']);
     * echo $model->property; // outputs "foo"
     *
     * // Method chaining
     * $model->initializeProperty(['property' => 'bar'])->someOtherMethod();
     * ```
     */
    public function initializeProperty( array $init = [] ):static
    {
        $this->property = $init[ self::PROPERTY ] ?? null ;
        return $this ;
    }
}