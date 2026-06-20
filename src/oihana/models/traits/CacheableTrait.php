<?php

namespace oihana\models\traits;

use DateInterval;
use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Provides a standardized caching layer for classes using PSR-16 (Simple Cache).
 *
 * This trait manages:
 * - **Instance-level caching**: Easily enable/disable cache per object.
 * - **Dependency Injection**: Can resolve cache instances from a PSR-11 Container.
 * - **Flexible TTL**: Defines a default Time To Live (TTL) that can be overridden at call time.
 * - **Fluent Initialization**: Provides methods to hydrate the cache configuration from arrays.
 * * Expected configuration keys in $init arrays:
 * - 'cache' (string|CacheInterface): The cache instance or its container ID.
 * - 'cacheable' (bool): Toggle to enable/disable the cache functionality.
 * - 'ttl' (int|DateInterval|null): The default expiration time.
 *
 * @author Marc Alcaraz (eKameleon)
 * @package oihana\traits
 */
trait CacheableTrait
{
    /**
     * The 'cache' parameter constant.
     */
    public const string CACHE = 'cache' ;

    /**
     * The 'cacheable' parameter constant.
     */
    public const string CACHEABLE = 'cacheable' ;

    /**
     * The 'ttl' parameter constant.
     */
    public const string TTL = 'ttl' ;

    /**
     * The PSR-16 cache reference.
     * @var CacheInterface|mixed|null
     */
    public ?CacheInterface $cache = null ;

    /**
     * Indicates if the instance use an internal PSR-16 cache.
     * @var bool
     */
    public bool $cacheable = true ;

    /**
     * Default TTL for cache items.
     * @var null|int|DateInterval
     */
    public null|int|DateInterval $ttl = null ;

    /**
     * Clear the cache.
     * @return void
     */
    public function clearCache():void
    {
        $this->cache?->clear() ;
    }

    /**
     * Delete a key/value in the cache.
     * @param string $key
     * @return void
     * @throws InvalidArgumentException
     */
    public function deleteCache( string $key ):void
    {
        $this->cache?->delete( $key ) ;
    }

    /**
     * Returns the registered value in the cache with a specific key.
     * @param string $key
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function getCache( string $key ):mixed
    {
        return $this->cache?->get( $key ) ?? null ;
    }

    /**
     * Indicates if the key is registered in the cache.
     * @param ?string $key
     * @return bool
     * @throws InvalidArgumentException
     */
    public function hasCache( ?string $key ):bool
    {
        if( is_string( $key ) )
        {
            return $this->cache?->has( $key ) ?? false ;
        }
        return false ;
    }

    /**
     * Indicates if the ressource can use the cache.
     * @param array $init
     * @return bool
     */
    public function isCacheable( array $init = [] ):bool
    {
        return isset( $this->cache ) && ( $init[ self::CACHEABLE ] ?? $this->cacheable ?? false ) ;
    }

    /**
     * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
     *
     * @param string                $key   The key of the item to store.
     * @param mixed                 $value The value of the item to store, must be serializable.
     * @param null|int|DateInterval $ttl   Optional TTL (Time to Live)value of this item.
     *                                     If no value is sent and the driver supports TTL then the library may set
     *                                     a default value for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     *
     * @throws InvalidArgumentException MUST be thrown if the $key string is not a legal value.
     */
    public function setCache
    (
        string                $key ,
        mixed                 $value ,
        null|int|DateInterval $ttl = null
    )
    :bool
    {
        if( $this->cacheable )
        {
            return $this->cache?->set( $key , $value , $ttl ?? $this->ttl ) ?? false ;
        }
        return false ;
    }

    /**
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     *
     * @param array                 $values A list of key => value pairs for a multiple-set operation.
     * @param null|int|DateInterval $ttl    Optional TTL (Time to Live) value of this item.
     *                                      If no value is sent and the driver supports TTL then the library may set
     *                                      a default value for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     *
     * @throws InvalidArgumentException
     */
    public function setCacheMultiple
    (
        array                 $values ,
        null|int|DateInterval $ttl    = null
    )
    :bool
    {
        if( $this->cacheable )
        {
            return $this->cache?->setMultiple( $values , $ttl ?? $this->ttl ) ?? false ;
        }
        return false ;
    }

    /**
     * Initialize the cache reference.
     * @param array $init
     * @param Container|null $container
     * @return static
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function initializeCache(  array $init = [] , ?Container $container = null  ):static
    {
        $cache = $init[ self::CACHE ] ?? $this->cache ;

        if( is_string( $cache ) && isset( $container ) && $container->has( $cache ) )
        {
            $cache = $container->get( $cache ) ;
        }

        $this->cache = $cache instanceof CacheInterface ? $cache : null ;

        return $this->initializeCacheable( $init )->initializeTtl( $init ) ;
    }

    /**
     * Initialize the cacheable property.
     * @param array $init
     * @return static
     */
    public function initializeCacheable( array $init = [] ) :static
    {
        $this->cacheable = $init[ self::CACHEABLE ] ?? $this->cacheable ;
        return $this;
    }

    /**
     * Initialize the TTL property.
     * @param array $init
     * @return static
     */
    public function initializeTtl( array $init = [] ): static
    {
        $this->ttl = $init[ self::TTL ] ?? $this->ttl ;
        return $this ;
    }
}