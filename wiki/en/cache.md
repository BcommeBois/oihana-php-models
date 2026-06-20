# Cache

`oihana\models\traits\CacheableTrait` gives any class a standardized PSR-16
(`Psr\SimpleCache\CacheInterface`) caching layer: a cache instance, an on/off
toggle, a default TTL, and the helpers to read, write and clear entries. It is
the foundation behind cached model collections, typically backed by
[Scrapbook](https://www.scrapbook.cash) and wired with the
[`cacheCollection()`](helpers.md) helper.

## `CacheableTrait`

The trait holds three public properties and the operations that act on them.
None of the methods do anything when `$cache` is `null`, so a class can use the
trait safely even before a backend is wired.

### Properties & constants

| Member | Type | Default | Description |
|---|---|---|---|
| `$cache` | `?CacheInterface` | `null` | The PSR-16 store. When `null`, every operation is a no-op. |
| `$cacheable` | `bool` | `true` | Master switch. Writes via `setCache()` / `setCacheMultiple()` are skipped when `false`. |
| `$ttl` | `null\|int\|DateInterval` | `null` | Default expiration applied when a call omits its own TTL. |
| `CacheableTrait::CACHE` | `string` | `'cache'` | Init-array key for the cache instance or container id. |
| `CacheableTrait::CACHEABLE` | `string` | `'cacheable'` | Init-array key for the `$cacheable` toggle. |
| `CacheableTrait::TTL` | `string` | `'ttl'` | Init-array key for the default TTL. |

### Operations

| Method | Returns | Description |
|---|---|---|
| `getCache( string $key )` | `mixed` | Value stored under `$key`, or `null`. |
| `hasCache( ?string $key )` | `bool` | `true` if `$key` is a string and present in the store. |
| `setCache( string $key , mixed $value , null\|int\|DateInterval $ttl = null )` | `bool` | Persists one entry. Returns `false` when `$cacheable` is `false`. Falls back to `$this->ttl` when `$ttl` is omitted. |
| `setCacheMultiple( array $values , null\|int\|DateInterval $ttl = null )` | `bool` | Persists many `key => value` pairs. Returns `false` when `$cacheable` is `false`. |
| `deleteCache( string $key )` | `void` | Removes a single entry. |
| `clearCache()` | `void` | Empties the whole store. |
| `isCacheable( array $init = [] )` | `bool` | `true` when a `$cache` is set **and** caching is enabled (an `$init['cacheable']` override wins over the property). |

### Initialization helpers

These fluent methods hydrate the configuration from an `$init` array and return
`$this` (or `static`), so they can be chained — typically from a constructor.

| Method | Description |
|---|---|
| `initializeCache( array $init = [] , ?Container $container = null )` | Resolves `$cache` from `$init['cache']`. When the value is a string and a PSR-11 `Container` is supplied, it is fetched from the container. Then runs `initializeCacheable()` and `initializeTtl()`. |
| `initializeCacheable( array $init = [] )` | Sets `$cacheable` from `$init['cacheable']` (keeps the current value if absent). |
| `initializeTtl( array $init = [] )` | Sets `$ttl` from `$init['ttl']` (keeps the current value if absent). |

## Wiring a PSR-16 cache

Any `Psr\SimpleCache\CacheInterface` works. The example below builds a Scrapbook
store over Memcached and wraps it in the PSR-16 adapter:

```php
use Memcached;
use MatthiasMullie\Scrapbook\Adapters\Memcached as ScrapbookMemcached;
use MatthiasMullie\Scrapbook\Psr16\SimpleCache;

$client = new Memcached() ;
$client->addServer( '127.0.0.1' , 11211 ) ;

$cache = new SimpleCache( new ScrapbookMemcached( $client ) ) ;
```

## Enabling caching on a model

Add the trait to your class, then hydrate it from an `$init` array. When the
`cache` key is a container id, pass the DI container so it can be resolved:

```php
use DateInterval;
use oihana\models\traits\CacheableTrait;

class ProductModel
{
    use CacheableTrait;

    public function __construct( array $init = [] , ?\DI\Container $container = null )
    {
        $this->initializeCache( $init , $container ) ;
    }
}

$model = new ProductModel(
[
    ProductModel::CACHE     => $cache ,                   // CacheInterface or container id
    ProductModel::CACHEABLE => true ,
    ProductModel::TTL       => new DateInterval( 'PT1H' ) , // 1 hour default
] ) ;
```

## Reading & writing

Once wired, use the trait's operations directly. Writes honour the master switch
and fall back to the default TTL:

```php
if ( ! $model->hasCache( 'products:42' ) )
{
    $model->setCache( 'products:42' , [ 'id' => 42 , 'name' => 'Widget' ] ) ;
}

$product = $model->getCache( 'products:42' ) ; // ['id' => 42, 'name' => 'Widget']

$model->deleteCache( 'products:42' ) ; // drop one entry
$model->clearCache() ;                 // empty the whole store
```

To temporarily disable persistence without unwiring the cache, flip the toggle —
`setCache()` and `setCacheMultiple()` become no-ops, while reads keep working:

```php
$model->cacheable = false ;
$model->setCache( 'products:42' , $value ) ; // returns false, nothing stored
```

## Next steps

- [Documents](documents.md) — the model classes that consume this caching layer.
- [Helpers](helpers.md) — `cacheCollection()`, the namespaced PSR-16 collection builder.
- [Tests & coverage](testing.md) — how the cache behaviour is verified.
