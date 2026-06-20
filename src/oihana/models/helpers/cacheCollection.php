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
 * Example usage:
 * ```php
 * // Retrieve a cache collection named "users"
 * $userCache = cacheCollection( $container , "users" , 'cache:memory' );
 *
 * // Store and retrieve values
 * $userCache->set("id:42", ["name" => "Alice"]);
 * $data = $userCache->get("id:42");
 * ```
 *
 * @param Container $container  The DI container used to resolve the cache store definition.
 * @param string    $collection The collection name (namespace) to create inside the cache store.
 * @param string    $definition The container entry identifier of the base key/value store in the DI container.
 *
 * @return SimpleCache|null A PSR-16 cache instance scoped to the given collection, or `null` if the definition is not found or not compatible.
 *
 * @throws DependencyException If the container fails to resolve the cache definition.
 * @throws NotFoundException   If the cache definition is not registered in the container.
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