<?php

namespace oihana\models\helpers ;

use oihana\models\interfaces\DocumentsModel;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Resolves a {@see DocumentsModel} instance from a PSR-11 container or returns a default.
 *
 * This helper function provides a flexible way to obtain a `DocumentsModel` instance:
 *
 * - If `$definition` is already a `DocumentsModel`, it is returned as-is.
 * - If `$definition` is a string and `$container` implements {@see ContainerInterface},
 * the function attempts to resolve the corresponding service from the container.
 * - If resolution fails, the provided `$default` (if any) is returned instead.
 *
 * This pattern allows for safe dependency resolution in controllers or services,
 * without requiring explicit type-checking or container awareness in user code.
 *
 * @param string|DocumentsModel|null $definition The model definition — either:
 * - a `DocumentsModel` instance (returned directly),
 * - a string service identifier (resolved from `$container`),
 * - or `null` (uses `$default`).
 * @param ContainerInterface|null $container Optional PSR-11 container to resolve string identifiers.
 * @param DocumentsModel|null $default Optional fallback model if no valid instance is found.
 *
 * @return DocumentsModel|null The resolved model, the provided default, or `null` if none.
 *
 * @throws ContainerExceptionInterface If an error occurs while retrieving an entry from the dependency-injection container.
 * @throws NotFoundExceptionInterface  If no entry is found for the requested identifier in the container.
 *
 * @example
 * ```php
 * use oihana\models\interfaces\DocumentsModel;
 * use Psr\Container\ContainerInterface;
 *
 * use function oihana\models\helpers\getDocumentsModel;
 *
 * // Case 1: Direct instance
 * $model = new MyDocumentsModel();
 * echo getDocumentsModel( $model ) === $model ? 'ok' : 'fail' ; // ok
 *
 * // Case 2: String identifier resolved via container
 * $container = new Container(); // implements ContainerInterface
 * $container->set( 'mainModel', new MyDocumentsModel() );
 *
 * $resolved = getDocumentsModel( 'mainModel', $container );
 * echo $resolved instanceof DocumentsModel ? 'ok' : 'fail' ;   // ok
 *
 * // Case 3: Fallback to default model
 * $default = new DefaultDocumentsModel();
 * echo getDocumentsModel( 'unknown', $container, $default ) === $default ? 'ok' : 'fail' ; // ok
 * ```
 *
 * @package oihana\models\helpers
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
function getDocumentsModel
(
    string|null|DocumentsModel $definition = null ,
    ?ContainerInterface        $container  = null ,
    ?DocumentsModel            $default    = null
)
:?DocumentsModel
{
    if( $definition instanceof DocumentsModel )
    {
        return $definition ;
    }

    if( is_string( $definition ) && $container?->has( $definition ) )
    {
        $definition = $container->get( $definition ) ;
    }

    return $definition instanceof DocumentsModel ? $definition : $default ;
}