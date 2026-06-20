<?php

namespace oihana\models\traits;

use Closure;
use InvalidArgumentException;
use oihana\models\enums\ModelParam;
use org\schema\helpers\SchemaResolver;

/**
 * Provides methods for initialize a 'schema' property.
 *
 * @package oihana\traits
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
trait SchemaTrait
{
    /**
     * The internal schema to use to hydrate the resources.
     * @var null|string|Closure|SchemaResolver
     */
    public null|string|Closure|SchemaResolver $schema = null ;

    /**
     * Check if schema is defined (either as string or callable).
     * @return bool
     */
    public function hasSchema(): bool
    {
        return $this->schema !== null;
    }

    /**
     * Get the resolved schema value.
     *
     * @param mixed $target Optional target to pass if the schema is invokable (like SchemaResolver)
     *
     * @return string|null
     */
    public function getSchema( mixed $target = null ): ?string
    {
        if ( $this->schema instanceof SchemaResolver )
        {
            /** @var SchemaResolver $resolver */
            $resolver = $this->schema ;
            return $resolver( $target ) ;
        }

        if ( is_callable( $this->schema ) )
        {
            return ( $this->schema )( $target ) ;
        }

        return $this->schema ;
    }

    /**
     * Initialize the 'schema' property.
     * @param array $init
     * @return static
     */
    public function initializeSchema( array $init = [] ):static
    {
        $value = $init[ ModelParam::SCHEMA ] ?? null ;

        if
        (
            $value !== null
            && !is_string($value)
            && !( $value instanceof Closure )
            && !($value instanceof SchemaResolver)
        )
        {
            throw new InvalidArgumentException('The `schema` property must be a string or Closure, or SchemaResolver.') ;
        }

        $this->schema = $value ;

        return $this ;
    }
}