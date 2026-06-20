# Helpers

Four free functions registered through composer `autoload.files`, all under the
`oihana\models\helpers` namespace. They are global functions, not class methods:
import each one with a `use function` statement, e.g.
`use function oihana\models\helpers\getModel;`. They cover the most common glue
needed when wiring models into a PSR-11 / dependency-injection container:
resolving a model, building a document URL, and creating a namespaced cache
collection.

## `getModel` — resolve a `Model` from a container

Resolves a `oihana\models\Model` instance from a flexible definition. The
definition may be a `Model` (returned as-is), an array carrying a
`ModelParam::MODEL` key, a string service identifier resolved from the
container, or `null`.

```php
public function getModel
(
    array|string|null|Model $definition = null ,
    ?ContainerInterface     $container  = null ,
    ?Model                  $default    = null
) : ?Model
```

- `$definition` — a `Model` instance, an array with key `ModelParam::MODEL`, a
  string identifier looked up in `$container`, or `null`.
- `$container` — optional PSR-11 container used to resolve a string definition.
- `$default` — optional fallback returned when nothing could be resolved.

**Returns** the resolved `Model`, the provided `$default`, or `null`.

Throws `Psr\Container\ContainerExceptionInterface` /
`Psr\Container\NotFoundExceptionInterface` on container errors.

```php
use function oihana\models\helpers\getModel;
use oihana\models\enums\ModelParam;

// From a string identifier in the container
$model = getModel( 'mainModel' , $container ) ;

// From an array definition
$model = getModel( [ ModelParam::MODEL => 'mainModel' ] , $container ) ;

// With a fallback when nothing resolves
$model = getModel( 'unknown' , $container , $defaultModel ) ;
```

## `getDocumentsModel` — resolve a `DocumentsModel` from a container

Same resolution pattern as `getModel`, specialised for the
`oihana\models\interfaces\DocumentsModel` interface. A `DocumentsModel` instance
is returned directly; a string identifier is resolved from the container;
otherwise the default is returned.

```php
public function getDocumentsModel
(
    string|null|DocumentsModel $definition = null ,
    ?ContainerInterface        $container  = null ,
    ?DocumentsModel            $default    = null
) : ?DocumentsModel
```

- `$definition` — a `DocumentsModel` instance, a string service identifier, or
  `null`.
- `$container` — optional PSR-11 container used to resolve a string definition.
- `$default` — optional fallback returned when no valid instance is found.

**Returns** the resolved `DocumentsModel`, the provided `$default`, or `null`.

Throws `Psr\Container\ContainerExceptionInterface` /
`Psr\Container\NotFoundExceptionInterface` on container errors.

```php
use function oihana\models\helpers\getDocumentsModel;
use oihana\models\interfaces\DocumentsModel;

// Direct instance
$model = new MyDocumentsModel() ;
echo getDocumentsModel( $model ) === $model ? 'ok' : 'fail' ; // ok

// String identifier resolved via the container
$resolved = getDocumentsModel( 'mainModel' , $container ) ;
echo $resolved instanceof DocumentsModel ? 'ok' : 'fail' ;    // ok

// Fallback to a default model
$default = new DefaultDocumentsModel() ;
echo getDocumentsModel( 'unknown' , $container , $default ) === $default ? 'ok' : 'fail' ; // ok
```

## `documentUrl` — build a document URL from the base URL

Generates a full document URL by joining a base URL (read from the container)
with a relative path. Commonly used inside IoC container definitions of models
to expose the accessible URL of a document or resource.

```php
public function documentUrl
(
    string              $path          = Char::EMPTY ,
    ?ContainerInterface $container     = null ,
    ?string             $definition    = 'baseUrl' ,
    bool                $trailingSlash = false
) : string
```

- `$path` — relative path of the document (default: empty string).
- `$container` — optional DI container to fetch the base URL from.
- `$definition` — key used to fetch the base URL from the container (default:
  `'baseUrl'`).
- `$trailingSlash` — whether to append a trailing slash to the result (default:
  `false`).

**Returns** the fully resolved document URL as a string.

Throws `Psr\Container\ContainerExceptionInterface` /
`Psr\Container\NotFoundExceptionInterface` on container errors.

```php
use function oihana\models\helpers\documentUrl;

$url = documentUrl( 'uploads/image.png' , $container ) ;
// e.g. 'https://example.com/uploads/image.png'

$urlWithSlash = documentUrl( 'uploads' , $container , 'baseUrl' , true ) ;
// 'https://example.com/uploads/'
```

## `cacheCollection` — create a namespaced PSR-16 cache

Creates an isolated, namespaced cache collection from a Key/Value store
registered in the DI container. It retrieves a
`MatthiasMullie\Scrapbook\KeyValueStore`, takes the requested collection, and
wraps it in a PSR-16 `MatthiasMullie\Scrapbook\Psr16\SimpleCache`. This lets you
keep several logical caches (per feature, domain, or module) inside the same
backend.

```php
public function cacheCollection
(
    Container $container  ,
    string    $collection ,
    string    $definition
) : ?SimpleCache
```

- `$container` — the PHP-DI `DI\Container` used to resolve the cache store.
- `$collection` — the collection name (namespace) to create inside the store.
- `$definition` — the container entry identifier of the base key/value store.

**Returns** a PSR-16 `SimpleCache` scoped to the collection, or `null` if the
definition is not found or is not a `KeyValueStore`.

Throws `DI\DependencyException` / `DI\NotFoundException` on container errors.

```php
use function oihana\models\helpers\cacheCollection;

// Retrieve a cache collection named "users"
$userCache = cacheCollection( $container , 'users' , 'cache:memory' ) ;

// Store and retrieve values
$userCache->set( 'id:42' , [ 'name' => 'Alice' ] ) ;
$data = $userCache->get( 'id:42' ) ;
```

## Next steps

- [Models](models.md) — the base `Model` resolved by `getModel`.
- [Documents](documents.md) — the `DocumentsModel` resolved by `getDocumentsModel`.
- [Cache](cache.md) — caching layer used with `cacheCollection`.
- [Tests & coverage](testing.md) — run and extend the helper test suite.
