<?php

namespace oihana\models\traits;

/**
 * Provides a configurable "throwable" behavior to models.
 *
 * Mix this trait in when a model should let callers choose between fail-fast and silent error
 * handling. Methods of the host class read the public `$throwable` flag to decide whether to raise
 * an exception or degrade gracefully (e.g. return `null`/`false`). The flag can be set directly or
 * hydrated from an initialization array via {@see initializeThrowable()}.
 *
 * @package oihana\models\traits
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
trait ThrowableTrait
{
    /**
     * The `throwable` key used in initialization arrays.
     */
    public const string THROWABLE = 'throwable' ;

    /**
     * Whether the host methods should throw exceptions instead of failing silently.
     *
     * @var bool
     */
    public bool $throwable = false ;

    /**
     * Initializes the `$throwable` flag from an initialization array.
     *
     * Read from the {@see self::THROWABLE} key when present; otherwise the current value is kept.
     *
     * @param array<string,mixed> $init Initialization options (key: `throwable`).
     *
     * @return static The current instance, for fluent chaining.
     */
    public function initializeThrowable( array $init = [] ):static
    {
        $this->throwable = $init[ self::THROWABLE ] ?? $this->throwable ;
        return $this ;
    }
}