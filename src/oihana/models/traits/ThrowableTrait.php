<?php

namespace oihana\models\traits;

/**
 * Trait to provide a throwable behavior to models.
 *
 * This trait allows a class to specify whether its methods should throw exceptions or handle errors silently.
 *
 * @author Marc Alcaraz (eKameleon)
 * @package oihana\traits
 */
trait ThrowableTrait
{
    /**
     * Key used in initialization arrays to set throwable behavior.
     */
    public const string THROWABLE = 'throwable' ;

    /**
     * Determines if methods should throw exceptions.
     * @var bool
     */
    public bool $throwable = false ;

    /**
     * Initializes the throwable property from an array of options.
     *
     * @param array<string,mixed> $init Initialization options
     * @return static
     */
    public function initializeThrowable( array $init = [] ):static
    {
        $this->throwable = $init[ self::THROWABLE ] ?? $this->throwable ;
        return $this ;
    }
}