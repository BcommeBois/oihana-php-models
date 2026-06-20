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
     * The default alter key reference.
     * @var string
     * @see AlterUrlPropertyTrait
     */
    public string $alterKey = Schema::ID ;

    /**
     * Initialize the 'alters' property.
     * @param array $init
     * @return static
     */
    public function initializeAlterKey( array $init = [] ):static
    {
        $this->alterKey = $init[ ModelParam::ALTER_KEY ] ?? Schema::ID ;
        return $this ;
    }
}