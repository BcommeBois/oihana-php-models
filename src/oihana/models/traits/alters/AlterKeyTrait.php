<?php

namespace oihana\models\traits\alters;

use oihana\models\enums\ModelParam;
use org\schema\constants\Schema;

/**
 * Provides support for defining and initializing the default "alter key".
 *
 * The alter key is used by alteration traits (e.g., {@see AlterUrlPropertyTrait})
 * to resolve which property or contextual key should be used when applying
 * transformations in a model's alteration pipeline.
 *
 * By default, the alter key is initialized to {@see Schema::ID}, but it can
 * be overridden at construction time or during initialization using the
 * {@see ModelParam::ALTER_KEY} parameter.
 *
 * @package oihana\models\traits\alters
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
trait AlterKeyTrait
{
    /**
     * The default property key used when an alteration needs to read a value from the document.
     *
     * Consumed for instance by {@see AlterUrlPropertyTrait} to pick which property provides the
     * final URL segment when none is given explicitly. Defaults to {@see Schema::ID}.
     *
     * @var string
     * @see AlterUrlPropertyTrait
     */
    public string $alterKey = Schema::ID ;

    /**
     * Initializes the {@see $alterKey} property from an initialization array.
     *
     * Reads {@see ModelParam::ALTER_KEY} from `$init`; when absent, the key falls back to
     * {@see Schema::ID}. Returns the current instance for fluent chaining.
     *
     * @param array $init Initialization options; the {@see ModelParam::ALTER_KEY} entry, when
     *                    present, overrides the default alter key.
     *
     * @return static The current instance, for method chaining.
     *
     * @example
     * ```php
     * use oihana\models\enums\ModelParam;
     *
     * $this->initializeAlterKey( [ ModelParam::ALTER_KEY => 'slug' ] );
     * // $this->alterKey === 'slug'
     *
     * $this->initializeAlterKey();
     * // $this->alterKey === Schema::ID  (default)
     * ```
     */
    public function initializeAlterKey( array $init = [] ):static
    {
        $this->alterKey = $init[ ModelParam::ALTER_KEY ] ?? Schema::ID ;
        return $this ;
    }
}