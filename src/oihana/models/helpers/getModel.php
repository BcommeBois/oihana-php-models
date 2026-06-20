<?php

namespace oihana\models\helpers ;

use oihana\models\enums\ModelParam;
use oihana\models\Model;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Resolves a model instance from a PSR-11 container or returns a default.
 *
 * This function attempts to retrieve a `Model` instance based on the provided
 * definition. The definition can be:
 * - A `Model` instance (returned directly),
 * - An array containing a `ModelParam::MODEL` key,
 * - A string identifier for a model in a PSR-11 container.
 *
 * @param array|string|Model|null $definition The model definition, which can be:
 *                                           - a Model instance,
 *                                           - an array with key `ModelParam::MODEL`,
 *                                           - a string identifier in the container,
 *                                           - or null.
 * @param ContainerInterface|null $container  Optional PSR-11 container used to resolve a string definition.
 * @param Model|null              $default    Optional fallback model returned if none could be resolved.
 *
 * @return Model|null The resolved `Model` instance, the provided default, or `null` if none found.
 *
 * @throws ContainerExceptionInterface If an error occurs while retrieving the model from the container.
 * @throws NotFoundExceptionInterface  If a string definition is provided but not found in the container.
 *
 * @package oihana\models\helpers
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
function getModel
(
    array|string|null|Model $definition = null ,
    ?ContainerInterface     $container  = null ,
    ?Model                  $default    = null
)
:?Model
{
    if( $definition instanceof Model )
    {
        return $definition ;
    }

    if( is_array( $definition ) )
    {
        $definition = $definition[ ModelParam::MODEL ] ?? null ;
    }

    if( is_string( $definition ) && $container?->has( $definition ) )
    {
        $definition = $container->get( $definition ) ;
    }

    return $definition instanceof Model ? $definition : $default ;
}