<?php

namespace oihana\models\helpers ;

use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;

use MatthiasMullie\Scrapbook\KeyValueStore;
use MatthiasMullie\Scrapbook\Psr16\SimpleCache;

/**
 * Creates a namespaced cache collection from a Key/Value store definition registered in the dependency injection container.
 *
 * A cache collection is an isolated namespace within the same backend,
 * allowing logical separation of cached values (e.g. per feature, domain, or module).
 * This helper function retrieves a {@see KeyValueStore} from the container,
 * and wraps its collection in a PSR-16 {@see SimpleCache} implementation.
 *
 * It returns `null` when the definition is not registered in the container, or
 * when the resolved entry is not a {@see KeyValueStore} — making it safe to use
 * as an optional cache layer.
 *
 * @param Container $container  The DI container used to resolve the cache store definition.
 * @param string    $collection The collection name (namespace) to create inside the cache store.
 * @param string    $definition The container entry identifier of the base key/value store in the DI container.
 *
 * @return SimpleCache|null A PSR-16 cache instance scoped to the given collection, or `null` if the definition is not found or not compatible.
 *
 * @throws DependencyException If the dependency cannot be resolved by the container.
 * @throws NotFoundException   If no entry is found for the given identifier in the container.
 *
 * @example
 * ```php
 * use function oihana\models\helpers\cacheCollection;
 *
 * // Retrieve a cache collection named "users" backed by the 'cache:memory' store.
 * $userCache = cacheCollection( $container, 'users', 'cache:memory' ) ;
 *
 * if ( $userCache !== null )
 * {
 *     $userCache->set( 'id:42', [ 'name' => 'Alice' ] ) ;
 *     $data = $userCache->get( 'id:42' ) ; // [ 'name' => 'Alice' ]
 * }
 * ```
 *
 * @see https://www.scrapbook.cash
 *
 * @package oihana\models\helpers
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
function cacheCollection
(
    Container $container  ,
    string    $collection ,
    string    $definition
)
: ?SimpleCache
{
    if( $container->has( $definition ) )
    {
        $cache = $container->get( $definition ) ;
        if( $cache instanceof KeyValueStore )
        {
            return new SimpleCache( $cache->getCollection( $collection ) ) ;
        }
    }
    return null ;
}