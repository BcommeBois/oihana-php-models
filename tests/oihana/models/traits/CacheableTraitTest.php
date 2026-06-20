<?php

namespace tests\oihana\models\traits;

use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;

use oihana\models\traits\CacheableTrait;

use PHPUnit\Framework\TestCase;

use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

class MockCacheable
{
    use CacheableTrait;
}

/**
 * Usage
 * ```bash
 * composer test tests/oihana/models/traits/CacheableTraitTest.php
 * ```
 */
final class CacheableTraitTest extends TestCase
{
    private object $object;
    private CacheInterface $mockCache;

    protected function setUp(): void
    {
        $this->object        = new MockCacheable();
        $this->mockCache     = $this->createStub(CacheInterface::class ) ; // PSR-16 Stub
        $this->object->cache = $this->mockCache ;
    }

    /**
     * Test simple du clear cache.
     */
    public function testClearCacheCallsClear(): void
    {
        $this->mockCache = $this->createMock(CacheInterface::class);
        $this->object->cache = $this->mockCache;

        $this->mockCache->expects($this->once())->method('clear');
        $this->object->clearCache();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testDeleteCacheCallsDelete(): void
    {
        $this->mockCache = $this->createMock(CacheInterface::class);
        $this->object->cache = $this->mockCache;
        $this->mockCache->expects($this->once())->method('delete')->with('foo');
        $this->object->deleteCache('foo');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testGetCacheReturnsValue(): void
    {
        $this->mockCache = $this->createMock(CacheInterface::class);
        $this->object->cache = $this->mockCache;
        $this->mockCache->expects($this->once())->method('get')->with('key')->willReturn('value');
        $this->assertSame('value', $this->object->getCache('key'));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testGetCacheReturnsNullWhenCacheIsNull(): void
    {
        $this->object->cache = null;
        $this->assertNull($this->object->getCache('key'));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testHasCacheReturnsTrue(): void
    {
        $this->mockCache = $this->createMock(CacheInterface::class);
        $this->object->cache = $this->mockCache;
        $this->mockCache->expects($this->once())->method('has')->with('key')->willReturn(true);
        $this->assertTrue($this->object->hasCache('key'));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testHasCacheReturnsFalseForNullKey(): void
    {
        $this->assertFalse($this->object->hasCache(null));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testHasCacheReturnsFalseWhenCacheIsNull(): void
    {
        $this->object->cache = null;
        $this->assertFalse($this->object->hasCache('key'));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testSetCacheStoresValueWhenCacheable(): void
    {
        $this->mockCache = $this->createMock(CacheInterface::class);
        $this->object->cache = $this->mockCache;
        $this->mockCache->expects($this->once())->method('set')->with('foo', 'bar')->willReturn(true);
        $this->assertTrue($this->object->setCache('foo', 'bar'));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testSetCacheReturnsFalseWhenNotCacheable(): void
    {
        $this->object->cacheable = false;
        $this->mockCache->method('set');
        $this->assertFalse($this->object->setCache('foo', 'bar'));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testSetCacheMultipleStoresValues(): void
    {
        $this->mockCache = $this->createMock(CacheInterface::class);
        $this->object->cache = $this->mockCache;
        $this->mockCache->expects($this->once())->method('setMultiple')->with(['a' => 1, 'b' => 2], null)->willReturn(true);
        $this->assertTrue($this->object->setCacheMultiple(['a' => 1, 'b' => 2]));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testSetCacheMultipleReturnsFalseWhenNotCacheable(): void
    {
        $this->object->cacheable = false;
        $this->mockCache->method('setMultiple');
        $this->assertFalse($this->object->setCacheMultiple(['x' => 1]));
    }

    public function testIsCacheableReturnsTrueWhenCacheSet(): void
    {
        $this->assertTrue($this->object->isCacheable());
    }

    public function testIsCacheableReturnsFalseWhenNoCache(): void
    {
        $this->object->cache = null;
        $this->assertFalse($this->object->isCacheable());
    }

    public function testIsCacheableOverridesFromInit(): void
    {
        $this->assertFalse ( $this->object->isCacheable( [ MockCacheable::CACHEABLE => false] ) ) ;
        $this->assertTrue  ( $this->object->isCacheable( [ MockCacheable::CACHEABLE => true ] ) ) ;
    }

    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function testInitializeCacheAcceptsCacheInstance(): void
    {
        $result = $this->object->initializeCache( [ MockCacheable::CACHE => $this->mockCache ] );
        $this->assertSame($this->object, $result);
        $this->assertSame($this->mockCache, $this->object->cache);
    }

    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function testInitializeCacheWithStringAndContainerResolves(): void
    {
        $container = new Container();

        $container->set( 'cacheService', $this->mockCache );

        $result = $this->object->initializeCache( [MockCacheable::CACHE => 'cacheService'], $container );

        $this->assertSame($this->mockCache, $this->object->cache);
        $this->assertSame($this->object, $result);
    }

    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function testInitializeCacheWithInvalidValueSetsNull(): void
    {
        $result = $this->object->initializeCache( [ MockCacheable::CACHE => 'invalid' ] ) ;
        $this->assertNull($this->object->cache);
        $this->assertSame($this->object, $result);
    }

    public function testInitializeCacheableUsesInitArray(): void
    {
        $this->object->cacheable = false;
        $this->object->initializeCacheable( [ MockCacheable::CACHEABLE => true ] ) ;
        $this->assertTrue($this->object->cacheable);
    }

    public function testInitializeCacheableKeepsExistingValue(): void
    {
        $this->object->cacheable = true;
        $this->object->initializeCacheable();
        $this->assertTrue( $this->object->cacheable ) ;
    }

    /**
     * Vérifie que setCache utilise le TTL de l'instance si aucun n'est fourni.
     * @throws InvalidArgumentException
     */
    public function testSetCacheUsesInstanceTtlWhenNoneProvided(): void
    {
        $this->object->ttl = 3600;

        // On crée un mock pour vérifier l'argument passé à set()
        $this->mockCache = $this->createMock(CacheInterface::class);
        $this->object->cache = $this->mockCache;

        $this->mockCache->expects($this->once())
            ->method('set')
            ->with('foo', 'bar', 3600)
            ->willReturn(true);

        $this->object->setCache('foo', 'bar');
    }

    /**
     * Vérifie que le TTL passé en argument prime sur le TTL de l'instance.
     * @throws InvalidArgumentException
     */
    public function testSetCacheArgumentOverridesInstanceTtl(): void
    {
        $this->object->ttl = 3600;

        $this->mockCache = $this->createMock(CacheInterface::class);
        $this->object->cache = $this->mockCache;

        $this->mockCache->expects($this->once())
            ->method('set')
            ->with('foo', 'bar', 60)
            ->willReturn(true);

        $this->object->setCache('foo', 'bar', 60);
    }

    /**
     * Vérifie setCacheMultiple avec le TTL de l'instance.
     * @throws InvalidArgumentException
     */
    public function testSetCacheMultipleUsesInstanceTtl(): void
    {
        $this->object->ttl = 120;
        $values = ['a' => 1, 'b' => 2];

        $this->mockCache = $this->createMock(CacheInterface::class);
        $this->object->cache = $this->mockCache;

        $this->mockCache->expects($this->once())
            ->method('setMultiple')
            ->with($values, 120)
            ->willReturn(true);

        $this->object->setCacheMultiple($values);
    }

    /**
     * Test de l'initialisation du TTL via le tableau.
     */
    public function testInitializeTtlSetsValue(): void
    {
        $this->object->initializeTtl([MockCacheable::TTL => 500]);
        $this->assertSame(500, $this->object->ttl);
    }

    /**
     * Vérifie que initializeCache appelle bien les sous-initialisations (Ttl et Cacheable).
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function testInitializeCacheTriggersSubInitializers(): void
    {
        $init = [
            MockCacheable::CACHE     => $this->mockCache,
            MockCacheable::CACHEABLE => false,
            MockCacheable::TTL       => 999
        ];

        $this->object->initializeCache($init);

        $this->assertSame($this->mockCache, $this->object->cache);
        $this->assertFalse($this->object->cacheable);
        $this->assertSame(999, $this->object->ttl);
    }

    /**
     * Vérifie le comportement de isCacheable quand le cache est désactivé via init.
     */
    public function testIsCacheableReturnsFalseEvenIfCacheExistsButDisabled(): void
    {
        $this->object->cacheable = false;
        $this->assertFalse($this->object->isCacheable());
    }
}
