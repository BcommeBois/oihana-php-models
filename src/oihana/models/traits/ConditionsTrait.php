<?php

namespace oihana\models\traits ;

use oihana\models\enums\ModelParam;

/**
 * Provides support for handling model conditions.
 *
 * This trait defines a container for query or model conditions and
 * exposes an initialization helper to hydrate them from a parameter
 * array (typically coming from a model configuration or options object).
 *
 * The conditions structure is intentionally left flexible and may vary
 * depending on the model implementation (SQL, NoSQL, in-memory, etc.).
 *
 * @package oihana\models\traits
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
trait ConditionsTrait
{
    /**
     * Internal list of model conditions.
     *
     * This array usually represents filtering rules applied to the model, such as
     * WHERE (SQL) or FILTER (AQL) clauses, logical constraints, or driver-specific condition definitions.
     *
     * @var array
     */
    public array $conditions = [] ;

    /**
     * Initializes the model conditions from an initialization array.
     *
     * The conditions are extracted using the {@see ModelParam::CONDITIONS}
     * key if present. If the key is missing, the conditions list is reset
     * to an empty array.
     *
     * This method is typically called during model construction or
     * configuration hydration.
     *
     * @param array<string,mixed> $init Initialization parameters
     *
     * @return static Returns the current instance for fluent usage
     */
    public function initializeConditions( array $init = [] ) :static
    {
        $this->conditions = $init[ ModelParam::CONDITIONS ] ?? [] ;
        return $this;
    }
}