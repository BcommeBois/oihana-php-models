<?php

namespace oihana\models\traits;

use Closure;
use InvalidArgumentException;
use oihana\models\enums\ModelParam;
use org\schema\helpers\SchemaResolver;

/**
 * Provides a flexible `schema` reference used to hydrate resources.
 *
 * The schema can be a fixed class/type string, a {@see Closure}, or a {@see SchemaResolver}. When
 * it is callable (closure or resolver) it can decide the schema dynamically from a target value,
 * which lets a single model resolve the right Schema.org type per document. Mix this trait in when
 * a model needs to expose such a configurable schema and resolve it on demand.
 *
 * @package oihana\models\traits
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
trait SchemaTrait
{
    /**
     * The schema used to hydrate the resources.
     *
     * A fixed type string, a {@see Closure} taking the target and returning a string, or a
     * {@see SchemaResolver}. `null` means no schema is configured.
     *
     * @var null|string|Closure|SchemaResolver
     */
    public null|string|Closure|SchemaResolver $schema = null ;

    /**
     * Indicates whether a schema is configured.
     *
     * @return bool `true` if `$schema` is not `null` (string, closure or resolver), `false` otherwise.
     */
    public function hasSchema(): bool
    {
        return $this->schema !== null;
    }

    /**
     * Resolves the schema to its final string value.
     *
     * If `$schema` is a {@see SchemaResolver} or any callable, it is invoked with `$target` and its
     * return value is used. If it is a plain string it is returned as-is. Returns `null` when no
     * schema is configured.
     *
     * @param mixed $target Optional target passed to the resolver/closure to compute the schema dynamically.
     *
     * @return string|null The resolved schema string, or `null` when none is configured.
     *
     * @example
     * ```php
     * $model->schema = fn( $doc ) => $doc['type'] === 'book' ? 'Book' : 'Thing';
     * echo $model->getSchema( [ 'type' => 'book' ] ); // "Book"
     * ```
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
     * Initializes the `$schema` property from an initialization array.
     *
     * The value is read from the {@see ModelParam::SCHEMA} key. It must be `null`, a string, a
     * {@see Closure} or a {@see SchemaResolver}; any other type is rejected.
     *
     * @param array $init Initialization options (key: `ModelParam::SCHEMA`).
     *
     * @return static The current instance, for fluent chaining.
     *
     * @throws InvalidArgumentException If the value is neither a string, a Closure, nor a SchemaResolver.
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