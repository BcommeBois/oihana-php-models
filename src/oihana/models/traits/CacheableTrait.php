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
 * Mix this trait into any model or service that needs an optional, swappable PSR-16 cache.
 * When `$cacheable` is `false` (or no cache is set) the read/write helpers degrade gracefully:
 * writes become no-ops returning `false` and reads return `null`, so callers never have to
 * branch on whether caching is enabled.
 *
 * @package oihana\models\traits
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
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
     * Clears the whole cache.
     *
     * No-op when no cache instance is attached.
     *
     * @return void
     */
    public function clearCache():void
    {
        $this->cache?->clear() ;
    }

    /**
     * Deletes a single entry from the cache.
     *
     * No-op when no cache instance is attached.
     *
     * @param string $key The cache key to remove.
     *
     * @return void
     *
     * @throws InvalidArgumentException If the `$key` is not a legal cache key.
     */
    public function deleteCache( string $key ):void
    {
        $this->cache?->delete( $key ) ;
    }

    /**
     * Returns the value stored under the given cache key.
     *
     * @param string $key The cache key to read.
     *
     * @return mixed The cached value, or `null` when the key is missing or no cache is attached.
     *
     * @throws InvalidArgumentException If the `$key` is not a legal cache key.
     *
     * @example
     * ```php
     * if ( !$this->hasCache( 'products' ) )
     * {
     *     $this->setCache( 'products' , $this->fetchProducts() , 3600 );
     * }
     *
     * $products = $this->getCache( 'products' );
     * ```
     */
    public function getCache( string $key ):mixed
    {
        return $this->cache?->get( $key ) ?? null ;
    }

    /**
     * Indicates whether a given key is present in the cache.
     *
     * @param ?string $key The cache key to test; `null` always returns `false`.
     *
     * @return bool `true` if the key exists in the attached cache, `false` otherwise (or when no cache is attached).
     *
     * @throws InvalidArgumentException If the `$key` is a non-null but illegal cache key.
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
     * Indicates whether the resource may actually use the cache.
     *
     * Returns `true` only when a cache instance is attached *and* caching is enabled — either by
     * the runtime {@see self::CACHEABLE} flag in `$init` or, as a fallback, by the `$cacheable` property.
     *
     * @param array $init Optional runtime options; a `cacheable` boolean key overrides the property.
     *
     * @return bool `true` if the cache can be used, `false` otherwise.
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
     * @return bool True on success and false on failure (including when caching is disabled or no cache is attached).
     *
     * @throws InvalidArgumentException MUST be thrown if the $key string is not a legal value.
     *
     * @example
     * ```php
     * // Cache for one hour
     * $this->setCache( 'user:42' , $user , 3600 );
     *
     * // Cache using the default TTL of the instance
     * $this->setCache( 'config' , $config );
     * ```
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
     * @return bool True on success and false on failure (including when caching is disabled or no cache is attached).
     *
     * @throws InvalidArgumentException If any of the `$values` keys is not a legal cache key.
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
     * Initializes the cache reference, then the `cacheable` and `ttl` properties.
     *
     * The cache is read from the {@see self::CACHE} key of `$init` (falling back to the current
     * `$cache`). When that value is a string and a container is provided, it is resolved as a
     * container service id. Any value that is not a {@see CacheInterface} results in a `null` cache.
     *
     * @param array          $init      Initialization options (keys: `cache`, `cacheable`, `ttl`).
     * @param Container|null  $container Optional DI container used to resolve a string cache id.
     *
     * @return static The current instance, for fluent chaining.
     *
     * @throws DependencyException If the dependency cannot be resolved by the container.
     * @throws NotFoundException If no entry is found for the given identifier in the container.
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
     * Initializes the `$cacheable` flag from an initialization array.
     *
     * Read from the {@see self::CACHEABLE} key when present; otherwise the current value is kept.
     *
     * @param array $init Initialization options.
     *
     * @return static The current instance, for fluent chaining.
     */
    public function initializeCacheable( array $init = [] ) :static
    {
        $this->cacheable = $init[ self::CACHEABLE ] ?? $this->cacheable ;
        return $this;
    }

    /**
     * Initializes the default `$ttl` from an initialization array.
     *
     * Read from the {@see self::TTL} key when present; otherwise the current value is kept.
     *
     * @param array $init Initialization options.
     *
     * @return static The current instance, for fluent chaining.
     */
    public function initializeTtl( array $init = [] ): static
    {
        $this->ttl = $init[ self::TTL ] ?? $this->ttl ;
        return $this ;
    }
}